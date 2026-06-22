<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Roles globales viejos → nuevos. gerente_general tenía poder total → admin. */
    private array $mapa = [
        'gerente_general' => 'admin',
        'residente' => 'usuario',
        'ingeniero' => 'usuario',
        'invitado' => 'usuario',
    ];

    public function up(): void
    {
        $guard = 'web';

        // Asegura que existan los roles nuevos.
        foreach (['admin', 'usuario'] as $nombre) {
            DB::table('roles')->updateOrInsert(
                ['name' => $nombre, 'guard_name' => $guard],
                ['name' => $nombre, 'guard_name' => $guard],
            );
        }

        foreach ($this->mapa as $viejo => $nuevo) {
            $viejoId = DB::table('roles')->where('name', $viejo)->where('guard_name', $guard)->value('id');

            if (! $viejoId) {
                continue;
            }

            $nuevoId = DB::table('roles')->where('name', $nuevo)->where('guard_name', $guard)->value('id');

            // Reasigna usuarios al rol nuevo (sin duplicar) y elimina el viejo.
            $asignaciones = DB::table('model_has_roles')->where('role_id', $viejoId)->get();

            foreach ($asignaciones as $a) {
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $nuevoId,
                        'model_type' => $a->model_type,
                        'model_id' => $a->model_id,
                    ],
                    [],
                );
            }

            DB::table('model_has_roles')->where('role_id', $viejoId)->delete();
            DB::table('roles')->where('id', $viejoId)->delete();
        }

        // Invitaciones globales pendientes con rol viejo → mapeado.
        foreach ($this->mapa as $viejo => $nuevo) {
            DB::table('invitaciones')->where('rol_global', $viejo)->update(['rol_global' => $nuevo]);
        }
    }

    public function down(): void
    {
        // No reversible (consolidación).
    }
};
