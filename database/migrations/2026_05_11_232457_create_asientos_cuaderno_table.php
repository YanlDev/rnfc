<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asientos_cuaderno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            // Cuaderno al que pertenece (supervisor/residente) — numeración independiente por tipo.
            $table->string('tipo_autor', 20);
            $table->unsignedInteger('numero');
            $table->date('fecha');
            $table->text('contenido');

            // Adjunto opcional (escaneo del asiento físico u otro respaldo).
            $table->string('archivo_path', 1000)->nullable();
            $table->string('archivo_nombre_original')->nullable();
            $table->string('archivo_mime', 120)->nullable();
            $table->unsignedBigInteger('archivo_tamano')->nullable();

            $table->foreignId('autor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // §4.5: trazabilidad legal del cuaderno

            // Numeración única por obra + tipo (§4.6 del plan).
            $table->unique(['obra_id', 'tipo_autor', 'numero']);
            $table->index(['obra_id', 'tipo_autor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos_cuaderno');
    }
};
