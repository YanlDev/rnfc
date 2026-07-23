<?php

namespace App\Notifications;

use App\Models\Certificado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CertificadoEliminado extends Notification implements ShouldQueue
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
            'tipo' => 'certificado_eliminado',
            'titulo' => 'Certificado eliminado',
            'mensaje' => "{$this->certificado->codigo} · {$this->certificado->beneficiario_nombre}",
            'obra_codigo' => null,
            'obra_nombre' => null,
            // El certificado ya no es accesible en show (soft delete): se
            // enlaza al listado.
            'url' => route('certificados.index'),
            'icono' => 'Trash2',
            'color' => '#c1272d',
        ];
    }
}
