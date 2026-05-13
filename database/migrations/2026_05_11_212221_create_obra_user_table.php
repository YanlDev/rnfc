<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obra_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('rol_obra', 30);
            $table->timestamp('asignado_at')->useCurrent();
            $table->timestamps();

            $table->unique(['obra_id', 'user_id']);
            $table->index('rol_obra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_user');
    }
};
