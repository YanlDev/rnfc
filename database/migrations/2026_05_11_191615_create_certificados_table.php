<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('tipo', 40)->index();

            // Beneficiario (persona a quien se emite)
            $table->string('beneficiario_nombre');
            $table->string('beneficiario_documento', 30)->nullable();
            $table->string('beneficiario_profesion')->nullable();

            // Contexto de la obra/servicio (cuando aplica)
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->string('cargo')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Texto libre del cuerpo del certificado
            $table->text('descripcion')->nullable();
            $table->string('lugar_emision')->default('Puno, Perú');

            // Datos del emisor (firmante)
            $table->string('emisor_nombre')->default('Ing. Roger Neptali Flores Coaquira');
            $table->string('emisor_cargo')->default('Consultor de Obras');
            $table->string('emisor_cip')->nullable();

            // Verificación
            $table->date('fecha_emision');
            $table->string('hash_verificacion', 64)->unique();
            $table->timestamp('revocado_at')->nullable();
            $table->text('motivo_revocacion')->nullable();

            // Auditoría
            $table->foreignId('emitido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados');
    }
};
