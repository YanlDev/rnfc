<?php

namespace App\Enums;

/**
 * Forma de pago de un alquiler recurrente.
 */
enum FormaPagoAlquiler: string
{
    case Adelantado = 'adelantado';
    case FinDeMes = 'fin_de_mes';

    public function label(): string
    {
        return match ($this) {
            self::Adelantado => 'Mes adelantado',
            self::FinDeMes => 'Fin de mes',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $f) => $f->value, self::cases());
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function opciones(): array
    {
        return array_map(fn (self $f) => ['value' => $f->value, 'label' => $f->label()], self::cases());
    }
}
