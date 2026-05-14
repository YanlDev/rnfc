<?php

namespace App\Console\Commands;

use App\Enums\RolGlobal;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CrearAdminCommand extends Command
{
    protected $signature = 'admin:crear
        {--email= : Email del admin (si se omite se pregunta)}
        {--name= : Nombre del admin (si se omite se pregunta)}
        {--password= : Password (si se omite se pregunta en modo oculto)}';

    protected $description = 'Crea o promueve un usuario al rol Admin. Idempotente.';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email');
        $name = $this->option('name') ?: $this->ask('Nombre completo');
        $password = $this->option('password') ?: $this->secret('Password (mínimo 12 caracteres)');

        $datos = compact('email', 'name', 'password');

        $reglas = [
            'email' => ['required', 'email:rfc'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ];

        $validator = Validator::make($datos, $reglas);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::firstWhere('email', $email);

        if ($user) {
            $this->info("Usuario encontrado (id={$user->id}). Se actualizarán nombre y password.");
            $user->forceFill([
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            $user = User::create([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("Usuario creado (id={$user->id}).");
        }

        if (! $user->hasRole(RolGlobal::Admin->value)) {
            $user->assignRole(RolGlobal::Admin->value);
            $this->info('Rol Admin asignado.');
        } else {
            $this->line('El usuario ya tenía el rol Admin.');
        }

        $this->newLine();
        $this->info("Listo: {$user->email} es Admin.");

        return self::SUCCESS;
    }
}
