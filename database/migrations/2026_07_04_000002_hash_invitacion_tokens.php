<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los tokens de invitación pasan a guardarse hasheados (sha256): son
     * credenciales de acceso y en texto plano cualquiera con lectura de la
     * tabla podría aceptar invitaciones pendientes, incluidas las de admin.
     *
     * Se hashea el valor almacenado: los enlaces ya enviados por correo
     * llevan el token plano y siguen funcionando (el lookup hashea antes
     * de comparar). Ejecutar UNA sola vez: no es idempotente.
     */
    public function up(): void
    {
        DB::table('invitaciones')
            ->select('id', 'token')
            ->orderBy('id')
            ->chunkById(500, function ($invitaciones) {
                foreach ($invitaciones as $invitacion) {
                    DB::table('invitaciones')
                        ->where('id', $invitacion->id)
                        ->update(['token' => hash('sha256', $invitacion->token)]);
                }
            });
    }

    public function down(): void
    {
        // Irreversible: el token plano no se puede recuperar del hash.
    }
};
