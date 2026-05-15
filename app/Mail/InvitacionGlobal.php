<?php

namespace App\Mail;

use App\Models\Invitacion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitacionGlobal extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Invitacion $invitacion) {}

    public function envelope(): Envelope
    {
        $rol = $this->invitacion->rol_global?->label() ?? 'colaborador';

        return new Envelope(
            subject: "Has sido invitado a RNFC como {$rol}",
        );
    }

    public function content(): Content
    {
        $this->invitacion->loadMissing('invitador');

        return new Content(
            markdown: 'emails.invitacion-global',
            with: [
                'invitacion' => $this->invitacion,
                'invitador' => $this->invitacion->invitador,
                'urlAceptar' => route('invitaciones.mostrar', $this->invitacion->token),
                'rol' => $this->invitacion->rol_global?->label(),
            ],
        );
    }
}
