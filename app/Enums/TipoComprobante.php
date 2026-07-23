<?php

namespace App\Enums;

/**
 * Tipo de comprobante de un gasto de caja chica. Es la clasificación con la
 * que se rinde en obra (subtotales de facturas, boletas y recibos).
 */
enum TipoComprobante: string
{
    case Factura = 'factura';
    case Boleta = 'boleta';
    case Recibo = 'recibo';

    public function label(): string
    {
        return match ($this) {
            self::Factura => 'Factura',
            self::Boleta => 'Boleta',
            self::Recibo => 'Recibo',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function opciones(): array
    {
        return array_map(fn (self $t) => ['value' => $t->value, 'label' => $t->label()], self::cases());
    }
}
