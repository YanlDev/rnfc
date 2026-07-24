<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitaciones', function (Blueprint $table) {
            // Para invitaciones globales de rol Usuario: si se acepta, la cuenta
            // queda habilitada para crear sus propias obras.
            $table->boolean('puede_crear_obras')->default(false)->after('rol_global');
        });
    }

    public function down(): void
    {
        Schema::table('invitaciones', function (Blueprint $table) {
            $table->dropColumn('puede_crear_obras');
        });
    }
};
