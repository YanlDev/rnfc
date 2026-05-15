<?php

namespace App\Notifications;

use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
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
        public readonly Obra $obra,
        public readonly RolObra $rolObra,
        public readonly ?User $invitadoPor = null,
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
        $url = route('obras.show', $this->obra->id);

        $mailMessage = (new MailMessage)
            ->subject("Te agregaron al equipo de la obra: {$this->obra->nombre}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Has sido agregado(a) al equipo de la obra **{$this->obra->nombre}** como **{$this->rolObra->label()}**.");

        if ($this->invitadoPor) {
            $mailMessage->line("Invitado por: {$this->invitadoPor->name}");
        }

        return $mailMessage->action('Ver obra', $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'invitacion_recibida',
            'titulo' => 'Te agregaron al equipo de una obra',
            'mensaje' => "{$this->obra->nombre} — Rol: {$this->rolObra->label()}",
            'obra_codigo' => $this->obra->codigo,
            'obra_nombre' => $this->obra->nombre,
            'url' => route('obras.show', $this->obra->id),
            'icono' => 'UserPlus',
            'color' => '#1aa39c',
        ];
    }
}
