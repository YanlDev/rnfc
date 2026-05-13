<?php

namespace App\Enums;

/**
 * Tipos de evento del calendario de una obra. Cada tipo trae su propio
 * color de marca para distinguirlo en el grid mensual.
 */
enum TipoEvento: string
{
    case Hito = 'hito';
    case Vencimiento = 'vencimiento';
    case Reunion = 'reunion';
    case Inspeccion = 'inspeccion';
    case Entrega = 'entrega';
    case Paralizacion = 'paralizacion';
    case Reinicio = 'reinicio';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Hito => 'Hito',
            self::Vencimiento => 'Vencimiento',
            self::Reunion => 'Reunión',
            self::Inspeccion => 'Inspección',
            self::Entrega => 'Entrega',
            self::Paralizacion => 'Paralización',
            self::Reinicio => 'Reinicio',
            self::Otro => 'Otro',
        };
    }

    /**
     * Color hex usado en badges y bullets del calendario.
     */
    public function color(): string
    {
        return match ($this) {
            self::Hito => '#145694',           // azul oscuro de marca
            self::Vencimiento => '#c1272d',     // rojo (urgencia)
            self::Reunion => '#2850da',         // azul claro
            self::Inspeccion => '#9ed146',      // verde claro
            self::Entrega => '#5da235',         // verde
            self::Paralizacion => '#ffd21c',    // amarillo
            self::Reinicio => '#1aa39c',        // teal
            self::Otro => '#5d6166',            // gris
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
