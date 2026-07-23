<?php

namespace Database\Seeders;

use App\Enums\RolGlobal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesSeeder::class);

        // En producción nunca se crea un admin con credenciales conocidas:
        // el primer administrador se crea con `php artisan admin:crear` (pide
        // una contraseña segura de forma interactiva).
        if (app()->isProduction()) {
            $this->command?->warn('Producción: omitido el admin de conveniencia. Crea el primer admin con `php artisan admin:crear`.');

            return;
        }

        // Sólo en entornos no productivos (local/testing): admin de conveniencia.
        // Las credenciales son configurables por entorno para no fijarlas en el repo.
        $admin = User::updateOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@rnfc.test')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Administrador RNFC'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'rnfc2026')),
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole(RolGlobal::Admin->value)) {
            $admin->assignRole(RolGlobal::Admin->value);
        }
    }
}
