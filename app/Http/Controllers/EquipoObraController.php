<?php

namespace App\Http\Controllers;

use App\Enums\RolObra;
use App\Http\Requests\InvitarUsuarioRequest;
use App\Mail\InvitacionObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use App\Notifications\InvitacionRecibida;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class EquipoObraController extends Controller
{
    /**
     * Página de gestión del equipo de una obra.
     */
    public function index(Obra $obra): Response
    {
        $this->authorize('view', $obra);

        $obra->load([
            'usuarios' => fn ($q) => $q->orderBy('name'),
            'invitaciones' => fn ($q) => $q
                ->whereNull('aceptada_at')
                ->whereNull('cancelada_at')
                ->where('expira_at', '>', now())
                ->latest(),
            'invitaciones.invitador:id,name',
        ]);

        return Inertia::render('obras/equipo', [
            'obra' => [
                'id' => $obra->id,
                'codigo' => $obra->codigo,
                'nombre' => $obra->nombre,
            ],
            'equipo' => $obra->usuarios->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'rol_obra' => $u->pivot->rol_obra,
                'rol_obra_label' => RolObra::from($u->pivot->rol_obra)->label(),
                'asignado_at' => $u->pivot->asignado_at,
            ])->all(),
            'invitacionesPendientes' => $obra->invitaciones->map(fn (Invitacion $i) => [
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
            ])->all(),
            'puedeAdministrar' => request()->user()?->can('gestionarEquipo', $obra) ?? false,
        ]);
    }

    /**
     * Envía una invitación. Si el correo ya tiene cuenta, lo vincula directo.
     */
    public function invitar(InvitarUsuarioRequest $request, Obra $obra): RedirectResponse
    {
        $email = strtolower($request->validated('email'));
        $rolObra = RolObra::from($request->validated('rol_obra'));

        $usuarioExistente = User::withTrashed()->where('email', $email)->first();

        // Un usuario en la papelera conserva su email (unique), así que si se
        // invitara nunca podría completar el registro: hay que restaurarlo.
        if ($usuarioExistente?->trashed()) {
            return back()->withErrors([
                'email' => 'Este correo pertenece a un usuario que está en la papelera. Pide a un administrador restaurarlo desde Administración → Usuarios.',
            ]);
        }

        // Caso 1: el usuario ya existe → lo vinculamos directo, sin invitación.
        if ($usuarioExistente) {
            $yaVinculado = $obra->usuarios()->where('users.id', $usuarioExistente->id)->exists();

            if ($yaVinculado) {
                return back()->withErrors([
                    'email' => 'Este usuario ya forma parte del equipo de la obra.',
                ]);
            }

            $obra->usuarios()->attach($usuarioExistente->id, [
                'rol_obra' => $rolObra->value,
                'asignado_at' => now(),
            ]);

            $usuarioExistente->notify(
                new InvitacionRecibida($obra, $rolObra, $request->user()),
            );

            return back()->with(
                'success',
                "{$usuarioExistente->name} fue agregado(a) al equipo como {$rolObra->label()}.",
            );
        }

        // Caso 2: no existe → crear invitación pendiente y enviar correo.
        $yaInvitado = Invitacion::where('obra_id', $obra->id)
            ->where('email', $email)
            ->whereNull('aceptada_at')
            ->whereNull('cancelada_at')
            ->where('expira_at', '>', now())
            ->exists();

        if ($yaInvitado) {
            return back()->withErrors([
                'email' => 'Ya hay una invitación pendiente para este correo.',
            ]);
        }

        $token = Invitacion::generarToken();

        $invitacion = Invitacion::create([
            'obra_id' => $obra->id,
            'email' => $email,
            'rol_obra' => $rolObra->value,
            'token' => Invitacion::hashToken($token),
            'invitado_por' => $request->user()?->id,
            'expira_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new InvitacionObra($invitacion, $token));

        return back()->with(
            'success',
            "Invitación enviada a {$email}. El enlace expira en 7 días.",
        );
    }

    public function cambiarRol(Obra $obra, User $usuario): RedirectResponse
    {
        $this->authorize('gestionarEquipo', $obra);

        // Sin esto, updateExistingPivot sobre un no-miembro es un no-op
        // silencioso y se respondería "Rol actualizado" sin cambiar nada.
        abort_unless($obra->usuarios()->where('users.id', $usuario->id)->exists(), 404);

        $data = request()->validate([
            'rol_obra' => ['required', 'string', 'in:'.implode(',', RolObra::values())],
        ]);

        $obra->usuarios()->updateExistingPivot($usuario->id, [
            'rol_obra' => $data['rol_obra'],
        ]);

        return back()->with('success', 'Rol actualizado.');
    }

    public function remover(Obra $obra, User $usuario): RedirectResponse
    {
        $this->authorize('gestionarEquipo', $obra);

        abort_unless($obra->usuarios()->where('users.id', $usuario->id)->exists(), 404);

        $obra->usuarios()->detach($usuario->id);

        return back()->with('success', "{$usuario->name} fue removido(a) del equipo.");
    }

    public function cancelarInvitacion(Obra $obra, Invitacion $invitacion): RedirectResponse
    {
        $this->authorize('gestionarEquipo', $obra);

        abort_unless($invitacion->obra_id === $obra->id, 404);

        if ($invitacion->aceptada_at !== null) {
            return back()->withErrors([
                'invitacion' => 'Esta invitación ya fue aceptada; no se puede cancelar.',
            ]);
        }

        $invitacion->update(['cancelada_at' => now()]);

        return back()->with('success', 'Invitación cancelada.');
    }

    /**
     * Reenvía una invitación pendiente renovando token y expiración.
     * Una invitación aceptada o cancelada no se reenvía.
     */
    public function reenviarInvitacion(Obra $obra, Invitacion $invitacion): RedirectResponse
    {
        $this->authorize('gestionarEquipo', $obra);

        abort_unless($invitacion->obra_id === $obra->id, 404);

        if ($invitacion->aceptada_at !== null) {
            return back()->withErrors([
                'invitacion' => 'Esta invitación ya fue aceptada; no se puede reenviar.',
            ]);
        }

        if ($invitacion->cancelada_at !== null) {
            return back()->withErrors([
                'invitacion' => 'Esta invitación fue cancelada. Crea una invitación nueva.',
            ]);
        }

        $token = Invitacion::generarToken();

        $invitacion->update([
            'token' => Invitacion::hashToken($token),
            'expira_at' => now()->addDays(7),
        ]);

        Mail::to($invitacion->email)->send(new InvitacionObra($invitacion, $token));

        return back()->with('success', 'Invitación reenviada.');
    }
}
