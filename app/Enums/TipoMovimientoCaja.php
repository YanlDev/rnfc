<?php

namespace App\Enums;

/**
 * Tipo de movimiento de la caja chica de una obra.
 *  - Ingreso: asignación o reposición de fondos (suma al saldo).
 *  - Egreso: gasto (resta del saldo).
 */
enum TipoMovimientoCaja: string
{
    case Ingreso = 'ingreso';
    case Egreso = 'egreso';

    public function label(): string
    {
        return match ($this) {
            self::Ingreso => 'Ingreso',
            self::Egreso => 'Egreso',
        };
    }

    /**
     * Signo que aplica al saldo (+1 ingreso, -1 egreso).
     */
    public function signo(): int
    {
        return $this === self::Ingreso ? 1 : -1;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
