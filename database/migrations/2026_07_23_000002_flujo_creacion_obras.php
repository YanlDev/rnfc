<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nuevo flujo: usuarios habilitados (administradoras de obra) crean sus
     * propias obras y las manejan; el Admin general supervisa todas.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Habilitación individual para crear obras (la otorga el Admin).
            $table->boolean('puede_crear_obras')->default(false)->after('desactivado_at');
        });

        // El rol de obra Administrador gestiona su equipo y edita su obra por
        // defecto (matriz global, ajustable desde Permisos).
        foreach (['equipo.gestionar', 'obra.editar'] as $permiso) {
            DB::table('permisos_obra')->updateOrInsert(
                ['obra_id' => null, 'rol_obra' => 'administrador', 'permiso' => $permiso],
                [],
            );
        }

        Cache::forget('permisos_obra_map');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('puede_crear_obras');
        });
        // Los permisos de la matriz no se revierten: son configuración viva.
    }
};
