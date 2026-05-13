<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolGlobal;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UsuariosController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(function (Request $request, \Closure $next) {
                $user = $request->user();
                abort_unless($user && $user->hasAnyRole(RolGlobal::rolesAdministrativos()), 403);

                return $next($request);
            }),
        ];
    }

    /**
     * Listado de usuarios con búsqueda y filtros.
     */
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', 'todos'); // activos | desactivados | todos
        $rol = $request->query('rol', 'todos');

        $query = User::query()
            ->with(['roles:id,name', 'desactivadoPor:id,name'])
            ->withCount('obras')
            ->orderBy('name');

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'ilike', "%{$q}%")
                    ->orWhere('email', 'ilike', "%{$q}%");
            });
        }

        if ($estado === 'activos') {
            $query->activos();
        } elseif ($estado === 'desactivados') {
            $query->desactivados();
        }

        if ($rol !== 'todos') {
            $query->whereHas('roles', fn ($qb) => $qb->where('name', $rol));
        }

        $usuarios = $query->paginate(20)->withQueryString();

        // KPIs
        $kpis = [
            'total' => User::count(),
            'activos' => User::activos()->count(),
            'desactivados' => User::desactivados()->count(),
            'admins' => User::whereHas('roles', fn ($qb) => $qb->where('name', RolGlobal::Admin->value))
                ->activos()
                ->count(),
        ];

        return Inertia::render('admin/usuarios', [
            'usuarios' => [
                'data' => $usuarios->getCollection()->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'rol' => $u->roles->first()?->name,
                    'rol_label' => $u->roles->first()
                        ? RolGlobal::from($u->roles->first()->name)->label()
                        : '—',
                    'obras_count' => $u->obras_count,
                    'last_login_at' => $u->last_login_at?->format('Y-m-d H:i'),
                    'activo' => $u->estaActivo(),
                    'desactivado_at' => $u->desactivado_at?->format('Y-m-d H:i'),
                    'desactivado_por' => $u->desactivadoPor?->name,
                    'motivo_desactivacion' => $u->motivo_desactivacion,
                    'created_at' => $u->created_at?->format('Y-m-d'),
                    'es_yo' => $u->id === Auth::id(),
                ])->all(),
                'links' => $usuarios->linkCollection()->all(),
                'meta' => [
                    'current_page' => $usuarios->currentPage(),
                    'last_page' => $usuarios->lastPage(),
                    'total' => $usuarios->total(),
                    'from' => $usuarios->firstItem(),
                    'to' => $usuarios->lastItem(),
                ],
            ],
            'filtros' => [
                'q' => $q,
                'estado' => $estado,
                'rol' => $rol,
            ],
            'roles' => collect(RolGlobal::cases())->map(fn (RolGlobal $r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ])->all(),
            'kpis' => $kpis,
        ]);
    }

    /**
     * Desactiva o reactiva un usuario.
     */
    public function toggleActivo(Request $request, User $usuario): RedirectResponse
    {
        $request->validate([
            'motivo' => ['nullable', 'string', 'max:250'],
        ]);

        // Reglas: no puedes desactivarte a ti mismo
        if ($usuario->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'usuario' => 'No puedes desactivar tu propia cuenta.',
            ]);
        }

        if ($usuario->estaActivo()) {
            // Vamos a desactivarlo. Validar que no sea el último admin activo.
            if ($usuario->hasRole(RolGlobal::Admin->value)) {
                $adminsActivos = User::whereHas('roles', fn ($q) => $q->where('name', RolGlobal::Admin->value))
                    ->activos()
                    ->count();

                if ($adminsActivos <= 1) {
                    throw ValidationException::withMessages([
                        'usuario' => 'No puedes desactivar al único administrador activo.',
                    ]);
                }
            }

            $usuario->forceFill([
                'desactivado_at' => now(),
                'desactivado_por' => $request->user()->id,
                'motivo_desactivacion' => $request->input('motivo'),
            ])->save();

            // Cerrar sesiones activas del usuario
            DB::table('sessions')
                ->where('user_id', $usuario->id)
                ->delete();

            $mensaje = "Usuario {$usuario->name} desactivado.";
        } else {
            $usuario->forceFill([
                'desactivado_at' => null,
                'desactivado_por' => null,
                'motivo_desactivacion' => null,
            ])->save();

            $mensaje = "Usuario {$usuario->name} reactivado.";
        }

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
    }

    /**
     * Cambia el rol global del usuario.
     */
    public function cambiarRol(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'rol' => ['required', Rule::in(RolGlobal::values())],
        ]);

        $nuevoRol = $validated['rol'];
        $rolActual = $usuario->roles->first()?->name;

        if ($rolActual === $nuevoRol) {
            return redirect()->route('admin.usuarios.index');
        }

        // Si está quitando admin, validar que quede al menos otro admin activo
        if ($rolActual === RolGlobal::Admin->value && $nuevoRol !== RolGlobal::Admin->value) {
            $otrosAdmins = User::whereHas('roles', fn ($q) => $q->where('name', RolGlobal::Admin->value))
                ->where('id', '!=', $usuario->id)
                ->activos()
                ->count();

            if ($otrosAdmins === 0) {
                throw ValidationException::withMessages([
                    'rol' => 'No puedes quitar el rol de Administrador al único admin del sistema.',
                ]);
            }
        }

        $usuario->syncRoles([$nuevoRol]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Rol de {$usuario->name} actualizado a ".RolGlobal::from($nuevoRol)->label().'.');
    }
}
