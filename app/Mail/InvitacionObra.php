<?php

namespace App\Mail;

use App\Models\Invitacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitacionObra extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * $tokenPlano viaja solo en el correo; en la BD queda su hash.
     */
    public function __construct(
        public readonly Invitacion $invitacion,
        public readonly string $tokenPlano,
    ) {}

    public function envelope(): Envelope
    {
        $obra = $this->invitacion->obra;

        return new Envelope(
            subject: "Has sido invitado a la obra: {$obra->nombre}",
        );
    }

    public function content(): Content
    {
        $this->invitacion->loadMissing('obra', 'invitador');

        return new Content(
            markdown: 'emails.invitacion-obra',
            with: [
                'invitacion' => $this->invitacion,
                'obra' => $this->invitacion->obra,
                'invitador' => $this->invitacion->invitador,
                'urlAceptar' => route('invitaciones.mostrar', $this->tokenPlano),
                'rol' => $this->invitacion->rol_obra->label(),
            ],
        );
    }
}
