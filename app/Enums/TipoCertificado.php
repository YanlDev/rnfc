<?php

namespace App\Enums;

enum TipoCertificado: string
{
    case Trabajador = 'trabajador';
    case Especialista = 'especialista';
    case Residente = 'residente';
    case Supervisor = 'supervisor';
    case Capacitacion = 'capacitacion';
    case Participacion = 'participacion';
    case ServiciosProfesionales = 'servicios_profesionales';

    public function label(): string
    {
        return match ($this) {
            self::Trabajador => 'Trabajo',
            self::Especialista => 'Especialista',
            self::Residente => 'Residente de Obra',
            self::Supervisor => 'Supervisor de Obra',
            self::Capacitacion => 'Capacitación',
            self::Participacion => 'Participación',
            self::ServiciosProfesionales => 'Servicios Profesionales',
        };
    }

    /**
     * Cargo/rol a usar en el cuerpo del certificado cuando el campo "cargo"
     * está vacío. Diferente de label(): aquí queremos el sustantivo del rol.
     */
    public function cargoPorDefecto(): string
    {
        return match ($this) {
            self::Trabajador => 'Trabajador',
            self::Especialista => 'Especialista',
            self::Residente => 'Residente de Obra',
            self::Supervisor => 'Supervisor de Obra',
            self::Capacitacion => 'Participante',
            self::Participacion => 'Participante',
            self::ServiciosProfesionales => 'Consultor',
        };
    }

    /** Texto que encabeza el certificado emitido. */
    public function titulo(): string
    {
        return match ($this) {
            self::Trabajador => 'CERTIFICADO DE TRABAJO',
            self::Especialista => 'CERTIFICADO DE ESPECIALISTA',
            self::Residente => 'CERTIFICADO DE RESIDENTE DE OBRA',
            self::Supervisor => 'CERTIFICADO DE SUPERVISOR DE OBRA',
            self::Capacitacion => 'CERTIFICADO DE CAPACITACIÓN',
            self::Participacion => 'CERTIFICADO DE PARTICIPACIÓN',
            self::ServiciosProfesionales => 'CERTIFICADO DE SERVICIOS PROFESIONALES',
        };
    }

    /** ¿Este tipo de certificado requiere obra asociada? */
    public function requiereObra(): bool
    {
        return match ($this) {
            self::Capacitacion => false,
            default => true,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
