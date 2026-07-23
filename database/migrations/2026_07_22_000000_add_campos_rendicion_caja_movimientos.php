<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            // Rendición al estilo de obra: cada gasto es factura/boleta/recibo.
            $table->string('tipo_comprobante', 10)->nullable()->after('categoria');
            $table->string('numero_comprobante', 50)->nullable()->after('tipo_comprobante');
            $table->string('proveedor')->nullable()->after('numero_comprobante');
            // Vía del depósito (sólo ingresos): yape, transferencia, efectivo…
            $table->string('metodo', 20)->nullable()->after('proveedor');
        });
    }

    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropColumn(['tipo_comprobante', 'numero_comprobante', 'proveedor', 'metodo']);
        });
    }
};
