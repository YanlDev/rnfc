<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            // Permite emitir certificados con nombre de obra manual (obras
            // anteriores a la plataforma o trabajos no registrados).
            $table->string('obra_nombre_libre')->nullable()->after('obra_id');
            $table->string('obra_entidad_libre')->nullable()->after('obra_nombre_libre');
        });
    }

    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropColumn(['obra_nombre_libre', 'obra_entidad_libre']);
        });
    }
};
