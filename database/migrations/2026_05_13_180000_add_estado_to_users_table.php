<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('desactivado_at')->nullable()->after('remember_token');
            $table->foreignId('desactivado_por')->nullable()
                ->after('desactivado_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('motivo_desactivacion')->nullable()->after('desactivado_por');
            $table->timestamp('last_login_at')->nullable()->after('motivo_desactivacion');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->index('desactivado_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['desactivado_at']);
            $table->dropConstrainedForeignId('desactivado_por');
            $table->dropColumn([
                'desactivado_at',
                'motivo_desactivacion',
                'last_login_at',
                'last_login_ip',
            ]);
        });
    }
};
