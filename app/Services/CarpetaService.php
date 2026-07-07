<?php

namespace App\Services;

use App\Models\Carpeta;
use App\Models\Documento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CarpetaService
{
    private const DISCO = 'documentos';

    /**
     * Elimina la carpeta (la BD borra en cascada subcarpetas y documentos)
     * y luego los archivos físicos de todo el subárbol.
     *
     * El borrado físico se basa en documentos.archivo_path — no en el
     * directorio de la carpeta — porque al renombrar carpetas la ruta lógica
     * cambia pero los archivos se quedan en su ruta física original.
     */
    public function eliminar(Carpeta $carpeta): void
    {
        $carpetaIds = Carpeta::where('obra_id', $carpeta->obra_id)
            ->where(fn ($q) => $q->whereKey($carpeta->id)
                ->orWhere('ruta', 'like', $carpeta->ruta.'/%'))
            ->pluck('id');

        // Incluye versiones históricas (comparten carpeta_id con su raíz).
        $rutasArchivos = Documento::whereIn('carpeta_id', $carpetaIds)
            ->pluck('archivo_path')
            ->filter()
            ->unique()
            ->values();

        $carpeta->delete();

        if ($rutasArchivos->isEmpty()) {
            return;
        }

        // Best-effort: si el disco remoto falla no revertimos la eliminación
        // lógica; se registra para poder limpiar los huérfanos después.
        try {
            Storage::disk(self::DISCO)->delete($rutasArchivos->all());
        } catch (\Throwable $e) {
            Log::warning('No se pudieron borrar archivos físicos al eliminar la carpeta', [
                'obra_id' => $carpeta->obra_id,
                'carpeta_ruta' => $carpeta->ruta,
                'archivos' => $rutasArchivos->count(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Renombra una carpeta y recalcula la ruta de todos sus descendientes,
     * reemplazando el prefijo viejo por el nuevo. La metadata organizativa
     * (ruta) es la fuente de verdad lógica; los archivos físicos no se mueven.
     *
     * @return bool true si hubo cambios; false si el nombre/ruta no cambió.
     *
     * @throws ValidationException si la nueva ruta colisiona con otra carpeta.
     */
    public function renombrar(Carpeta $carpeta, string $nuevoNombre): bool
    {
        $nuevoSlug = Carpeta::slugify($nuevoNombre);
        $parentRuta = $carpeta->parent_id
            ? Carpeta::where('id', $carpeta->parent_id)->value('ruta')
            : null;
        $nuevaRuta = $parentRuta ? "{$parentRuta}/{$nuevoSlug}" : $nuevoSlug;

        // No-op: ni la ruta efectiva ni el nombre cambian.
        if ($nuevaRuta === $carpeta->ruta && $nuevoNombre === $carpeta->nombre) {
            return false;
        }

        // Conflicto: la nueva ruta ya existe en la obra.
        $existe = Carpeta::where('obra_id', $carpeta->obra_id)
            ->where('ruta', $nuevaRuta)
            ->where('id', '!=', $carpeta->id)
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'nombre' => 'Ya existe una carpeta con ese nombre en la misma ubicación.',
            ]);
        }

        DB::transaction(function () use ($carpeta, $nuevoNombre, $nuevaRuta) {
            $rutaVieja = $carpeta->ruta;
            $carpeta->update(['nombre' => $nuevoNombre, 'ruta' => $nuevaRuta]);

            Carpeta::where('obra_id', $carpeta->obra_id)
                ->where('ruta', 'like', $rutaVieja.'/%')
                ->get()
                ->each(function (Carpeta $hijo) use ($rutaVieja, $nuevaRuta) {
                    $hijo->update([
                        'ruta' => $nuevaRuta.substr($hijo->ruta, strlen($rutaVieja)),
                    ]);
                });
        });

        return true;
    }
}
