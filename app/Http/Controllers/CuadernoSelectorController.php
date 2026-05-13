<?php

namespace App\Http\Controllers;

use App\Enums\RolGlobal;
use App\Enums\TipoAutorCuaderno;
use App\Models\AsientoCuaderno;
use App\Models\Obra;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CuadernoSelectorController extends Controller
{
    /**
     * Selector global: muestra las obras a las que el usuario puede acceder
     * y le permite saltar al cuaderno de cada una. Si sólo hay una obra
     * disponible, redirige directamente.
     */
    public function __invoke(): Response|RedirectResponse
    {
        $user = request()->user();
        abort_unless($user, 401);

        $vistaCompleta = $user->hasAnyRole(RolGlobal::rolesVisionGlobal());

        $obras = Obra::query()
            ->when(! $vistaCompleta, fn ($q) => $q->whereHas(
                'usuarios',
                fn ($qb) => $qb->where('users.id', $user->id),
            ))
            ->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'entidad_contratante', 'estado']);

        // Si hay una sola obra accesible, saltar directo.
        if ($obras->count() === 1) {
            return redirect()->route('obras.cuaderno.index', ['obra' => $obras->first()->id]);
        }

        $obrasConConteo = $obras->map(function (Obra $o) {
            $totales = AsientoCuaderno::query()
                ->where('obra_id', $o->id)
                ->selectRaw('tipo_autor, count(*) as total')
                ->groupBy('tipo_autor')
                ->pluck('total', 'tipo_autor')
                ->all();

            return [
                'id' => $o->id,
                'codigo' => $o->codigo,
                'nombre' => $o->nombre,
                'entidad_contratante' => $o->entidad_contratante,
                'estado' => $o->estado->value,
                'estado_label' => $o->estado->label(),
                'asientos_supervisor' => (int) ($totales[TipoAutorCuaderno::Supervisor->value] ?? 0),
                'asientos_residente' => (int) ($totales[TipoAutorCuaderno::Residente->value] ?? 0),
            ];
        })->all();

        return Inertia::render('cuaderno/index', [
            'obras' => $obrasConConteo,
        ]);
    }
}
