<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pivot obra_user: rol_obra "supervisor_obra" → "administrador_obra"
        DB::table('obra_user')
            ->where('rol_obra', 'supervisor_obra')
            ->update(['rol_obra' => 'administrador_obra']);

        // 2. Rol global "supervisor" se reasigna a "residente"
        //    (modelo simplificado: solo admin/gerente general tienen acceso global)
        $supervisorRolId = DB::table('roles')->where('name', 'supervisor')->value('id');
        $residenteRolId = DB::table('roles')->where('name', 'residente')->value('id');

        if ($supervisorRolId) {
            if ($residenteRolId) {
                DB::table('model_has_roles')
                    ->where('role_id', $supervisorRolId)
                    ->update(['role_id' => $residenteRolId]);
            } else {
                DB::table('model_has_roles')->where('role_id', $supervisorRolId)->delete();
            }
            DB::table('roles')->where('id', $supervisorRolId)->delete();
        }
    }

    public function down(): void
    {
        // Revertir pivot: administrador_obra → supervisor_obra
        DB::table('obra_user')
            ->where('rol_obra', 'administrador_obra')
            ->update(['rol_obra' => 'supervisor_obra']);

        // El rol global supervisor se recrea automáticamente vía RolesSeeder si se rebuilds
    }
};
