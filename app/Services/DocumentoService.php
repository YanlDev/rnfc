<?php

namespace App\Services;

use App\Models\Carpeta;
use App\Models\Documento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentoService
{
    private const DISCO = 'documentos';

    /**
     * Sube un archivo nuevo a la carpeta indicada. Crea un documento vigente
     * (documento_padre_id = null, version = 1).
     */
    public function subir(Carpeta $carpeta, UploadedFile $archivo, ?int $usuarioId = null): Documento
    {
        return DB::transaction(function () use ($carpeta, $archivo, $usuarioId) {
            $nombreArchivo = $this->nombreUnico($archivo);
            $directorio = "obras/{$carpeta->obra_id}/{$carpeta->ruta}";

            $rutaCompleta = Storage::disk(self::DISCO)
                ->putFileAs($directorio, $archivo, $nombreArchivo);

            return Documento::create([
                'obra_id' => $carpeta->obra_id,
                'carpeta_id' => $carpeta->id,
                'documento_padre_id' => null,
                'version' => 1,
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_archivo' => $nombreArchivo,
                'archivo_path' => $rutaCompleta,
                'mime' => $archivo->getMimeType() ?? 'application/octet-stream',
                'tamano' => $archivo->getSize() ?? 0,
                'subido_por' => $usuarioId,
            ]);
        });
    }

    /**
     * Sube una nueva versión sobre un documento raíz existente.
     * Implementa el patrón "raíz como actual" (§4.4):
     *   1. Snapshot del estado actual del documento raíz como una fila hija.
     *   2. La fila raíz se actualiza con el archivo nuevo y version + 1.
     */
    public function subirNuevaVersion(Documento $raiz, UploadedFile $archivo, ?int $usuarioId = null): Documento
    {
        if ($raiz->documento_padre_id !== null) {
            throw new \InvalidArgumentException('Las versiones nuevas se cargan sobre el documento vigente (raíz).');
        }

        return DB::transaction(function () use ($raiz, $archivo, $usuarioId) {
            // 1. Snapshot del archivo actual como versión histórica.
            Documento::create([
                'obra_id' => $raiz->obra_id,
                'carpeta_id' => $raiz->carpeta_id,
                'documento_padre_id' => $raiz->id,
                'version' => $raiz->version,
                'nombre_original' => $raiz->nombre_original,
                'nombre_archivo' => $raiz->nombre_archivo,
                'archivo_path' => $raiz->archivo_path,
                'mime' => $raiz->mime,
                'tamano' => $raiz->tamano,
                'subido_por' => $raiz->subido_por,
                'created_at' => $raiz->updated_at,
            ]);

            // 2. Guardar el nuevo archivo en disco.
            $nombreArchivo = $this->nombreUnico($archivo);
            $directorio = "obras/{$raiz->obra_id}/{$raiz->carpeta->ruta}";
            $rutaCompleta = Storage::disk(self::DISCO)
                ->putFileAs($directorio, $archivo, $nombreArchivo);

            // 3. Reemplazar el contenido de la raíz con la nueva versión.
            $raiz->update([
                'version' => $raiz->version + 1,
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_archivo' => $nombreArchivo,
                'archivo_path' => $rutaCompleta,
                'mime' => $archivo->getMimeType() ?? 'application/octet-stream',
                'tamano' => $archivo->getSize() ?? 0,
                'subido_por' => $usuarioId,
            ]);

            return $raiz->fresh();
        });
    }

    /**
     * Elimina un documento raíz y todas sus versiones históricas + archivos.
     */
    public function eliminar(Documento $documento): void
    {
        if ($documento->documento_padre_id !== null) {
            throw new \InvalidArgumentException('Sólo se elimina el documento vigente; las versiones caen por cascada.');
        }

        DB::transaction(function () use ($documento) {
            // Borrar archivos físicos: el actual + todas las versiones históricas.
            $rutas = [$documento->archivo_path];
            foreach ($documento->versionesHistoricas as $v) {
                $rutas[] = $v->archivo_path;
            }
            Storage::disk(self::DISCO)->delete($rutas);

            $documento->delete(); // cascade borra los hijos (versiones) en BD
        });
    }

    private function nombreUnico(UploadedFile $archivo): string
    {
        $ext = strtolower($archivo->getClientOriginalExtension()) ?: 'bin';

        return Str::ulid().'.'.$ext;
    }
}
