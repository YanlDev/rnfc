<?php

namespace App\Notifications;

use App\Models\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentoSubido extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Documento $documento,
        public readonly ?string $autorNombre = null,
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
        $obra = $this->documento->obra;
        $url = route('obras.documentos.index', $obra->id);
        $accion = $this->documento->version > 1 ? "nueva versión (v{$this->documento->version})" : 'documento';

        return (new MailMessage)
            ->subject("[{$obra->codigo}] Nuevo {$accion}: {$this->documento->nombre_original}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Se subió un {$accion} a la obra **{$obra->nombre}**:")
            ->line("**{$this->documento->nombre_original}**")
            ->when($this->autorNombre, fn ($m) => $m->line("Subido por {$this->autorNombre}."))
            ->action('Ver documentos', $url)
            ->line('Este es un mensaje autom&aacute;tico de RNFC.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'documento_subido',
            'titulo' => 'Nuevo documento subido',
            'mensaje' => $this->documento->nombre_original.($this->documento->version > 1 ? " (v{$this->documento->version})" : ''),
            'obra_codigo' => $this->documento->obra?->codigo,
            'obra_nombre' => $this->documento->obra?->nombre,
            'url' => route('obras.documentos.index', $this->documento->obra_id),
            'icono' => 'FolderTree',
            'color' => '#2850da',
        ];
    }
}
