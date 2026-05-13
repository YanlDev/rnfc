<?php

namespace App\Notifications;

use App\Models\EventoCalendario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventoProximoAVencer extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly EventoCalendario $evento,
        public readonly int $diasRestantes,
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
        $obra = $this->evento->obra;
        $url = route('obras.calendario.index', $obra->id);
        $cuandoTexto = $this->diasRestantes === 0
            ? 'vence HOY'
            : "vence en {$this->diasRestantes} día(s)";

        return (new MailMessage)
            ->subject("[{$obra->codigo}] {$this->evento->tipo->label()} {$cuandoTexto}: {$this->evento->titulo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("El evento **{$this->evento->titulo}** ({$this->evento->tipo->label()}) {$cuandoTexto}.")
            ->line("Obra: **{$obra->nombre}**")
            ->line("Fecha: {$this->evento->fecha_inicio->format('d/m/Y')}")
            ->action('Ver calendario', $url);
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $cuandoTexto = $this->diasRestantes === 0
            ? 'vence hoy'
            : "vence en {$this->diasRestantes} día(s)";

        return [
            'tipo' => 'evento_por_vencer',
            'titulo' => "{$this->evento->tipo->label()}: {$this->evento->titulo}",
            'mensaje' => "{$cuandoTexto} · {$this->evento->fecha_inicio->format('d/m/Y')}",
            'obra_codigo' => $this->evento->obra?->codigo,
            'obra_nombre' => $this->evento->obra?->nombre,
            'url' => route('obras.calendario.index', $this->evento->obra_id),
            'icono' => 'CalendarClock',
            'color' => $this->diasRestantes === 0 ? '#c1272d' : '#ffd21c',
        ];
    }
}
