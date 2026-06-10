<?php

namespace App\Services;

use App\Models\Carpeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CarpetaService
{
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
