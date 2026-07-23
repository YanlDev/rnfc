<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Http\Controllers\Controller;
use App\Models\Obra;
use App\Support\PermisosObra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermisosController extends Controller
{
    public function index(Request $request): Response
    {
        $this->autorizar($request);

        $obra = $this->obraSeleccionada($request);

        $grupos = collect(PermisosObra::CATALOGO)
            ->map(fn (array $permisos, string $grupo) => [
                'grupo' => $grupo,
                'permisos' => collect($permisos)
                    ->map(fn (string $label, string $key) => [
                        'key' => $key,
                        'label' => $label,
                        // Reservado al Administrador de obra (p. ej. caja chica).
                        'solo_administrador' => in_array($key, PermisosObra::SOLO_ADMINISTRADOR, true),
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
            'matriz' => PermisosObra::estadoCompleto($obra),
            'obras' => Obra::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->map(fn (Obra $o) => [
                    'id' => $o->id,
                    'nombre' => $o->nombre,
                    'personalizada' => PermisosObra::tieneMatrizPropia($o),
                ])
                ->all(),
            'obraSeleccionada' => $obra?->id,
            'matrizPropia' => $obra !== null && PermisosObra::tieneMatrizPropia($obra),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->autorizar($request);

        $obra = $this->obraSeleccionada($request);

        $datos = $request->validate([
            'matriz' => ['required', 'array'],
            'matriz.*' => ['array'],
            'matriz.*.*' => ['boolean'],
        ]);

        PermisosObra::sincronizar($datos['matriz'], $obra);

        return back()->with('success', 'Permisos actualizados.');
    }

    /**
     * Elimina la matriz propia de una obra: vuelve a la matriz por defecto.
     */
    public function destroy(Request $request, Obra $obra): RedirectResponse
    {
        $this->autorizar($request);

        PermisosObra::restaurarDefecto($obra);

        return back()->with('success', 'La obra vuelve a usar los permisos por defecto.');
    }

    private function autorizar(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole(RolGlobal::rolesAdministrativos()) ?? false,
            403,
        );
    }

    /**
     * Obra a la que aplica la petición (`?obra=` / `obra` en el body),
     * o null para la matriz por defecto.
     */
    private function obraSeleccionada(Request $request): ?Obra
    {
        $id = $request->validate([
            'obra' => ['nullable', 'integer', 'exists:obras,id'],
        ])['obra'] ?? null;

        return $id !== null ? Obra::query()->findOrFail($id) : null;
    }
}
