<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Models\UploadSession;
use App\Models\User;
use App\Notifications\DocumentoSubido;
use App\Services\DocumentoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class FinalizarSubidaDocumento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * No reintentamos una subida de varios GB a ciegas: si falla, se marca
     * error y el usuario reintenta. El timeout cubre el push largo a Bunny.
     */
    public int $tries = 1;

    public int $timeout = 3600; // 1 h

    public function __construct(public UploadSession $sesion)
    {
        $this->onQueue(config('uploads.queue'));
    }

    public function handle(DocumentoService $service): void
    {
        $sesion = $this->sesion->fresh();

        if ($sesion === null || $sesion->estado === 'completado') {
            return;
        }

        $sesion->update(['estado' => 'procesando']);

        $dir = $sesion->directorioTemporal();
        $ensamblado = $dir.'/ensamblado.bin';

        try {
            $this->ensamblar($sesion, $dir, $ensamblado);

            $mime = $this->detectarMime($ensamblado, $sesion->nombre_original);
            $tamano = (int) filesize($ensamblado);

            $documento = $sesion->esVersion()
                ? $this->finalizarVersion($service, $sesion, $ensamblado, $mime, $tamano)
                : $this->finalizarNuevo($service, $sesion, $ensamblado, $mime, $tamano);

            if ($documento === null) {
                throw new \RuntimeException('El destino de la subida ya no existe.');
            }

            $sesion->update([
                'estado' => 'completado',
                'documento_resultante_id' => $documento->id,
                'error' => null,
            ]);

            $this->avisarMiembros($documento, $sesion->subido_por);
        } catch (\Throwable $e) {
            $this->marcarError($sesion, $e);
            throw $e;
        } finally {
            $this->limpiar($dir);
        }
    }

    public function failed(\Throwable $e): void
    {
        $sesion = $this->sesion->fresh();
        if ($sesion !== null && $sesion->estado !== 'completado') {
            $this->marcarError($sesion, $e);
            $this->limpiar($sesion->directorioTemporal());
        }
    }

    /**
     * Concatena los trozos en orden en un único archivo. Va borrando cada
     * trozo tras copiarlo para no duplicar el espacio en disco.
     */
    private function ensamblar(UploadSession $sesion, string $dir, string $ensamblado): void
    {
        $out = fopen($ensamblado, 'wb');
        if ($out === false) {
            throw new \RuntimeException('No se pudo crear el archivo ensamblado.');
        }

        try {
            for ($i = 0; $i < $sesion->total_chunks; $i++) {
                $part = $dir.'/'.$i.'.part';
                if (! is_file($part)) {
                    throw new \RuntimeException("Falta el trozo {$i} de la subida.");
                }

                $in = fopen($part, 'rb');
                if ($in === false) {
                    throw new \RuntimeException("No se pudo leer el trozo {$i}.");
                }
                stream_copy_to_stream($in, $out);
                fclose($in);
                @unlink($part);
            }
        } finally {
            fclose($out);
        }

        $tamano = filesize($ensamblado);
        if ($tamano !== $sesion->tamano_total) {
            throw new \RuntimeException(
                "Tamaño ensamblado ({$tamano}) distinto al esperado ({$sesion->tamano_total})."
            );
        }
    }

    private function finalizarNuevo(DocumentoService $service, UploadSession $sesion, string $ruta, string $mime, int $tamano): ?Documento
    {
        $carpeta = $sesion->carpeta;
        if ($carpeta === null) {
            return null;
        }

        return $service->subirDesdeRuta(
            $carpeta,
            $ruta,
            $sesion->nombre_original,
            $mime,
            $tamano,
            $sesion->subido_por,
        );
    }

    private function finalizarVersion(DocumentoService $service, UploadSession $sesion, string $ruta, string $mime, int $tamano): ?Documento
    {
        $raiz = $sesion->documento;
        if ($raiz === null || $raiz->documento_padre_id !== null) {
            return null;
        }

        return $service->subirNuevaVersionDesdeRuta(
            $raiz,
            $ruta,
            $sesion->nombre_original,
            $mime,
            $tamano,
            $sesion->subido_por,
        );
    }

    private function detectarMime(string $ruta, string $nombreOriginal): string
    {
        $mime = @mime_content_type($ruta) ?: 'application/octet-stream';

        // finfo no reconoce planos (DWG/DXF) ni algunos formatos: si cae en
        // octet-stream, dejamos ese valor genérico (la descarga usa el nombre
        // original con su extensión de todos modos).
        return $mime;
    }

    private function avisarMiembros(Documento $documento, ?int $autorId): void
    {
        $obra = $documento->obra;
        if ($obra === null) {
            return;
        }

        $miembros = $obra->usuarios()
            ->when($autorId, fn ($q) => $q->where('users.id', '!=', $autorId))
            ->get();

        if ($miembros->isEmpty()) {
            return;
        }

        $autor = $autorId ? User::find($autorId) : null;
        Notification::send($miembros, new DocumentoSubido($documento, $autor?->name));
    }

    private function marcarError(UploadSession $sesion, \Throwable $e): void
    {
        Log::error('Falló la finalización de subida', [
            'upload_session' => $sesion->token,
            'error' => $e->getMessage(),
        ]);

        $sesion->update([
            'estado' => 'error',
            'error' => 'No se pudo procesar el archivo. Inténtalo de nuevo.',
        ]);
    }

    private function limpiar(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.'/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($dir);
    }
}
