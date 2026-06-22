<?php

namespace App\Http\Controllers;

use App\Http\Requests\AplicarPlantillaRequest;
use App\Http\Requests\StoreCarpetaRequest;
use App\Http\Requests\UpdateCarpetaRequest;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Services\CarpetaService;
use App\Services\PlantillaCarpetasService;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CarpetaController extends Controller
{
    public function index(Obra $obra, PlantillaCarpetasService $plantilla): Response
    {
        $this->authorize('viewAny', [Carpeta::class, $obra]);

        $carpetas = Carpeta::where('obra_id', $obra->id)
            ->orderBy('parent_id')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'parent_id', 'nombre', 'ruta', 'orden']);

        $obraData = [
            'id' => $obra->id,
            'codigo' => $obra->codigo,
            'nombre' => $obra->nombre,
        ];

        // Carpeta seleccionada (vía ?carpeta=ID).
        $carpetaSeleccionadaId = request()->integer('carpeta') ?: null;
        $documentos = [];
        $carpetaActiva = null;

        if ($carpetaSeleccionadaId) {
            $carpeta = $carpetas->firstWhere('id', $carpetaSeleccionadaId);
            if ($carpeta) {
                $carpetaActiva = [
                    'id' => $carpeta->id,
                    'nombre' => $carpeta->nombre,
                    'ruta' => $carpeta->ruta,
                ];
                $documentos = Documento::query()
                    ->where('carpeta_id', $carpeta->id)
                    ->vigentes()
                    ->with('subidoPor:id,name')
                    ->latest('updated_at')
                    ->get()
                    ->map(fn (Documento $d) => $this->serializarDocumento($d))
                    ->all();
            }
        }

        return Inertia::render('obras/documentos/index', [
            'obra' => $obraData,
            'carpetas' => $carpetas,
            'plantillaDisponible' => $plantilla->plantilla(),
            // puedeAdministrar = gestionar carpetas. Subir/eliminar documentos
            // son permisos independientes (un asistente puede subir sin gestionar
            // carpetas).
            'puedeAdministrar' => $this->puedeAdministrarObra($obra),
            'puedeSubir' => PermisosObra::puede(request()->user(), $obra, 'documento.subir'),
            'puedeEliminarDoc' => PermisosObra::puede(request()->user(), $obra, 'documento.eliminar'),
            'carpetaActiva' => $carpetaActiva,
            'documentos' => $documentos,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializarDocumento(Documento $d): array
    {
        return [
            'id' => $d->id,
            'nombre' => $d->nombre_original,
            'mime' => $d->mime,
            'tamano' => (int) $d->tamano,
            'tamano_humano' => $d->tamanoFormateado(),
            'version' => $d->version,
            'es_imagen' => $d->esImagen(),
            'es_pdf' => $d->esPdf(),
            'subido_por' => $d->subidoPor?->name,
            'updated_at' => $d->updated_at?->toIso8601String(),
            'url_preview' => route('obras.documentos.preview', [$d->obra_id, $d]),
            'url_descarga' => route('obras.documentos.descargar', [$d->obra_id, $d]),
        ];
    }

    public function store(StoreCarpetaRequest $request, Obra $obra): RedirectResponse
    {
        $data = $request->validated();
        $parent = isset($data['parent_id'])
            ? Carpeta::where('obra_id', $obra->id)->findOrFail($data['parent_id'])
            : null;

        $slug = Carpeta::slugify($data['nombre']);
        $ruta = $parent ? "{$parent->ruta}/{$slug}" : $slug;

        // Idempotente: si ya existe esa ruta, no la duplicamos.
        $existente = Carpeta::where('obra_id', $obra->id)
            ->where('ruta', $ruta)
            ->first();

        if (! $existente) {
            Carpeta::create([
                'obra_id' => $obra->id,
                'parent_id' => $parent?->id,
                'nombre' => $data['nombre'],
                'ruta' => $ruta,
                'orden' => 0,
                'creado_por' => $request->user()?->id,
            ]);
        }

        return back()->with('success', "Carpeta «{$data['nombre']}» creada.");
    }

    public function update(
        UpdateCarpetaRequest $request,
        Obra $obra,
        Carpeta $carpeta,
        CarpetaService $service,
    ): RedirectResponse {
        abort_unless($carpeta->obra_id === $obra->id, 404);

        $cambio = $service->renombrar($carpeta, $request->validated('nombre'));

        return $cambio
            ? back()->with('success', 'Carpeta renombrada.')
            : back();
    }

    public function destroy(Obra $obra, Carpeta $carpeta): RedirectResponse
    {
        $this->authorize('delete', $carpeta);
        abort_unless($carpeta->obra_id === $obra->id, 404);

        $nombre = $carpeta->nombre;
        $carpeta->delete();

        return back()->with('success', "Carpeta «{$nombre}» eliminada (junto con sus subcarpetas).");
    }

    public function aplicarPlantilla(
        AplicarPlantillaRequest $request,
        Obra $obra,
        PlantillaCarpetasService $service,
    ): RedirectResponse {
        $creadas = $service->aplicar(
            obra: $obra,
            seleccion: $request->validated('seleccion'),
            usuarioId: $request->user()?->id,
        );

        $msg = $creadas === 0
            ? 'No se crearon carpetas nuevas (todas las seleccionadas ya existían).'
            : "Se crearon {$creadas} carpetas nuevas a partir de la plantilla.";

        return redirect()
            ->route('obras.documentos.index', $obra)
            ->with('success', $msg);
    }

    private function puedeAdministrarObra(Obra $obra): bool
    {
        return request()->user()?->can('create', [Carpeta::class, $obra]) ?? false;
    }
}
