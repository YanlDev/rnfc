<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mapea los valores antiguos del enum RolObra a los nuevos valores
     * más específicos (supervisor → supervisor_obra, etc.). Aplica tanto
     * al pivot obra_user como a invitaciones pendientes.
     */
    public function up(): void
    {
        $mapeo = [
            'supervisor' => 'supervisor_obra',
            'residente' => 'residente_obra',
            'ingeniero' => 'asistente',
            // 'invitado' se mantiene igual.
        ];

        foreach ($mapeo as $antiguo => $nuevo) {
            DB::table('obra_user')
                ->where('rol_obra', $antiguo)
                ->update(['rol_obra' => $nuevo]);
            DB::table('invitaciones')
                ->where('rol_obra', $antiguo)
                ->update(['rol_obra' => $nuevo]);
        }
    }

    public function down(): void
    {
        $mapeoInverso = [
            'supervisor_obra' => 'supervisor',
            'residente_obra' => 'residente',
            'asistente' => 'ingeniero',
        ];

        foreach ($mapeoInverso as $nuevo => $antiguo) {
            DB::table('obra_user')
                ->where('rol_obra', $nuevo)
                ->update(['rol_obra' => $antiguo]);
            DB::table('invitaciones')
                ->where('rol_obra', $nuevo)
                ->update(['rol_obra' => $antiguo]);
        }
    }
};
