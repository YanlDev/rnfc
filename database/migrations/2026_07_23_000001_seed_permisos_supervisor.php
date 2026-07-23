<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permisos por defecto (matriz global) del nuevo rol de obra Supervisor:
     * lectura de documentos, cuaderno y calendario, y escritura en el
     * cuaderno (sus asientos de supervisión). Sin caja: es exclusiva del
     * Administrador de obra.
     *
     * @var array<int, string>
     */
    private array $permisos = [
        'documento.ver',
        'cuaderno.ver',
        'cuaderno.escribir',
        'calendario.ver',
    ];

    public function up(): void
    {
        foreach ($this->permisos as $permiso) {
            DB::table('permisos_obra')->updateOrInsert(
                ['obra_id' => null, 'rol_obra' => 'supervisor', 'permiso' => $permiso],
                [],
            );
        }

        Cache::forget('permisos_obra_map');
    }

    public function down(): void
    {
        DB::table('permisos_obra')
            ->whereNull('obra_id')
            ->where('rol_obra', 'supervisor')
            ->whereIn('permiso', $this->permisos)
            ->delete();

        Cache::forget('permisos_obra_map');
    }
};
