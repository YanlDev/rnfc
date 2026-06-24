<?php

namespace App\Console\Commands;

use App\Models\UploadSession;
use Illuminate\Console\Command;

class LimpiarSubidas extends Command
{
    protected $signature = 'rnfc:limpiar-subidas';

    protected $description = 'Borra sesiones de subida abandonadas y sus trozos temporales.';

    public function handle(): int
    {
        $limite = now()->subHours((int) config('uploads.ttl_horas'));

        $sesiones = UploadSession::where('created_at', '<', $limite)
            ->whereNotIn('estado', ['completado'])
            ->get();

        $borradas = 0;

        foreach ($sesiones as $sesion) {
            $dir = $sesion->directorioTemporal();
            if (is_dir($dir)) {
                foreach (glob($dir.'/*') ?: [] as $f) {
                    @unlink($f);
                }
                @rmdir($dir);
            }
            $sesion->delete();
            $borradas++;
        }

        $this->info("Sesiones de subida abandonadas eliminadas: {$borradas}.");

        return self::SUCCESS;
    }
}
