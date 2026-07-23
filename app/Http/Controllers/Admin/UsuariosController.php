<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolGlobal;
use App\Http\Controllers\Controller;
use App\Models\Invitacion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Las rutas de este controlador viven bajo el grupo middleware('admin')
 * (ver routes/web.php): solo roles globales administrativos llegan aquí.
 */
class UsuariosController extends Controller
{
    /**
     * Listado de usuarios con búsqueda y filtros.
     */
    public function index(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $estado = $request->query('estado', 'todos'); // activos | desactivados | eliminados | todos
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
        } elseif ($estado === 'eliminados') {
            $query->onlyTrashed();
        }

        if ($rol !== 'todos') {
            $query->whereHas('roles', fn ($qb) => $qb->where('name', $rol));
        }

        $usuarios = $query->paginate(20)->withQueryString();

        // KPIs: una pasada sobre users + una query para admins (whereHas roles).
        $conteos = User::withTrashed()
            ->selectRaw('SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS total')
            ->selectRaw('SUM(CASE WHEN deleted_at IS NULL AND desactivado_at IS NULL THEN 1 ELSE 0 END) AS activos')
            ->selectRaw('SUM(CASE WHEN deleted_at IS NULL AND desactivado_at IS NOT NULL THEN 1 ELSE 0 END) AS desactivados')
            ->selectRaw('SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS eliminados')
            ->first();

        $kpis = [
            'total' => (int) $conteos->total,
            'activos' => (int) $conteos->activos,
            'desactivados' => (int) $conteos->desactivados,
            'admins' => User::whereHas('roles', fn ($qb) => $qb->where('name', RolGlobal::Admin->value))
                ->activos()
                ->count(),
            'eliminados' => (int) $conteos->eliminados,
        ];

        return Inertia::render('admin/usuarios', [
            'usuarios' => [
                'data' => $usuarios->getCollection()->map(function (User $u) {
                    $rolNombre = $u->roles->first()?->name;
                    $rolEnum = $rolNombre ? RolGlobal::tryFrom($rolNombre) : null;

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'rol' => $rolNombre,
                        'rol_label' => $rolEnum?->label() ?? '—',
                        'obras_count' => $u->obras_count,
                        'puede_crear_obras' => (bool) $u->puede_crear_obras,
                        'last_login_at' => $u->last_login_at?->format('Y-m-d H:i'),
                        'activo' => $u->estaActivo(),
                        'desactivado_at' => $u->desactivado_at?->format('Y-m-d H:i'),
                        'desactivado_por' => $u->desactivadoPor?->name,
                        'motivo_desactivacion' => $u->motivo_desactivacion,
                        'created_at' => $u->created_at?->format('Y-m-d'),
                        'eliminado_at' => $u->deleted_at?->format('Y-m-d H:i'),
                        'es_yo' => $u->id === Auth::id(),
                    ];
                })->all(),
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
            'invitacionesPendientes' => Invitacion::with('invitador:id,name')
                ->whereNotNull('rol_global')
                ->whereNull('aceptada_at')
                ->whereNull('cancelada_at')
                ->where('expira_at', '>', now())
                ->latest()
                ->get()
                ->map(fn (Invitacion $i) => [
                    'id' => $i->id,
                    'email' => $i->email,
                    'rol_global' => $i->rol_global->value,
                    'rol_global_label' => $i->rol_global->label(),
                    'expira_at' => $i->expira_at->toIso8601String(),
                    'invitador' => $i->invitador?->name,
                ])
                ->all(),
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
            DB::transaction(function () use ($request, $usuario) {
                if ($usuario->hasRole(RolGlobal::Admin->value)) {
                    $this->bloquearOperacionesDeAdmin();

                    if (! $this->hayOtroAdminActivo($usuario)) {
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
            });

            Log::warning('Usuario desactivado', [
                'usuario_id' => $usuario->id,
                'por' => $request->user()->id,
                'motivo' => $request->input('motivo'),
            ]);

            $mensaje = "Usuario {$usuario->name} desactivado.";
        } else {
            $usuario->forceFill([
                'desactivado_at' => null,
                'desactivado_por' => null,
                'motivo_desactivacion' => null,
            ])->save();

            Log::info('Usuario reactivado', [
                'usuario_id' => $usuario->id,
                'por' => $request->user()->id,
            ]);

            $mensaje = "Usuario {$usuario->name} reactivado.";
        }

        return redirect()->route('admin.usuarios.index')->with('success', $mensaje);
    }

    /**
     * Habilita o deshabilita al usuario para crear sus propias obras
     * (quedará como Administrador de obra de las que cree).
     */
    public function togglePuedeCrearObras(Request $request, User $usuario): RedirectResponse
    {
        $nuevo = ! $usuario->puede_crear_obras;
        $usuario->forceFill(['puede_crear_obras' => $nuevo])->save();

        Log::info('Permiso crear obras cambiado', [
            'usuario_id' => $usuario->id,
            'puede_crear_obras' => $nuevo,
            'por' => $request->user()->id,
        ]);

        return redirect()->route('admin.usuarios.index')->with(
            'success',
            $nuevo
                ? "{$usuario->name} ahora puede crear obras."
                : "{$usuario->name} ya no puede crear obras.",
        );
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

        DB::transaction(function () use ($usuario, $rolActual, $nuevoRol) {
            // Si está quitando admin, validar que quede al menos otro admin activo
            if ($rolActual === RolGlobal::Admin->value && $nuevoRol !== RolGlobal::Admin->value) {
                $this->bloquearOperacionesDeAdmin();

                if (! $this->hayOtroAdminActivo($usuario)) {
                    throw ValidationException::withMessages([
                        'rol' => 'No puedes quitar el rol de Administrador al único admin del sistema.',
                    ]);
                }
            }

            $usuario->syncRoles([$nuevoRol]);
        });

        Log::info('Rol global actualizado', [
            'usuario_id' => $usuario->id,
            'rol_anterior' => $rolActual,
            'rol_nuevo' => $nuevoRol,
            'por' => $request->user()->id,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Rol de {$usuario->name} actualizado a ".RolGlobal::from($nuevoRol)->label().'.');
    }

    /**
     * Envía un usuario a la papelera (soft delete). Se conserva la autoría en
     * certificados, cuadernos, caja, etc.; solo se le quita el acceso.
     */
    public function eliminar(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id) {
            throw ValidationException::withMessages([
                'usuario' => 'No puedes eliminar tu propia cuenta.',
            ]);
        }

        DB::transaction(function () use ($usuario) {
            // No dejar el sistema sin ningún administrador activo.
            if ($usuario->hasRole(RolGlobal::Admin->value)) {
                $this->bloquearOperacionesDeAdmin();

                if (! $this->hayOtroAdminActivo($usuario)) {
                    throw ValidationException::withMessages([
                        'usuario' => 'No puedes eliminar al único administrador activo.',
                    ]);
                }
            }

            // Cerrar sesiones activas para revocar el acceso de inmediato.
            DB::table('sessions')
                ->where('user_id', $usuario->id)
                ->delete();

            // No dejar enlaces de invitación vivos apuntando a una cuenta
            // en papelera (no podrían completarse jamás).
            Invitacion::where('email', $usuario->email)
                ->pendientes()
                ->update(['cancelada_at' => now()]);

            $usuario->delete();
        });

        Log::warning('Usuario eliminado (papelera)', [
            'usuario_id' => $usuario->id,
            'por' => $request->user()->id,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} enviado a la papelera.");
    }

    /**
     * Restaura un usuario que estaba en la papelera.
     */
    public function restaurar(Request $request, User $usuario): RedirectResponse
    {
        if (! $usuario->trashed()) {
            return redirect()->route('admin.usuarios.index');
        }

        $usuario->restore();

        Log::info('Usuario restaurado', [
            'usuario_id' => $usuario->id,
            'por' => $request->user()->id,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$usuario->name} restaurado.");
    }

    /**
     * Serializa las operaciones que pueden dejar el sistema sin admins:
     * dentro de una transacción, todas compiten por el lock de la misma
     * fila del rol admin, así el check-then-act no tiene carrera (dos
     * admins desactivándose mutuamente a la vez, por ejemplo).
     */
    private function bloquearOperacionesDeAdmin(): void
    {
        DB::table('roles')
            ->where('name', RolGlobal::Admin->value)
            ->lockForUpdate()
            ->first();
    }

    /**
     * ¿Queda al menos otro administrador activo además de $usuario?
     */
    private function hayOtroAdminActivo(User $usuario): bool
    {
        return User::whereHas('roles', fn ($q) => $q->where('name', RolGlobal::Admin->value))
            ->where('id', '!=', $usuario->id)
            ->activos()
            ->exists();
    }
}
