<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quitar softDeletes de obras.
        Schema::table('obras', function (Blueprint $table) {
            if (Schema::hasColumn('obras', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        // Cambiar FK certificados.obra_id a cascadeOnDelete.
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropForeign(['obra_id']);
        });
        Schema::table('certificados', function (Blueprint $table) {
            $table->foreign('obra_id')
                ->references('id')
                ->on('obras')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->dropForeign(['obra_id']);
        });
        Schema::table('certificados', function (Blueprint $table) {
            $table->foreign('obra_id')
                ->references('id')
                ->on('obras')
                ->nullOnDelete();
        });

        Schema::table('obras', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
