<?php

namespace App\Http\Controllers;

use App\Jobs\FinalizarSubidaDocumento;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Models\UploadSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Inicia una sesión de subida en trozos. Devuelve el token, el tamaño de
     * trozo (fuente única de verdad: lo decide el server) y el total de trozos.
     */
    public function iniciar(Request $request, Obra $obra): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tamano' => ['required', 'integer', 'min:1', 'max:'.config('uploads.max_bytes')],
            'carpeta_id' => ['nullable', 'integer', 'required_without:documento_id'],
            'documento_id' => ['nullable', 'integer', 'required_without:carpeta_id'],
        ]);

        $carpeta = null;
        $documento = null;

        if (! empty($data['documento_id'])) {
            $documento = Documento::where('obra_id', $obra->id)
                ->whereNull('documento_padre_id')
                ->findOrFail($data['documento_id']);
            $this->authorize('update', $documento);
        } else {
            $carpeta = Carpeta::where('obra_id', $obra->id)->findOrFail($data['carpeta_id']);
            $this->authorize('create', [Documento::class, $carpeta]);
        }

        $chunkSize = (int) config('uploads.chunk_size');
        $totalChunks = (int) max(1, ceil($data['tamano'] / $chunkSize));

        $sesion = UploadSession::create([
            'obra_id' => $obra->id,
            'carpeta_id' => $carpeta?->id,
            'documento_id' => $documento?->id,
            'nombre_original' => $data['nombre'],
            'tamano_total' => $data['tamano'],
            'total_chunks' => $totalChunks,
            'estado' => 'pendiente',
            'subido_por' => $request->user()?->id,
        ]);

        return response()->json([
            'token' => $sesion->token,
            'chunk_size' => $chunkSize,
            'total_chunks' => $totalChunks,
        ]);
    }

    /**
     * Recibe un trozo (cuerpo binario crudo) y lo guarda en disco local.
     * Cada request es pequeño (~chunk_size), así nginx/PHP no necesitan
     * límites grandes.
     */
    public function chunk(Request $request, UploadSession $sesion): JsonResponse
    {
        $this->autorizarSesion($request, $sesion);

        abort_if(in_array($sesion->estado, ['procesando', 'completado'], true), 409, 'La subida ya está en proceso.');

        $index = (int) $request->query('index', '-1');
        abort_if($index < 0 || $index >= $sesion->total_chunks, 422, 'Índice de trozo inválido.');

        $contenido = $request->getContent();
        $limite = (int) config('uploads.chunk_size') + 1024; // pequeño margen
        abort_if($contenido === '' || strlen($contenido) > $limite, 422, 'Trozo vacío o demasiado grande.');

        $dir = $sesion->directorioTemporal();
        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            abort(500, 'No se pudo preparar el almacenamiento temporal.');
        }

        if (file_put_contents($dir.'/'.$index.'.part', $contenido) === false) {
            abort(500, 'No se pudo guardar el trozo.');
        }

        $recibidos = count(glob($dir.'/*.part') ?: []);
        $sesion->update([
            'estado' => 'subiendo',
            'chunks_recibidos' => $recibidos,
        ]);

        return response()->json(['recibidos' => $recibidos]);
    }

    /**
     * Verifica que llegaron todos los trozos y despacha el Job que los
     * reensambla y los sube a Bunny en streaming.
     */
    public function completar(Request $request, UploadSession $sesion): JsonResponse
    {
        $this->autorizarSesion($request, $sesion);

        if (in_array($sesion->estado, ['procesando', 'completado'], true)) {
            return response()->json(['estado' => $sesion->estado]);
        }

        $dir = $sesion->directorioTemporal();
        $recibidos = count(glob($dir.'/*.part') ?: []);
        abort_if($recibidos !== $sesion->total_chunks, 422, 'Faltan trozos por subir.');

        $sesion->update(['estado' => 'procesando']);
        FinalizarSubidaDocumento::dispatch($sesion);

        return response()->json(['estado' => 'procesando']);
    }

    /**
     * Estado de la sesión para el polling del frontend.
     */
    public function estado(Request $request, UploadSession $sesion): JsonResponse
    {
        $this->autorizarSesion($request, $sesion);

        return response()->json([
            'estado' => $sesion->estado,
            'error' => $sesion->error,
            'documento_id' => $sesion->documento_resultante_id,
        ]);
    }

    private function autorizarSesion(Request $request, UploadSession $sesion): void
    {
        abort_unless($sesion->subido_por === $request->user()?->id, 403);
    }
}
