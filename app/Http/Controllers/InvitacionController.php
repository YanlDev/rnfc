<?php

namespace App\Http\Controllers;

use App\Models\Invitacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvitacionController extends Controller
{
    /**
     * Pantalla pública que se muestra al hacer clic en el link del correo.
     * Si hay sesión iniciada con el mismo correo, ofrece aceptar directo.
     * Si no, deriva al login/registro guardando el token en sesión.
     */
    public function mostrar(string $token): Response|RedirectResponse
    {
        $invitacion = Invitacion::with('obra:id,codigo,nombre,entidad_contratante', 'invitador:id,name')
            ->where('token', $token)
            ->first();

        if (! $invitacion || ! $invitacion->estaActiva()) {
            return Inertia::render('invitaciones/invalida', [
                'estado' => $invitacion?->estado() ?? 'inexistente',
            ]);
        }

        $user = request()->user();

        // Mismo correo y sesión activa → aceptar de inmediato.
        if ($user && strcasecmp($user->email, $invitacion->email) === 0) {
            $this->aceptar($invitacion);

            if ($invitacion->esGlobal()) {
                return redirect()
                    ->route('dashboard')
                    ->with('success', 'Te uniste a la plataforma como '.$invitacion->rol_global->label().'.');
            }

            return redirect()
                ->route('obras.show', $invitacion->obra_id)
                ->with('success', "Te uniste a la obra {$invitacion->obra->nombre}.");
        }

        // Si está autenticado con otro correo, le pedimos cerrar sesión.
        if ($user) {
            return Inertia::render('invitaciones/conflicto', [
                'invitacion' => [
                    'email' => $invitacion->email,
                    'obra' => $invitacion->esGlobal()
                        ? null
                        : $invitacion->obra->nombre,
                ],
                'usuarioActual' => $user->email,
            ]);
        }

        // No autenticado: guardamos el token en sesión y mostramos la pantalla pública.
        session(['invitacion_token' => $invitacion->token]);

        if ($invitacion->esGlobal()) {
            return Inertia::render('invitaciones/aceptar-global', [
                'invitacion' => [
                    'email' => $invitacion->email,
                    'rol' => $invitacion->rol_global->label(),
                    'invitador' => $invitacion->invitador?->name,
                    'expira_at' => $invitacion->expira_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return Inertia::render('invitaciones/aceptar', [
            'invitacion' => [
                'email' => $invitacion->email,
                'rol' => $invitacion->rol_obra->label(),
                'obra' => [
                    'codigo' => $invitacion->obra->codigo,
                    'nombre' => $invitacion->obra->nombre,
                    'entidad' => $invitacion->obra->entidad_contratante,
                ],
                'invitador' => $invitacion->invitador?->name,
                'expira_at' => $invitacion->expira_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Aceptación directa cuando ya hay sesión con el mismo correo.
     */
    public function aceptarAuth(Request $request, string $token): RedirectResponse
    {
        $invitacion = Invitacion::where('token', $token)->firstOrFail();

        abort_unless($invitacion->estaActiva(), 410, 'La invitación ya no es válida.');
        abort_unless(strcasecmp($request->user()->email, $invitacion->email) === 0, 403);

        $this->aceptar($invitacion);

        if ($invitacion->esGlobal()) {
            return redirect()
                ->route('dashboard')
                ->with('success', 'Te uniste a la plataforma como '.$invitacion->rol_global->label().'.');
        }

        return redirect()
            ->route('obras.show', $invitacion->obra_id)
            ->with('success', "Te uniste a la obra {$invitacion->obra->nombre}.");
    }

    /**
     * Marca la invitación como aceptada y crea el vínculo.
     */
    private function aceptar(Invitacion $invitacion): void
    {
        $user = request()->user();

        if ($invitacion->esGlobal()) {
            if (! $user->hasRole($invitacion->rol_global->value)) {
                $user->assignRole($invitacion->rol_global->value);
            }
        } else {
            if (! $invitacion->obra->usuarios()->where('users.id', $user->id)->exists()) {
                $invitacion->obra->usuarios()->attach($user->id, [
                    'rol_obra' => $invitacion->rol_obra->value,
                    'asignado_at' => now(),
                ]);
            }
        }

        $invitacion->update(['aceptada_at' => now()]);
        session()->forget('invitacion_token');
    }
}
