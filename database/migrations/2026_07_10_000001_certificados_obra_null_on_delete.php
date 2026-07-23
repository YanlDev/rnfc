<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los certificados son documentos permanentes con verificación pública: borrar
 * una obra NO debe destruirlos. Se revierte la FK a nullOnDelete (como nació
 * la tabla); la migración 2026_05_11_210423 la había puesto en cascade.
 *
 * El nombre/entidad de la obra se preserva en los campos "libres" del
 * certificado en el momento del borrado (ver ObraController@destroy).
 */
return new class extends Migration
{
    public function up(): void
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
                ->cascadeOnDelete();
        });
    }
};
