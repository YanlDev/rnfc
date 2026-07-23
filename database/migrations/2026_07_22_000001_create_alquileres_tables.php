<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alquileres recurrentes de una obra (oficina, habitaciones de ingenieros…).
        Schema::create('alquileres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();

            $table->string('inquilino');                  // "ING GENARO", "OFICINA"…
            $table->decimal('monto_mensual', 12, 2);
            $table->string('forma_pago', 20);             // adelantado | fin_de_mes
            $table->date('fecha_inicio');
            $table->boolean('activo')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['obra_id', 'activo']);
        });

        // Pago de un mes concreto de un alquiler. Al pagarse genera un egreso
        // (recibo) en caja_movimientos, referenciado aquí.
        Schema::create('alquiler_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->cascadeOnDelete();

            $table->date('periodo');                      // primer día del mes pagado
            $table->date('fecha_pago');
            $table->decimal('monto', 12, 2);
            $table->foreignId('caja_movimiento_id')->nullable()
                ->constrained('caja_movimientos')->nullOnDelete();

            $table->timestamps();

            $table->unique(['alquiler_id', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alquiler_pagos');
        Schema::dropIfExists('alquileres');
    }
};
