<?php

namespace App\Enums;

/**
 * Rol de plataforma (Spatie). Sólo hay dos:
 *  - Admin: súper-usuario, puede todo y configura la matriz de permisos.
 *  - Usuario: usuario normal; su poder depende del rol que tenga DENTRO
 *    de cada obra (ver App\Enums\RolObra y App\Support\PermisosObra).
 */
enum RolGlobal: string
{
    case Admin = 'admin';
    case Usuario = 'usuario';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Usuario => 'Usuario',
        };
    }

    /**
     * Roles con poder total de plataforma (crear/eliminar obras,
     * certificados, ver todas las obras, gestionar usuarios y permisos).
     *
     * @return array<int, string>
     */
    public static function rolesAdministrativos(): array
    {
        return [self::Admin->value];
    }

    /**
     * Roles con visión global de todas las obras (sin pasar por el pivot).
     *
     * @return array<int, string>
     */
    public static function rolesVisionGlobal(): array
    {
        return self::rolesAdministrativos();
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
