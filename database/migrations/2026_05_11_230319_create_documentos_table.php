<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('carpeta_id')->constrained('carpetas')->cascadeOnDelete();

            // Versionado raíz-como-actual (§4.4 del plan).
            // Si documento_padre_id IS NULL la fila es la VERSIÓN VIGENTE.
            // Si tiene padre, es una versión histórica.
            $table->foreignId('documento_padre_id')->nullable()
                ->constrained('documentos')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);

            $table->string('nombre_original');             // como lo subió el usuario
            $table->string('nombre_archivo');              // UUID + ext (único en storage)
            $table->string('archivo_path', 1000);          // ruta completa en el disco
            $table->string('mime', 120);
            $table->unsignedBigInteger('tamano')->default(0); // bytes

            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['carpeta_id', 'documento_padre_id']);
            $table->index('obra_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
