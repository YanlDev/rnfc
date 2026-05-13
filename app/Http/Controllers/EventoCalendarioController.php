<?php

namespace App\Http\Controllers;

use App\Enums\TipoEvento;
use App\Http\Requests\StoreEventoCalendarioRequest;
use App\Models\EventoCalendario;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventoCalendarioController extends Controller
{
    public function index(Obra $obra): Response
    {
        $this->authorize('viewAny', [EventoCalendario::class, $obra]);

        $eventos = EventoCalendario::query()
            ->where('obra_id', $obra->id)
            ->with('creador:id,name')
            ->orderBy('fecha_inicio')
            ->get()
            ->map(fn (EventoCalendario $e) => $this->serializar($e))
            ->all();

        return Inertia::render('obras/calendario/index', [
            'obra' => [
                'id' => $obra->id,
                'codigo' => $obra->codigo,
                'nombre' => $obra->nombre,
            ],
            'eventos' => $eventos,
            'tipos' => collect(TipoEvento::cases())->map(fn (TipoEvento $t) => [
                'value' => $t->value,
                'label' => $t->label(),
                'color' => $t->color(),
            ])->all(),
            'puedeEditar' => request()->user()?->can('create', [EventoCalendario::class, $obra]) ?? false,
        ]);
    }

    public function store(StoreEventoCalendarioRequest $request, Obra $obra): RedirectResponse
    {
        EventoCalendario::create([
            ...$request->validated(),
            'obra_id' => $obra->id,
            'creado_por' => $request->user()?->id,
        ]);

        return back()->with('success', 'Evento agregado.');
    }

    public function update(
        StoreEventoCalendarioRequest $request,
        Obra $obra,
        EventoCalendario $evento,
    ): RedirectResponse {
        abort_unless($evento->obra_id === $obra->id, 404);

        $evento->update($request->validated());

        return back()->with('success', 'Evento actualizado.');
    }

    public function destroy(Obra $obra, EventoCalendario $evento): RedirectResponse
    {
        abort_unless($evento->obra_id === $obra->id, 404);
        $this->authorize('delete', $evento);

        $evento->delete();

        return back()->with('success', 'Evento eliminado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializar(EventoCalendario $e): array
    {
        return [
            'id' => $e->id,
            'tipo' => $e->tipo->value,
            'tipo_label' => $e->tipo->label(),
            'color' => $e->color(),
            'titulo' => $e->titulo,
            'descripcion' => $e->descripcion,
            'fecha_inicio' => $e->fecha_inicio?->toIso8601String(),
            'fecha_fin' => $e->fecha_fin?->toIso8601String(),
            'fecha_inicio_iso' => $e->fecha_inicio?->format('Y-m-d'),
            'fecha_fin_iso' => $e->fecha_fin?->format('Y-m-d'),
            'todo_el_dia' => $e->todo_el_dia,
            'vencido' => $e->estaVencido(),
            'creador' => $e->creador?->name,
        ];
    }
}
