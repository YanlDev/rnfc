<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carpetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('carpetas')->cascadeOnDelete();
            $table->string('nombre');
            // Ruta tipo slug acumulada desde la raíz, usada para el filesystem.
            $table->string('ruta', 1000);
            $table->unsignedInteger('orden')->default(0);
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['obra_id', 'ruta']);
            $table->index(['obra_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carpetas');
    }
};
