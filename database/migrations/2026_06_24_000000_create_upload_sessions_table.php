<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_sessions', function (Blueprint $table) {
            $table->id();
            $table->ulid('token')->unique(); // identificador en URL y carpeta temporal

            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();

            // Destino: subida nueva a una carpeta, o nueva versión de un documento.
            $table->foreignId('carpeta_id')->nullable()->constrained('carpetas')->cascadeOnDelete();
            $table->foreignId('documento_id')->nullable()->constrained('documentos')->cascadeOnDelete();

            $table->string('nombre_original');
            $table->unsignedBigInteger('tamano_total'); // bytes del archivo completo
            $table->unsignedInteger('total_chunks');
            $table->unsignedInteger('chunks_recibidos')->default(0);

            // pendiente → subiendo → procesando → completado | error
            $table->string('estado', 20)->default('pendiente');
            $table->text('error')->nullable();

            $table->foreignId('documento_resultante_id')->nullable()
                ->constrained('documentos')->nullOnDelete();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_sessions');
    }
};
