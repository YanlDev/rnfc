<?php

namespace App\Enums;

enum EstadoObra: string
{
    case Planificacion = 'planificacion';
    case EnEjecucion = 'en_ejecucion';
    case Paralizada = 'paralizada';
    case Finalizada = 'finalizada';
    case Archivada = 'archivada';

    public function label(): string
    {
        return match ($this) {
            self::Planificacion => 'Planificación',
            self::EnEjecucion => 'En ejecución',
            self::Paralizada => 'Paralizada',
            self::Finalizada => 'Finalizada',
            self::Archivada => 'Archivada',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $e) => $e->value, self::cases());
    }
}
