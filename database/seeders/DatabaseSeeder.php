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

        $admin = User::updateOrCreate(
            ['email' => 'admin@rnfc.test'],
            [
                'name' => 'Roger Neptali Flores Coaquira',
                'password' => Hash::make('rnfc2026'),
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole(RolGlobal::Admin->value)) {
            $admin->assignRole(RolGlobal::Admin->value);
        }
    }
}
