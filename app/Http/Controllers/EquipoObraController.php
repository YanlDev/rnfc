<?php

namespace App\Http\Controllers;

use App\Enums\RolObra;
use App\Http\Requests\InvitarUsuarioRequest;
use App\Mail\InvitacionObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EquipoObraController extends Controller
{
    /**
     * Envía una invitación. Si el correo ya tiene cuenta, lo vincula directo.
     */
    public function invitar(InvitarUsuarioRequest $request, Obra $obra): RedirectResponse
    {
        $email = strtolower($request->validated('email'));
        $rolObra = RolObra::from($request->validated('rol_obra'));

        $usuarioExistente = User::where('email', $email)->first();

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

        $invitacion = Invitacion::create([
            'obra_id' => $obra->id,
            'email' => $email,
            'rol_obra' => $rolObra->value,
            'token' => Invitacion::generarToken(),
            'invitado_por' => $request->user()?->id,
            'expira_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new InvitacionObra($invitacion));

        return back()->with(
            'success',
            "Invitación enviada a {$email}. El enlace expira en 7 días.",
        );
    }

    public function cambiarRol(Obra $obra, User $usuario): RedirectResponse
    {
        $this->authorize('update', $obra);

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
        $this->authorize('update', $obra);

        $obra->usuarios()->detach($usuario->id);

        return back()->with('success', "{$usuario->name} fue removido(a) del equipo.");
    }

    public function cancelarInvitacion(Obra $obra, Invitacion $invitacion): RedirectResponse
    {
        $this->authorize('update', $obra);

        abort_unless($invitacion->obra_id === $obra->id, 404);

        $invitacion->update(['cancelada_at' => now()]);

        return back()->with('success', 'Invitación cancelada.');
    }

    public function reenviarInvitacion(Obra $obra, Invitacion $invitacion): RedirectResponse
    {
        $this->authorize('update', $obra);

        abort_unless($invitacion->obra_id === $obra->id, 404);

        // Renueva el token y la expiración.
        $invitacion->update([
            'token' => Invitacion::generarToken(),
            'expira_at' => now()->addDays(7),
            'cancelada_at' => null,
        ]);

        Mail::to($invitacion->email)->send(new InvitacionObra($invitacion));

        return back()->with('success', 'Invitación reenviada.');
    }
}
