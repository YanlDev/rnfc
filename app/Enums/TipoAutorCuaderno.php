<?php

namespace App\Enums;

/**
 * Distingue los dos cuadernos paralelos de una obra. La numeración
 * es independiente por tipo (cuaderno del supervisor y cuaderno del
 * residente llevan secuencias separadas dentro de cada obra).
 */
enum TipoAutorCuaderno: string
{
    case Supervisor = 'supervisor';
    case Residente = 'residente';

    public function label(): string
    {
        return match ($this) {
            self::Supervisor => 'Cuaderno del Supervisor',
            self::Residente => 'Cuaderno del Residente',
        };
    }

    public function labelCorto(): string
    {
        return match ($this) {
            self::Supervisor => 'Supervisión',
            self::Residente => 'Residencia',
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
