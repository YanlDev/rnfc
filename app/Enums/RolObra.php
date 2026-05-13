<?php

namespace App\Enums;

/**
 * Rol específico que un usuario desempeña dentro de una obra.
 * Es distinto del rol global (Spatie). Una persona puede ser
 * "Supervisor" globalmente y "Especialista QA/QC" en una obra ajena.
 *
 * Se usa como columna `rol_obra` en el pivot `obra_user`.
 */
enum RolObra: string
{
    // Dirección y línea principal
    case AdministradorObra = 'administrador_obra';
    case ResidenteObra = 'residente_obra';
    case JefeOficinaTecnica = 'jefe_oficina_tecnica';

    // Especialistas técnicos
    case EspecialistaCalidad = 'especialista_calidad';
    case EspecialistaSsoma = 'especialista_ssoma';
    case EspecialistaSeguridad = 'especialista_seguridad';
    case EspecialistaAmbiental = 'especialista_ambiental';
    case EspecialistaRiesgos = 'especialista_riesgos';
    case EspecialistaBim = 'especialista_bim';
    case EspecialistaCompatibilizacion = 'especialista_compatibilizacion';
    case EspecialistaMetradosCostos = 'especialista_metrados_costos';
    case EspecialistaValorizaciones = 'especialista_valorizaciones';
    case EspecialistaLiquidaciones = 'especialista_liquidaciones';

    // Apoyo
    case Asistente = 'asistente';
    case Practicante = 'practicante';

    // Lectura
    case Invitado = 'invitado';

    public function label(): string
    {
        return match ($this) {
            self::AdministradorObra => 'Administrador de obra',
            self::ResidenteObra => 'Residente de obra',
            self::JefeOficinaTecnica => 'Jefe de oficina técnica',
            self::EspecialistaCalidad => 'Especialista en Calidad (QA/QC)',
            self::EspecialistaSsoma => 'Especialista SSOMA',
            self::EspecialistaSeguridad => 'Especialista en Seguridad y Salud Ocupacional',
            self::EspecialistaAmbiental => 'Especialista Ambiental',
            self::EspecialistaRiesgos => 'Especialista en Gestión de Riesgos',
            self::EspecialistaBim => 'Especialista BIM',
            self::EspecialistaCompatibilizacion => 'Especialista en Compatibilización',
            self::EspecialistaMetradosCostos => 'Especialista en Metrados y Costos',
            self::EspecialistaValorizaciones => 'Especialista en Valorizaciones',
            self::EspecialistaLiquidaciones => 'Especialista en Liquidaciones',
            self::Asistente => 'Asistente',
            self::Practicante => 'Practicante',
            self::Invitado => 'Invitado',
        };
    }

    /**
     * Agrupación para mostrar en menús/listas.
     */
    public function categoria(): string
    {
        return match ($this) {
            self::AdministradorObra,
            self::ResidenteObra,
            self::JefeOficinaTecnica => 'Dirección',

            self::EspecialistaCalidad,
            self::EspecialistaSsoma,
            self::EspecialistaSeguridad,
            self::EspecialistaAmbiental,
            self::EspecialistaRiesgos,
            self::EspecialistaBim,
            self::EspecialistaCompatibilizacion,
            self::EspecialistaMetradosCostos,
            self::EspecialistaValorizaciones,
            self::EspecialistaLiquidaciones => 'Especialistas',

            self::Asistente,
            self::Practicante => 'Apoyo',

            self::Invitado => 'Lectura',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $r) => $r->value, self::cases());
    }

    /**
     * El Administrador de obra es el único rol por obra con poder de gestión total
     * (editar la obra, crear asientos, eliminar documentos).
     */
    public function esAdministrador(): bool
    {
        return $this === self::AdministradorObra;
    }

    /**
     * El Invitado en obra es de solo lectura, sin uploads ni cuaderno.
     */
    public function esInvitado(): bool
    {
        return $this === self::Invitado;
    }
}
