<?php

namespace App\Enums;

/**
 * Rol que un usuario desempeña DENTRO de una obra (columna `rol_obra`
 * del pivot `obra_user`). Es distinto del rol global de plataforma.
 *
 * Las capacidades de cada rol (quién sube documentos, escribe el cuaderno,
 * etc.) NO viven aquí: se configuran desde la matriz de permisos
 * (tabla `permisos_obra`) que administra el Admin. Ver App\Support\PermisosObra.
 */
enum RolObra: string
{
    case Administrador = 'administrador';
    case Residente = 'residente';
    case Especialista = 'especialista';
    case Asistente = 'asistente';

    public function label(): string
    {
        return match ($this) {
            self::Administrador => 'Administrador de obra',
            self::Residente => 'Residente',
            self::Especialista => 'Especialista',
            self::Asistente => 'Asistente',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
