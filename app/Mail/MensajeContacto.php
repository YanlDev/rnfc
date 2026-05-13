<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MensajeContacto extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $nombre,
        public readonly string $correo,
        public readonly ?string $telefono,
        public readonly string $asunto,
        public readonly string $mensaje,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Web RNFC] {$this->asunto}",
            replyTo: [$this->correo],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.mensaje-contacto',
            with: [
                'nombre' => $this->nombre,
                'correo' => $this->correo,
                'telefono' => $this->telefono,
                'asunto' => $this->asunto,
                'mensaje' => $this->mensaje,
            ],
        );
    }
}
