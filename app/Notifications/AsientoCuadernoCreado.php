<?php

namespace App\Notifications;

use App\Models\AsientoCuaderno;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AsientoCuadernoCreado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly AsientoCuaderno $asiento,
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
        $obra = $this->asiento->obra;
        $url = route('obras.cuaderno.index', ['obra' => $obra->id, 'tipo' => $this->asiento->tipo_autor->value]);

        return (new MailMessage)
            ->subject("[{$obra->codigo}] Nuevo asiento N° {$this->asiento->numero} · {$this->asiento->tipo_autor->labelCorto()}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se registró el asiento **N° {$this->asiento->numero}** en el cuaderno de {$this->asiento->tipo_autor->labelCorto()} de la obra **{$obra->nombre}**.")
            ->line("Fecha del asiento: {$this->asiento->fecha->format('d/m/Y')}")
            ->action('Ver cuaderno', $url)
            ->line('Este es un mensaje autom&aacute;tico de RNFC.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'asiento_creado',
            'titulo' => "Asiento N° {$this->asiento->numero} · {$this->asiento->tipo_autor->labelCorto()}",
            'mensaje' => mb_strimwidth((string) $this->asiento->contenido, 0, 120, '…'),
            'obra_codigo' => $this->asiento->obra?->codigo,
            'obra_nombre' => $this->asiento->obra?->nombre,
            'url' => route('obras.cuaderno.index', ['obra' => $this->asiento->obra_id, 'tipo' => $this->asiento->tipo_autor->value]),
            'icono' => 'NotebookPen',
            'color' => '#5da235',
        ];
    }
}
