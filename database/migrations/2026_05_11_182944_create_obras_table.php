<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('ubicacion')->nullable();
            $table->string('entidad_contratante')->nullable();
            $table->decimal('monto_contractual', 14, 2)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin_prevista')->nullable();
            $table->date('fecha_fin_real')->nullable();
            $table->string('estado', 30)->default('planificacion')->index();
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};
