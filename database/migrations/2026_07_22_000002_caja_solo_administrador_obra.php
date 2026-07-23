<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La caja chica es exclusiva del rol de obra Administrador: limpiamos
     * cualquier concesión previa (global o por obra) a otros roles.
     */
    public function up(): void
    {
        DB::table('permisos_obra')
            ->where('permiso', 'like', 'caja.%')
            ->where('rol_obra', '!=', 'administrador')
            ->delete();

        Cache::forget('permisos_obra_map');
    }

    public function down(): void
    {
        // Nada que restaurar: no sabemos qué filas existían antes.
    }
};
