<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Normaliza a minúsculas los emails existentes en users e invitaciones.
     * A partir de ahora el modelo User los guarda siempre en minúsculas
     * (mutador) y las invitaciones ya se creaban con strtolower().
     *
     * Si existieran dos cuentas cuyo email solo difiere en mayúsculas, el
     * UPDATE falla por el unique de users.email: eso es intencional, hay que
     * resolver el duplicado a mano antes de migrar.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereRaw('email <> LOWER(email)')
            ->update(['email' => DB::raw('LOWER(email)')]);

        DB::table('invitaciones')
            ->whereRaw('email <> LOWER(email)')
            ->update(['email' => DB::raw('LOWER(email)')]);
    }

    public function down(): void
    {
        // No hay vuelta atrás: la capitalización original se pierde.
    }
};
