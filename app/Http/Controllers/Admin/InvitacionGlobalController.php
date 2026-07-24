<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolGlobal;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvitarGlobalRequest;
use App\Mail\InvitacionGlobal;
use App\Models\Invitacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

/**
 * Las rutas de este controlador viven bajo el grupo middleware('admin')
 * (ver routes/web.php): solo roles globales administrativos llegan aquí.
 * `store` además lo revalida vía InvitarGlobalRequest::authorize().
 */
class InvitacionGlobalController extends Controller
{
    /**
     * Envía una invitación global (rol de plataforma) a un correo que no tiene cuenta.
     */
    public function store(InvitarGlobalRequest $request): RedirectResponse
    {
        $email = strtolower($request->validated('email'));
        $rolGlobal = RolGlobal::from($request->validated('rol_global'));
        // El permiso de crear obras sólo tiene sentido para el rol Usuario.
        $puedeCrearObras = $rolGlobal === RolGlobal::Usuario
            && $request->boolean('puede_crear_obras');

        $yaInvitado = Invitacion::where('email', $email)
            ->whereNotNull('rol_global')
            ->whereNull('aceptada_at')
            ->whereNull('cancelada_at')
            ->where('expira_at', '>', now())
            ->exists();

        if ($yaInvitado) {
            return back()->withErrors([
                'email' => 'Ya hay una invitación global pendiente para este correo.',
            ]);
        }

        $token = Invitacion::generarToken();

        $invitacion = Invitacion::create([
            'email' => $email,
            'rol_global' => $rolGlobal->value,
            'puede_crear_obras' => $puedeCrearObras,
            'token' => Invitacion::hashToken($token),
            'invitado_por' => $request->user()?->id,
            'expira_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new InvitacionGlobal($invitacion, $token));

        return back()->with(
            'success',
            "Invitación global enviada a {$email} como {$rolGlobal->label()}. El enlace expira en 7 días.",
        );
    }

    /**
     * Cancela una invitación global pendiente.
     */
    public function cancelar(Invitacion $invitacion): RedirectResponse
    {
        abort_unless($invitacion->esGlobal(), 404);

        if ($invitacion->aceptada_at !== null) {
            return back()->withErrors([
                'invitacion' => 'Esta invitación ya fue aceptada; no se puede cancelar.',
            ]);
        }

        $invitacion->update(['cancelada_at' => now()]);

        return back()->with('success', 'Invitación global cancelada.');
    }

    /**
     * Reenvía una invitación global pendiente, renovando token y expiración.
     * Una invitación aceptada o cancelada no se reenvía: para volver a
     * invitar tras una cancelación se crea una invitación nueva.
     */
    public function reenviar(Invitacion $invitacion): RedirectResponse
    {
        abort_unless($invitacion->esGlobal(), 404);

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

        Mail::to($invitacion->email)->send(new InvitacionGlobal($invitacion, $token));

        return back()->with('success', 'Invitación global reenviada.');
    }
}
