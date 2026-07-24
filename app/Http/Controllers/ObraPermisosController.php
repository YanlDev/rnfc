<?php

namespace App\Http\Controllers;

use App\Enums\RolObra;
use App\Models\Obra;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Configuración de la matriz de permisos de UNA obra, por su Administrador de
 * obra (o el Admin de plataforma). A diferencia de Admin\PermisosController,
 * está acotado a la obra: no muestra la matriz global ni otras obras, y no
 * deja tocar los poderes del propio rol Administrador.
 */
class ObraPermisosController extends Controller
{
    public function index(Obra $obra): Response
    {
        $this->authorize('gestionarPermisos', $obra);

        $grupos = collect(PermisosObra::CATALOGO)
            ->map(fn (array $permisos, string $grupo) => [
                'grupo' => $grupo,
                'permisos' => collect($permisos)
                    ->map(fn (string $label, string $key) => [
                        'key' => $key,
                        'label' => $label,
                        'solo_administrador' => in_array($key, PermisosObra::SOLO_ADMINISTRADOR, true),
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('obras/permisos', [
            'obra' => [
                'id' => $obra->id,
                'codigo' => $obra->codigo,
                'nombre' => $obra->nombre,
            ],
            'grupos' => $grupos,
            // El rol Administrador se muestra como referencia, pero no editable.
            'roles' => collect(RolObra::cases())
                ->map(fn (RolObra $r) => [
                    'value' => $r->value,
                    'label' => $r->label(),
                    'editable' => $r !== RolObra::Administrador,
                ])
                ->all(),
            'matriz' => PermisosObra::estadoCompleto($obra),
            'personalizada' => PermisosObra::tieneMatrizPropia($obra),
        ]);
    }

    public function update(Request $request, Obra $obra): RedirectResponse
    {
        $this->authorize('gestionarPermisos', $obra);

        $datos = $request->validate([
            'matriz' => ['required', 'array'],
            'matriz.*' => ['array'],
            'matriz.*.*' => ['boolean'],
        ]);

        PermisosObra::sincronizarObra($datos['matriz'], $obra);

        return back()->with('success', 'Permisos del equipo actualizados.');
    }

    public function destroy(Obra $obra): RedirectResponse
    {
        $this->authorize('gestionarPermisos', $obra);

        PermisosObra::restaurarDefecto($obra);

        return back()->with('success', 'El equipo vuelve a los permisos por defecto.');
    }
}
