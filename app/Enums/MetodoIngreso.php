<?php

namespace App\Enums;

/**
 * Vía por la que llegó un depósito (ingreso) a la caja chica.
 */
enum MetodoIngreso: string
{
    case Yape = 'yape';
    case Transferencia = 'transferencia';
    case Efectivo = 'efectivo';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Yape => 'Yape',
            self::Transferencia => 'Transferencia',
            self::Efectivo => 'Efectivo',
            self::Otro => 'Otro',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $m) => $m->value, self::cases());
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function opciones(): array
    {
        return array_map(fn (self $m) => ['value' => $m->value, 'label' => $m->label()], self::cases());
    }
}
