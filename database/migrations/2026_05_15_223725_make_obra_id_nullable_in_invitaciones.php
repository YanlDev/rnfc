<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invitaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('obra_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('obra_id')->nullable(false)->change();
            $table->string('rol_obra')->nullable()->change();  
        });
    }
};
