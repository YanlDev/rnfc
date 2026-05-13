<?php

namespace App\Notifications;

use App\Models\Certificado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CertificadoRevocado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Certificado $certificado,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Auditoría interna: solo in-app para admin/gerente general.
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'tipo' => 'certificado_revocado',
            'titulo' => 'Certificado revocado',
            'mensaje' => "{$this->certificado->codigo} · {$this->certificado->beneficiario_nombre}",
            'obra_codigo' => null,
            'obra_nombre' => null,
            'url' => route('certificados.show', $this->certificado->id),
            'icono' => 'XCircle',
            'color' => '#c1272d',
        ];
    }
}
