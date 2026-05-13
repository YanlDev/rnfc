<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Http\Requests\StoreObraRequest;
use App\Http\Requests\UpdateObraRequest;
use App\Models\EventoCalendario;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

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
                'monto_contractual' => $o->monto_contractual,
                'fecha_inicio' => $o->fecha_inicio?->format('Y-m-d'),
                'fecha_fin_prevista' => $o->fecha_fin_prevista?->format('Y-m-d'),
                'estado' => $o->estado->value,
                'estado_label' => $o->estado->label(),
                'creador' => $o->creador?->name,
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

        $obra->load([
            'creador:id,name',
            'usuarios' => fn ($q) => $q->orderBy('name'),
            'invitaciones' => fn ($q) => $q
                ->whereNull('aceptada_at')
                ->whereNull('cancelada_at')
                ->where('expira_at', '>', now())
                ->latest(),
            'invitaciones.invitador:id,name',
        ]);

        return Inertia::render('obras/show', [
            'obra' => $this->serializarObra($obra),
            'equipo' => $obra->usuarios->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'rol_obra' => $u->pivot->rol_obra,
                'rol_obra_label' => RolObra::from($u->pivot->rol_obra)->label(),
                'asignado_at' => $u->pivot->asignado_at,
            ])->all(),
            'invitacionesPendientes' => $obra->invitaciones->map(fn ($i) => [
                'id' => $i->id,
                'email' => $i->email,
                'rol_obra' => $i->rol_obra->value,
                'rol_obra_label' => $i->rol_obra->label(),
                'expira_at' => $i->expira_at->toIso8601String(),
                'invitador' => $i->invitador?->name,
            ])->all(),
            'rolesObra' => collect(RolObra::cases())->map(fn (RolObra $r) => [
                'value' => $r->value,
                'label' => $r->label(),
                'categoria' => $r->categoria(),
            ])->all(),
            'puedeAdministrar' => request()->user()?->can('update', $obra) ?? false,
            'eventosCalendario' => EventoCalendario::query()
                ->where('obra_id', $obra->id)
                ->orderBy('fecha_inicio')
                ->get(['id', 'tipo', 'titulo', 'fecha_inicio', 'fecha_fin'])
                ->map(fn (EventoCalendario $e) => [
                    'id' => $e->id,
                    'titulo' => $e->titulo,
                    'color' => $e->color(),
                    'fecha_inicio_iso' => $e->fecha_inicio?->format('Y-m-d'),
                    'fecha_fin_iso' => $e->fecha_fin?->format('Y-m-d'),
                ])
                ->all(),
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
        $obra->delete();

        return redirect()
            ->route('obras.index')
            ->with('success', "Obra {$codigo} eliminada junto con sus certificados.");
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
