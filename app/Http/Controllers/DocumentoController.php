<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Notifications\DocumentoSubido;
use App\Services\DocumentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function __construct(private readonly DocumentoService $service) {}

    public function store(Request $request, Obra $obra, Carpeta $carpeta): RedirectResponse
    {
        abort_unless($carpeta->obra_id === $obra->id, 404);
        $this->authorize('create', [Documento::class, $carpeta]);

        $request->validate([
            'archivo' => ['required', 'file', 'max:51200'], // 50 MB por archivo
        ]);

        $documento = $this->service->subir($carpeta, $request->file('archivo'), $request->user()?->id);

        $this->avisarMiembros($carpeta->obra, $documento, $request->user());

        return back()->with('success', 'Archivo subido.');
    }

    public function storeVersion(Request $request, Obra $obra, Documento $documento): RedirectResponse
    {
        abort_unless($documento->obra_id === $obra->id, 404);
        abort_unless($documento->documento_padre_id === null, 422, 'Sólo puedes versionar el documento vigente.');
        $this->authorize('update', $documento);

        $request->validate([
            'archivo' => ['required', 'file', 'max:51200'],
        ]);

        $actualizado = $this->service->subirNuevaVersion($documento, $request->file('archivo'), $request->user()?->id);

        $this->avisarMiembros($obra, $actualizado, $request->user());

        return back()->with('success', "Nueva versión guardada.");
    }

    public function destroy(Obra $obra, Documento $documento): RedirectResponse
    {
        abort_unless($documento->obra_id === $obra->id, 404);
        abort_unless($documento->documento_padre_id === null, 422);
        $this->authorize('delete', $documento);

        $nombre = $documento->nombre_original;
        $this->service->eliminar($documento);

        return back()->with('success', "Documento «{$nombre}» eliminado.");
    }

    public function descargar(Obra $obra, Documento $documento): StreamedResponse
    {
        abort_unless($documento->obra_id === $obra->id, 404);
        $this->authorize('view', $documento);

        return Storage::disk('documentos')->download(
            $documento->archivo_path,
            $documento->nombre_original,
        );
    }

    /**
     * Notifica a los miembros de la obra (excepto al autor) sobre un nuevo
     * documento o nueva versión.
     */
    private function avisarMiembros(Obra $obra, Documento $documento, ?\App\Models\User $autor): void
    {
        $miembros = $obra->usuarios()
            ->when($autor, fn ($q) => $q->where('users.id', '!=', $autor->id))
            ->get();

        if ($miembros->isEmpty()) {
            return;
        }

        Notification::send($miembros, new DocumentoSubido($documento, $autor?->name));
    }

    /**
     * Sirve el archivo inline para previsualización (iframe PDF, <img>, etc.).
     */
    public function preview(Obra $obra, Documento $documento): StreamedResponse
    {
        abort_unless($documento->obra_id === $obra->id, 404);
        $this->authorize('view', $documento);

        $disk = Storage::disk('documentos');

        return $disk->response(
            $documento->archivo_path,
            $documento->nombre_original,
            ['Content-Type' => $documento->mime],
        );
    }
}
