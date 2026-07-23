<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Enums\RolGlobal;
use App\Http\Requests\StoreObraRequest;
use App\Http\Requests\UpdateObraRequest;
use App\Models\CajaMovimiento;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ObraController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Obra::class);

        $filtros = request()->only(['q', 'estado']);
        $user = request()->user();
        $vistaCompleta = $user?->hasAnyRole(RolGlobal::rolesVisionGlobal()) ?? false;

        $obras = Obra::query()
            ->with('creador:id,name')
            ->withCount(['documentos', 'asientosCuaderno', 'eventosCalendario'])
            ->when(! $vistaCompleta, fn ($q) => $q->whereHas('usuarios', fn ($qb) => $qb->where('users.id', $user?->id)))
            ->when($filtros['q'] ?? null, function ($query, $q) {
                $like = '%'.mb_strtolower($q).'%';
                $query->where(function ($qb) use ($like) {
                    $qb->whereRaw('lower(codigo) like ?', [$like])
                        ->orWhereRaw('lower(nombre) like ?', [$like])
                        ->orWhereRaw('lower(coalesce(entidad_contratante, \'\')) like ?', [$like]);
                });
            })
            ->when($filtros['estado'] ?? null, fn ($query, $estado) => $query->where('estado', $estado))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Obra $o) => [
                'id' => $o->id,
                'codigo' => $o->codigo,
                'nombre' => $o->nombre,
                'ubicacion' => $o->ubicacion,
                'entidad_contratante' => $o->entidad_contratante,
                'fecha_inicio' => $o->fecha_inicio?->format('Y-m-d'),
                'fecha_fin_prevista' => $o->fecha_fin_prevista?->format('Y-m-d'),
                'estado' => $o->estado->value,
                'estado_label' => $o->estado->label(),
                'imagen_url' => $o->imagen_path
                    ? route('obras.imagen.show', $o).'?v='.$o->updated_at?->timestamp
                    : null,
                'documentos_count' => $o->documentos_count,
                'cuaderno_count' => $o->asientos_cuaderno_count,
            ]);

        return Inertia::render('obras/index', [
            'obras' => $obras,
            'filtros' => $filtros,
            'estados' => $this->estadosOpciones(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Obra::class);

        return Inertia::render('obras/create', [
            'estados' => $this->estadosOpciones(),
            'codigoSugerido' => $this->sugerirCodigo(),
        ]);
    }

    public function store(StoreObraRequest $request): RedirectResponse
    {
        $obra = Obra::create([
            ...$request->validated(),
            'creado_por' => $request->user()?->id,
        ]);

        return redirect()
            ->route('obras.show', $obra)
            ->with('success', "Obra {$obra->codigo} creada correctamente.");
    }

    public function show(Obra $obra): Response
    {
        $this->authorize('view', $obra);

        $obra->load('creador:id,name')->loadCount([
            'documentos',
            'asientosCuaderno',
            'eventosCalendario',
            'usuarios',
            'cajaMovimientos',
        ]);

        return Inertia::render('obras/show', [
            'obra' => $this->serializarObra($obra),
            'contadores' => [
                'documentos' => $obra->documentos_count,
                'cuaderno' => $obra->asientos_cuaderno_count,
                'calendario' => $obra->eventos_calendario_count,
                'equipo' => $obra->usuarios_count,
                'caja' => $obra->caja_movimientos_count,
            ],
            'puedeAdministrar' => request()->user()?->can('update', $obra) ?? false,
            'puedeVerCaja' => request()->user()?->can('viewAny', [CajaMovimiento::class, $obra]) ?? false,
        ]);
    }

    public function edit(Obra $obra): Response
    {
        $this->authorize('update', $obra);

        return Inertia::render('obras/edit', [
            'obra' => $this->serializarObra($obra),
            'estados' => $this->estadosOpciones(),
        ]);
    }

    public function update(UpdateObraRequest $request, Obra $obra): RedirectResponse
    {
        $obra->update($request->validated());

        return redirect()
            ->route('obras.show', $obra)
            ->with('success', "Obra {$obra->codigo} actualizada.");
    }

    public function destroy(Obra $obra): RedirectResponse
    {
        $this->authorize('delete', $obra);

        $codigo = $obra->codigo;
        $obraId = $obra->id;
        $imagenPath = $obra->imagen_path;

        DB::transaction(function () use ($obra) {
            // Los certificados son permanentes (verificación pública). Antes de
            // que la FK los desvincule (nullOnDelete), copiamos el nombre y la
            // entidad de la obra a sus campos "libres" para que sigan siendo
            // autodescriptivos. Se truncan a 255 (obra.nombre es TEXT; el campo
            // libre es VARCHAR(255)) y sólo se rellenan si estaban vacíos.
            $obra->certificados()->whereNull('obra_nombre_libre')->update([
                'obra_nombre_libre' => mb_substr((string) $obra->nombre, 0, 255),
            ]);

            if ($obra->entidad_contratante !== null) {
                $obra->certificados()->whereNull('obra_entidad_libre')->update([
                    'obra_entidad_libre' => mb_substr($obra->entidad_contratante, 0, 255),
                ]);
            }

            $obra->delete();
        });

        // Todos los archivos de la obra (documentos, _caja, _cuaderno) viven
        // bajo obras/{id}; la portada vive aparte en obras/imagenes/. Borrar
        // ambos evita huérfanos en el disco. Best-effort: si el disco remoto
        // falla, se registra para limpieza.
        try {
            Storage::disk('documentos')->deleteDirectory("obras/{$obraId}");

            if ($imagenPath) {
                Storage::disk('documentos')->delete($imagenPath);
            }
        } catch (\Throwable $e) {
            Log::warning('No se pudieron borrar los archivos de la obra', [
                'obra_id' => $obraId,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('obras.index')
            ->with('success', "Obra {$codigo} eliminada. Sus certificados se conservan para verificación.");
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function estadosOpciones(): array
    {
        return collect(EstadoObra::cases())
            ->map(fn (EstadoObra $e) => ['value' => $e->value, 'label' => $e->label()])
            ->all();
    }

    /**
     * Sugiere un código secuencial OBR-YYYY-NNNN basado en el último registro del año.
     */
    private function sugerirCodigo(): string
    {
        $anio = (int) now()->format('Y');
        $prefijo = "OBR-{$anio}-";
        $ultima = Obra::query()
            ->where('codigo', 'like', $prefijo.'%')
            ->orderByDesc('codigo')
            ->value('codigo');

        $siguiente = 1;
        if ($ultima && preg_match('/-(\d+)$/', $ultima, $m)) {
            $siguiente = ((int) $m[1]) + 1;
        }

        return $prefijo.str_pad((string) $siguiente, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>
     */
    public function actualizarImagen(Request $request, Obra $obra): RedirectResponse
    {
        $this->authorize('update', $obra);

        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $disk = Storage::disk('documentos');

        if ($obra->imagen_path) {
            $disk->delete($obra->imagen_path);
        }

        $obra->update([
            'imagen_path' => $request->file('imagen')->store('obras/imagenes', 'documentos'),
        ]);

        return back()->with('success', 'Imagen del proyecto actualizada.');
    }

    public function eliminarImagen(Obra $obra): RedirectResponse
    {
        $this->authorize('update', $obra);

        if ($obra->imagen_path) {
            Storage::disk('documentos')->delete($obra->imagen_path);
            $obra->update(['imagen_path' => null]);
        }

        return back()->with('success', 'Imagen del proyecto eliminada.');
    }

    public function mostrarImagen(Obra $obra): StreamedResponse
    {
        $this->authorize('view', $obra);

        $disk = Storage::disk('documentos');
        abort_unless($obra->imagen_path && $disk->exists($obra->imagen_path), 404);

        // Portada restringida a imágenes rasterizadas al subir (mimes:jpg,png,webp),
        // por lo que inline es seguro. La URL ya lleva ?v=timestamp, así que
        // podemos cachear de forma inmutable. nosniff por defensa en profundidad.
        return $disk->response($obra->imagen_path, null, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=31536000, immutable',
        ]);
    }

    private function serializarObra(Obra $obra): array
    {
        return [
            'id' => $obra->id,
            'codigo' => $obra->codigo,
            'nombre' => $obra->nombre,
            'descripcion' => $obra->descripcion,
            'ubicacion' => $obra->ubicacion,
            'latitud' => $obra->latitud,
            'longitud' => $obra->longitud,
            'imagen_url' => $obra->imagen_path
                ? route('obras.imagen.show', $obra).'?v='.$obra->updated_at?->timestamp
                : null,
            'entidad_contratante' => $obra->entidad_contratante,
            'monto_contractual' => $obra->monto_contractual !== null
                ? (float) $obra->monto_contractual
                : null,
            'fecha_inicio' => $obra->fecha_inicio?->format('Y-m-d'),
            'fecha_fin_prevista' => $obra->fecha_fin_prevista?->format('Y-m-d'),
            'fecha_fin_real' => $obra->fecha_fin_real?->format('Y-m-d'),
            'estado' => $obra->estado->value,
            'estado_label' => $obra->estado->label(),
            'creador' => $obra->creador?->name,
            'created_at' => $obra->created_at?->toIso8601String(),
            'updated_at' => $obra->updated_at?->toIso8601String(),
        ];
    }
}
