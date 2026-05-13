<?php

namespace Database\Seeders;

use App\Enums\RolGlobal;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RolGlobal::cases() as $rol) {
            Role::findOrCreate($rol->value, 'web');
        }
    }
}
