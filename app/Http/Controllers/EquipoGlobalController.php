<?php

namespace App\Http\Controllers;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use Inertia\Inertia;
use Inertia\Response;

class EquipoGlobalController extends Controller
{
    /**
     * Vista global de equipo: cada obra como una tarjeta con su equipo,
     * invitaciones pendientes y acceso directo a "Gestionar equipo".
     */
    public function __invoke(): Response
    {
        $user = request()->user();
        abort_unless(
            $user?->hasAnyRole(RolGlobal::rolesVisionGlobal()),
            403,
        );

        $obras = Obra::query()
            ->with([
                'usuarios' => fn ($q) => $q->orderBy('name'),
                'invitaciones' => fn ($q) => $q
                    ->whereNull('aceptada_at')
                    ->whereNull('cancelada_at')
                    ->where('expira_at', '>', now())
                    ->latest(),
            ])
            ->orderBy('nombre')
            ->get()
            ->map(function (Obra $o) {
                return [
                    'id' => $o->id,
                    'codigo' => $o->codigo,
                    'nombre' => $o->nombre,
                    'entidad_contratante' => $o->entidad_contratante,
                    'estado_label' => $o->estado->label(),
                    'estado' => $o->estado->value,
                    'miembros' => $o->usuarios->map(fn ($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'rol_obra' => $u->pivot->rol_obra,
                        'rol_obra_label' => RolObra::tryFrom($u->pivot->rol_obra)?->label()
                            ?? $u->pivot->rol_obra,
                    ])->all(),
                    'invitaciones' => $o->invitaciones->map(fn ($i) => [
                        'id' => $i->id,
                        'email' => $i->email,
                        'rol_obra_label' => $i->rol_obra->label(),
                        'expira_at' => $i->expira_at->toIso8601String(),
                    ])->all(),
                ];
            })
            ->all();

        // Totales globales para el resumen superior.
        $totalMiembros = collect($obras)
            ->flatMap(fn ($o) => collect($o['miembros'])->pluck('email'))
            ->unique()
            ->count();
        $totalPendientes = collect($obras)->sum(fn ($o) => count($o['invitaciones']));

        return Inertia::render('equipo/index', [
            'obras' => $obras,
            'totales' => [
                'obras' => count($obras),
                'miembrosUnicos' => $totalMiembros,
                'invitacionesPendientes' => $totalPendientes,
            ],
        ]);
    }
}
