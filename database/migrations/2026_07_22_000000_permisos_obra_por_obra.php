<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite overrides de la matriz de permisos por obra.
     *
     * - obra_id NULL  => fila de la matriz por defecto (global).
     * - obra_id = X   => matriz personalizada de la obra X (reemplaza por
     *   completo a la global para esa obra).
     */
    public function up(): void
    {
        Schema::table('permisos_obra', function (Blueprint $table) {
            $table->dropUnique(['rol_obra', 'permiso']);
            $table->foreignId('obra_id')
                ->nullable()
                ->after('id')
                ->constrained('obras')
                ->cascadeOnDelete();
            $table->unique(['obra_id', 'rol_obra', 'permiso']);
        });
    }

    public function down(): void
    {
        Schema::table('permisos_obra', function (Blueprint $table) {
            $table->dropUnique(['obra_id', 'rol_obra', 'permiso']);
            $table->dropConstrainedForeignId('obra_id');
            $table->unique(['rol_obra', 'permiso']);
        });
    }
};
