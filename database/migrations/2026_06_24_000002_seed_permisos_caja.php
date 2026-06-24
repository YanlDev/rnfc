<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Concede los permisos de caja chica al Administrador de obra por defecto.
     * El resto de roles quedan sin acceso; el Admin de plataforma puede
     * ajustarlo después desde la matriz de permisos.
     */
    private array $permisos = ['caja.ver', 'caja.registrar', 'caja.gestionar'];

    public function up(): void
    {
        foreach ($this->permisos as $permiso) {
            DB::table('permisos_obra')->updateOrInsert(
                ['rol_obra' => 'administrador', 'permiso' => $permiso],
                [],
            );
        }

        Cache::forget('permisos_obra_map');
    }

    public function down(): void
    {
        DB::table('permisos_obra')
            ->where('rol_obra', 'administrador')
            ->whereIn('permiso', $this->permisos)
            ->delete();

        Cache::forget('permisos_obra_map');
    }
};
