<?php

namespace App\Http\Controllers;

use App\Http\Requests\AplicarPlantillaRequest;
use App\Http\Requests\StoreCarpetaRequest;
use App\Http\Requests\UpdateCarpetaRequest;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Services\PlantillaCarpetasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
            'puedeAdministrar' => $this->puedeAdministrarObra($obra),
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

    public function update(UpdateCarpetaRequest $request, Obra $obra, Carpeta $carpeta): RedirectResponse
    {
        abort_unless($carpeta->obra_id === $obra->id, 404);

        $nuevoNombre = $request->validated('nombre');
        $nuevoSlug = Carpeta::slugify($nuevoNombre);
        $parentRuta = $carpeta->parent_id
            ? Carpeta::where('id', $carpeta->parent_id)->value('ruta')
            : null;
        $nuevaRuta = $parentRuta ? "{$parentRuta}/{$nuevoSlug}" : $nuevoSlug;

        // Si no cambia la ruta efectiva, sólo actualizamos el nombre.
        if ($nuevaRuta === $carpeta->ruta && $nuevoNombre === $carpeta->nombre) {
            return back();
        }

        // Verificar conflicto: que la nueva ruta no exista ya en la obra.
        $existe = Carpeta::where('obra_id', $obra->id)
            ->where('ruta', $nuevaRuta)
            ->where('id', '!=', $carpeta->id)
            ->exists();
        if ($existe) {
            return back()->withErrors([
                'nombre' => 'Ya existe una carpeta con ese nombre en la misma ubicación.',
            ]);
        }

        DB::transaction(function () use ($carpeta, $nuevoNombre, $nuevaRuta) {
            $rutaVieja = $carpeta->ruta;
            $carpeta->update(['nombre' => $nuevoNombre, 'ruta' => $nuevaRuta]);

            // Recalcular la ruta de TODOS los descendientes reemplazando
            // el prefijo viejo por el nuevo. Esto mantiene la metadata
            // organizativa; los archivos físicos no se mueven (archivo_path
            // queda con su ubicación original — el carpeta_id es la fuente
            // de verdad lógica).
            Carpeta::where('obra_id', $carpeta->obra_id)
                ->where('ruta', 'like', $rutaVieja.'/%')
                ->get()
                ->each(function (Carpeta $hijo) use ($rutaVieja, $nuevaRuta) {
                    $hijo->update([
                        'ruta' => $nuevaRuta.substr($hijo->ruta, strlen($rutaVieja)),
                    ]);
                });
        });

        return back()->with('success', 'Carpeta renombrada.');
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
