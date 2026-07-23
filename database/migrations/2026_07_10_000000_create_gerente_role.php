<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Introduce el rol global "gerente" (visor global). Separa la visión global
 * del poder administrativo: hasta ahora sólo existía "admin" para ambos.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => 'gerente', 'guard_name' => 'web'],
            ['name' => 'gerente', 'guard_name' => 'web'],
        );
    }

    public function down(): void
    {
        $id = DB::table('roles')->where('name', 'gerente')->where('guard_name', 'web')->value('id');

        if ($id === null) {
            return;
        }

        // Los gerentes vuelven a "usuario" (rol base) antes de eliminar el rol.
        $usuarioId = DB::table('roles')->where('name', 'usuario')->where('guard_name', 'web')->value('id');

        if ($usuarioId !== null) {
            foreach (DB::table('model_has_roles')->where('role_id', $id)->get() as $a) {
                DB::table('model_has_roles')->updateOrInsert([
                    'role_id' => $usuarioId,
                    'model_type' => $a->model_type,
                    'model_id' => $a->model_id,
                ], []);
            }
        }

        DB::table('model_has_roles')->where('role_id', $id)->delete();
        DB::table('roles')->where('id', $id)->delete();
    }
};
