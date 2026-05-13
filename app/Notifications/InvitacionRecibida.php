<?php

namespace App\Notifications;

use App\Models\Invitacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificación cuando una persona ya existente en la plataforma es vinculada
 * a una obra (entra al pivot sin pasar por flujo de invitación por email).
 */
class InvitacionRecibida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invitacion $invitacion,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $obra = $this->invitacion->obra;
        $url = route('obras.show', $obra->id);

        return (new MailMessage)
            ->subject("Te agregaron al equipo de la obra: {$obra->nombre}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Has sido agregado(a) al equipo de la obra **{$obra->nombre}** como **{$this->invitacion->rol_obra->label()}**.")
            ->action('Ver obra', $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'invitacion_recibida',
            'titulo' => 'Te agregaron al equipo de una obra',
            'mensaje' => "Rol: {$this->invitacion->rol_obra->label()}",
            'obra_codigo' => $this->invitacion->obra?->codigo,
            'obra_nombre' => $this->invitacion->obra?->nombre,
            'url' => route('obras.show', $this->invitacion->obra_id),
            'icono' => 'UserPlus',
            'color' => '#1aa39c',
        ];
    }
}
