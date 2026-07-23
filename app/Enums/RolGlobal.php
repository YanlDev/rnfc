<?php

namespace App\Enums;

/**
 * Rol de plataforma (Spatie). Hay tres:
 *  - Admin: súper-usuario, puede todo y configura la matriz de permisos.
 *  - Gerente: visor global; ve TODAS las obras (cuaderno, calendario, equipo,
 *    caja) y emite certificados, pero no modifica contenido ni administra la
 *    plataforma. Es lectura global + emisión de certificados.
 *  - Usuario: usuario normal; su poder depende del rol que tenga DENTRO
 *    de cada obra (ver App\Enums\RolObra y App\Support\PermisosObra).
 */
enum RolGlobal: string
{
    case Admin = 'admin';
    case Gerente = 'gerente';
    case Usuario = 'usuario';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Gerente => 'Gerente General',
            self::Usuario => 'Usuario',
        };
    }

    /**
     * Roles con poder total de plataforma (crear/eliminar obras, revocar
     * certificados, gestionar usuarios y permisos). Sólo el Admin.
     *
     * @return array<int, string>
     */
    public static function rolesAdministrativos(): array
    {
        return [self::Admin->value];
    }

    /**
     * Roles con visión global de todas las obras (sin pasar por el pivot):
     * Admin y Gerente. Determina quién ve todo y quién puede emitir
     * certificados; el poder de escritura sigue separado (ver rolesAdministrativos).
     *
     * @return array<int, string>
     */
    public static function rolesVisionGlobal(): array
    {
        return [self::Admin->value, self::Gerente->value];
    }

    /**
     * Roles globales que un administrador puede otorgar por invitación
     * (el rol "usuario" no se invita: es el rol base que depende de la obra).
     *
     * @return array<int, string>
     */
    public static function rolesGlobalesAsignables(): array
    {
        return [self::Admin->value, self::Gerente->value];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }
}
