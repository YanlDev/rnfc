<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->nullable()->constrained('obras')->cascadeOnDelete();
            $table->string('email');
            $table->string('rol_obra', 30)->nullable();
            $table->string('rol_global', 30)->nullable();
            $table->string('token', 64)->unique();
            $table->foreignId('invitado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expira_at');
            $table->timestamp('aceptada_at')->nullable();
            $table->timestamp('cancelada_at')->nullable();
            $table->timestamps();

            $table->index(['obra_id', 'email']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitaciones');
    }
};
