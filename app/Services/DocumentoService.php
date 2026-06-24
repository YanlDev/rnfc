<?php

namespace App\Services;

use App\Models\Carpeta;
use App\Models\Documento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;

class DocumentoService
{
    private const DISCO = 'documentos';

    /**
     * Sube un archivo nuevo a la carpeta indicada. Crea un documento vigente
     * (documento_padre_id = null, version = 1).
     */
    public function subir(Carpeta $carpeta, UploadedFile $archivo, ?int $usuarioId = null): Documento
    {
        $nombreArchivo = $this->nombreUnico($archivo);
        $directorio = "obras/{$carpeta->obra_id}/{$carpeta->ruta}";

        // Escribimos primero en disco (el filesystem no es transaccional).
        $rutaCompleta = $this->guardarEnDisco($directorio, $archivo, $nombreArchivo);

        try {
            return DB::transaction(fn () => Documento::create([
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
            ]));
        } catch (\Throwable $e) {
            // Si el registro falla, no dejamos el archivo huérfano en disco.
            Storage::disk(self::DISCO)->delete($rutaCompleta);
            throw $e;
        }
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

        // 1. Guardar el nuevo archivo en disco antes de tocar la BD.
        $nombreArchivo = $this->nombreUnico($archivo);
        $directorio = "obras/{$raiz->obra_id}/{$raiz->carpeta->ruta}";
        $rutaCompleta = $this->guardarEnDisco($directorio, $archivo, $nombreArchivo);

        try {
            return DB::transaction(function () use ($raiz, $archivo, $usuarioId, $nombreArchivo, $rutaCompleta) {
                // 2. Snapshot del archivo actual como versión histórica.
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
        } catch (\Throwable $e) {
            // Limpiamos sólo el archivo recién subido (el de la versión previa
            // sigue referenciado por el snapshot y no debe borrarse).
            Storage::disk(self::DISCO)->delete($rutaCompleta);
            throw $e;
        }
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

    /**
     * Crea un documento vigente a partir de un archivo ya ensamblado en disco
     * local (subida en trozos). A diferencia de subir(), NO carga el archivo en
     * memoria: lo empuja al disco destino en streaming (importante para Bunny,
     * cuyo adapter bufferiza si se usa putFileAs/writeStream — ver §grande).
     */
    public function subirDesdeRuta(Carpeta $carpeta, string $rutaLocal, string $nombreOriginal, string $mime, int $tamano, ?int $usuarioId = null): Documento
    {
        $nombreArchivo = $this->nombreUnico($nombreOriginal);
        $directorio = "obras/{$carpeta->obra_id}/{$carpeta->ruta}";
        $rutaCompleta = $this->guardarStreaming($directorio, $nombreArchivo, $rutaLocal);

        try {
            return DB::transaction(fn () => Documento::create([
                'obra_id' => $carpeta->obra_id,
                'carpeta_id' => $carpeta->id,
                'documento_padre_id' => null,
                'version' => 1,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'archivo_path' => $rutaCompleta,
                'mime' => $mime ?: 'application/octet-stream',
                'tamano' => $tamano,
                'subido_por' => $usuarioId,
            ]));
        } catch (\Throwable $e) {
            Storage::disk(self::DISCO)->delete($rutaCompleta);
            throw $e;
        }
    }

    /**
     * Versión nueva (raíz-como-actual) a partir de un archivo ensamblado en
     * disco local. Equivalente a subirNuevaVersion() pero en streaming.
     */
    public function subirNuevaVersionDesdeRuta(Documento $raiz, string $rutaLocal, string $nombreOriginal, string $mime, int $tamano, ?int $usuarioId = null): Documento
    {
        if ($raiz->documento_padre_id !== null) {
            throw new \InvalidArgumentException('Las versiones nuevas se cargan sobre el documento vigente (raíz).');
        }

        $nombreArchivo = $this->nombreUnico($nombreOriginal);
        $directorio = "obras/{$raiz->obra_id}/{$raiz->carpeta->ruta}";
        $rutaCompleta = $this->guardarStreaming($directorio, $nombreArchivo, $rutaLocal);

        try {
            return DB::transaction(function () use ($raiz, $nombreOriginal, $mime, $tamano, $usuarioId, $nombreArchivo, $rutaCompleta) {
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

                $raiz->update([
                    'version' => $raiz->version + 1,
                    'nombre_original' => $nombreOriginal,
                    'nombre_archivo' => $nombreArchivo,
                    'archivo_path' => $rutaCompleta,
                    'mime' => $mime ?: 'application/octet-stream',
                    'tamano' => $tamano,
                    'subido_por' => $usuarioId,
                ]);

                return $raiz->fresh();
            });
        } catch (\Throwable $e) {
            Storage::disk(self::DISCO)->delete($rutaCompleta);
            throw $e;
        }
    }

    /**
     * Persiste el archivo en disco y verifica el resultado. Lanza si el disco
     * (p. ej. el CDN remoto) falla, en lugar de continuar con una ruta inválida.
     */
    private function guardarEnDisco(string $directorio, UploadedFile $archivo, string $nombreArchivo): string
    {
        $ruta = Storage::disk(self::DISCO)->putFileAs($directorio, $archivo, $nombreArchivo);

        if ($ruta === false || $ruta === '') {
            throw new \RuntimeException('No se pudo almacenar el archivo en el disco.');
        }

        return $ruta;
    }

    /**
     * Sube un archivo local al disco de documentos SIN cargarlo en memoria.
     *
     * El adapter de Bunny bufferiza todo el archivo (stream_get_contents) tanto
     * en putFileAs como en writeStream, lo que revienta memory_limit con
     * archivos de cientos de MB. Para Bunny vamos directo al cliente nativo
     * pasándole un handle: Guzzle lo envía en streaming con Content-Length.
     * Para discos locales, writeStream copia sin cargar todo a memoria.
     */
    private function guardarStreaming(string $directorio, string $nombreArchivo, string $rutaLocal): string
    {
        $rutaCompleta = trim($directorio, '/').'/'.$nombreArchivo;
        $config = config('filesystems.disks.'.self::DISCO);

        $handle = fopen($rutaLocal, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('No se pudo abrir el archivo ensamblado.');
        }

        try {
            if (($config['driver'] ?? null) === 'bunny') {
                $client = new BunnyCDNClient(
                    $config['storage_zone'],
                    $config['api_key'],
                    $config['region'] ?? '',
                );
                $client->upload($rutaCompleta, $handle);
            } else {
                Storage::disk(self::DISCO)->writeStream($rutaCompleta, $handle);
            }
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        // Verificamos que el objeto exista realmente en el disco destino.
        if (! Storage::disk(self::DISCO)->exists($rutaCompleta)) {
            throw new \RuntimeException('La subida al disco no se pudo verificar.');
        }

        return $rutaCompleta;
    }

    private function nombreUnico(UploadedFile|string $archivo): string
    {
        $original = $archivo instanceof UploadedFile
            ? $archivo->getClientOriginalName()
            : $archivo;

        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION)) ?: 'bin';

        return Str::ulid().'.'.$ext;
    }
}
