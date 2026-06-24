<?php

namespace App\Enums;

/**
 * Categoría de un gasto (egreso) de caja chica. Para ingresos no aplica.
 */
enum CategoriaCaja: string
{
    case Viaticos = 'viaticos';
    case Combustible = 'combustible';
    case Materiales = 'materiales';
    case Alimentacion = 'alimentacion';
    case Transporte = 'transporte';
    case Papeleria = 'papeleria';
    case ManoObra = 'mano_obra';
    case Otros = 'otros';

    public function label(): string
    {
        return match ($this) {
            self::Viaticos => 'Viáticos',
            self::Combustible => 'Combustible',
            self::Materiales => 'Materiales',
            self::Alimentacion => 'Alimentación',
            self::Transporte => 'Transporte',
            self::Papeleria => 'Papelería',
            self::ManoObra => 'Mano de obra',
            self::Otros => 'Otros',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function opciones(): array
    {
        return array_map(fn (self $c) => ['value' => $c->value, 'label' => $c->label()], self::cases());
    }
}
