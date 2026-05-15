<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RolGlobal;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvitarGlobalRequest;
use App\Mail\InvitacionGlobal;
use App\Models\Invitacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class InvitacionGlobalController extends Controller
{
    /**
     * Envía una invitación global (rol de plataforma) a un correo que no tiene cuenta.
     */
    public function store(InvitarGlobalRequest $request): RedirectResponse
    {
        $email = strtolower($request->validated('email'));
        $rolGlobal = RolGlobal::from($request->validated('rol_global'));

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

        $invitacion = Invitacion::create([
            'email' => $email,
            'rol_global' => $rolGlobal->value,
            'token' => Invitacion::generarToken(),
            'invitado_por' => $request->user()?->id,
            'expira_at' => now()->addDays(7),
        ]);

        Mail::to($email)->send(new InvitacionGlobal($invitacion));

        return back()->with(
            'success',
            "Invitación global enviada a {$email} como {$rolGlobal->label()}. El enlace expira en 7 días.",
        );
    }
}
