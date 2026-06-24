<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();

            $table->string('tipo', 10);                 // ingreso | egreso
            $table->string('categoria', 30)->nullable(); // sólo egresos
            $table->decimal('monto', 12, 2);             // siempre positivo
            $table->string('descripcion');
            $table->date('fecha');

            // Comprobante (boleta/factura) opcional, en el disco de documentos.
            $table->string('comprobante_path', 1000)->nullable();
            $table->string('comprobante_nombre_original')->nullable();
            $table->string('comprobante_mime', 120)->nullable();
            $table->unsignedBigInteger('comprobante_tamano')->nullable();

            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['obra_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
    }
};
