<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed(RolesSeeder::class))
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/*
|--------------------------------------------------------------------------
| Helpers de roles para tests
|--------------------------------------------------------------------------
*/

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use Database\Seeders\RolesSeeder;

/**
 * Crea un usuario con un rol global dado y email verificado.
 */
function usuarioConRol(RolGlobal $rol): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole($rol->value);

    return $u;
}

/**
 * Crea un usuario con rol Admin y email verificado.
 */
function admin(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

/**
 * Crea un usuario asignado a una obra con un rol_obra específico.
 * El rol global queda como Ingeniero por defecto (no tiene visión global).
 */
function usuarioEnObra(Obra $obra, RolObra $rolObra, ?RolGlobal $rolGlobal = null): User
{
    $u = usuarioConRol($rolGlobal ?? RolGlobal::Ingeniero);

    $obra->usuarios()->attach($u->id, [
        'rol_obra' => $rolObra->value,
        'asignado_at' => now(),
    ]);

    return $u;
}

function something()
{
    // ..
}
