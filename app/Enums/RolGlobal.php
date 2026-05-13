<?php

namespace App\Enums;

enum RolGlobal: string
{
    case Admin = 'admin';
    case GerenteGeneral = 'gerente_general';
    case Residente = 'residente';
    case Ingeniero = 'ingeniero';
    case Invitado = 'invitado';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::GerenteGeneral => 'Gerente General',
            self::Residente => 'Residente',
            self::Ingeniero => 'Ingeniero',
            self::Invitado => 'Invitado',
        };
    }

    /**
     * Roles con acceso administrativo total a la plataforma:
     * pueden crear/editar/eliminar obras y emitir/revocar certificados.
     *
     * @return array<int, string>
     */
    public static function rolesAdministrativos(): array
    {
        return [self::Admin->value, self::GerenteGeneral->value];
    }

    /**
     * Roles con visión global de todas las obras (sin pasar por el pivot).
     * Idéntico a rolesAdministrativos: simplificación del modelo.
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
