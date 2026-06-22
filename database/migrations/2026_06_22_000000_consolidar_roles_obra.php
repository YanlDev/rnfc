<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Mapa de valores antiguos (16) a los nuevos (4). */
    private array $mapa = [
        'administrador_obra' => 'administrador',
        'residente_obra' => 'residente',
        'jefe_oficina_tecnica' => 'residente',
        'especialista_calidad' => 'especialista',
        'especialista_ssoma' => 'especialista',
        'especialista_seguridad' => 'especialista',
        'especialista_ambiental' => 'especialista',
        'especialista_riesgos' => 'especialista',
        'especialista_bim' => 'especialista',
        'especialista_compatibilizacion' => 'especialista',
        'especialista_metrados_costos' => 'especialista',
        'especialista_valorizaciones' => 'especialista',
        'especialista_liquidaciones' => 'especialista',
        'asistente' => 'asistente',
        'practicante' => 'asistente',
        'invitado' => 'asistente',
    ];

    public function up(): void
    {
        foreach ($this->mapa as $viejo => $nuevo) {
            if ($viejo === $nuevo) {
                continue;
            }

            DB::table('obra_user')->where('rol_obra', $viejo)->update(['rol_obra' => $nuevo]);
            DB::table('invitaciones')->where('rol_obra', $viejo)->update(['rol_obra' => $nuevo]);
        }

        // Cualquier valor no contemplado cae a 'asistente' (el rol mínimo).
        $validos = ['administrador', 'residente', 'especialista', 'asistente'];
        DB::table('obra_user')->whereNotIn('rol_obra', $validos)->update(['rol_obra' => 'asistente']);
        DB::table('invitaciones')->whereNotNull('rol_obra')->whereNotIn('rol_obra', $validos)->update(['rol_obra' => 'asistente']);
    }

    public function down(): void
    {
        // Consolidación no reversible (varios valores colapsaron en uno).
    }
};
