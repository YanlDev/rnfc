<?php

namespace App\Console\Commands;

use App\Models\EventoCalendario;
use App\Notifications\EventoProximoAVencer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

#[Signature('rnfc:notificar-vencimientos')]
#[Description('Notifica a los miembros de obra los eventos del calendario que vencen hoy o en los próximos 3/7 días.')]
class NotificarEventosVencimiento extends Command
{
    /**
     * Días de antelación para enviar avisos.
     *
     * @var array<int>
     */
    private array $ventanas = [0, 3, 7];

    public function handle(): int
    {
        $hoy = Carbon::today();
        $total = 0;

        foreach ($this->ventanas as $dias) {
            $fechaObjetivo = $hoy->copy()->addDays($dias);

            $eventos = EventoCalendario::query()
                ->whereDate('fecha_inicio', $fechaObjetivo)
                ->where('tipo', 'vencimiento')
                ->with('obra.usuarios')
                ->get();

            foreach ($eventos as $evento) {
                $miembros = $evento->obra->usuarios;
                if ($miembros->isEmpty()) {
                    continue;
                }

                Notification::send($miembros, new EventoProximoAVencer($evento, $dias));
                $total += $miembros->count();
                $this->info("Aviso enviado: «{$evento->titulo}» (obra {$evento->obra->codigo}) → {$miembros->count()} usuario(s) a {$dias} día(s).");
            }
        }

        $this->info("Total de notificaciones encoladas: {$total}");

        return self::SUCCESS;
    }
}
