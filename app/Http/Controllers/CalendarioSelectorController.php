<?php

namespace App\Http\Controllers;

use App\Enums\RolGlobal;
use App\Enums\TipoEvento;
use App\Models\EventoCalendario;
use App\Models\Obra;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarioSelectorController extends Controller
{
    /**
     * Vista global del calendario: muestra todos los eventos de las obras
     * accesibles para el usuario en una sola línea de tiempo unificada.
     */
    public function __invoke(): Response
    {
        $user = request()->user();
        abort_unless($user, 401);

        $vistaCompleta = $user->hasAnyRole(RolGlobal::rolesVisionGlobal());

        $obrasQuery = Obra::query()
            ->when(! $vistaCompleta, fn ($q) => $q->whereHas(
                'usuarios',
                fn ($qb) => $qb->where('users.id', $user->id),
            ));

        $obras = $obrasQuery->get(['id', 'codigo', 'nombre']);

        $eventos = EventoCalendario::query()
            ->whereIn('obra_id', $obras->pluck('id'))
            ->with('obra:id,codigo,nombre')
            ->orderBy('fecha_inicio')
            ->get()
            ->map(fn (EventoCalendario $e) => [
                'id' => $e->id,
                'obra' => [
                    'id' => $e->obra->id,
                    'codigo' => $e->obra->codigo,
                    'nombre' => $e->obra->nombre,
                ],
                'tipo' => $e->tipo->value,
                'tipo_label' => $e->tipo->label(),
                'color' => $e->color(),
                'titulo' => $e->titulo,
                'descripcion' => $e->descripcion,
                'fecha_inicio_iso' => $e->fecha_inicio?->format('Y-m-d'),
                'fecha_fin_iso' => $e->fecha_fin?->format('Y-m-d'),
                'todo_el_dia' => $e->todo_el_dia,
                'vencido' => $e->estaVencido(),
            ])
            ->all();

        // Próximos 14 días y vencidos
        $ahora = Carbon::now();
        $proximos = collect($eventos)
            ->filter(function ($e) use ($ahora) {
                $fecha = $e['fecha_inicio_iso'];
                if (! $fecha) {
                    return false;
                }
                $inicio = Carbon::parse($fecha);

                return $inicio->between($ahora->copy()->startOfDay(), $ahora->copy()->addDays(14)->endOfDay());
            })
            ->sortBy('fecha_inicio_iso')
            ->take(10)
            ->values()
            ->all();

        $vencidos = collect($eventos)
            ->filter(fn ($e) => $e['vencido'] && $e['tipo'] === TipoEvento::Vencimiento->value)
            ->sortByDesc('fecha_inicio_iso')
            ->take(10)
            ->values()
            ->all();

        return Inertia::render('calendario/index', [
            'eventos' => $eventos,
            'obras' => $obras->map(fn ($o) => [
                'id' => $o->id,
                'codigo' => $o->codigo,
                'nombre' => $o->nombre,
            ])->all(),
            'proximos' => $proximos,
            'vencidos' => $vencidos,
        ]);
    }
}
