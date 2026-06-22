<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Http\Controllers\Controller;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermisosController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(
            $request->user()?->hasAnyRole(RolGlobal::rolesAdministrativos()) ?? false,
            403,
        );

        $grupos = collect(PermisosObra::CATALOGO)
            ->map(fn (array $permisos, string $grupo) => [
                'grupo' => $grupo,
                'permisos' => collect($permisos)
                    ->map(fn (string $label, string $key) => [
                        'key' => $key,
                        'label' => $label,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('admin/permisos', [
            'grupos' => $grupos,
            'roles' => collect(RolObra::cases())
                ->map(fn (RolObra $r) => ['value' => $r->value, 'label' => $r->label()])
                ->all(),
            'matriz' => PermisosObra::estadoCompleto(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasAnyRole(RolGlobal::rolesAdministrativos()) ?? false,
            403,
        );

        $datos = $request->validate([
            'matriz' => ['required', 'array'],
            'matriz.*' => ['array'],
            'matriz.*.*' => ['boolean'],
        ]);

        PermisosObra::sincronizar($datos['matriz']);

        return back()->with('success', 'Permisos actualizados.');
    }
}
