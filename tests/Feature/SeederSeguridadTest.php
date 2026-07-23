<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('no crea un admin con credenciales conocidas en producción', function () {
    app()->detectEnvironment(fn () => 'production');

    // Instanciamos el seeder directo (sin la capa `db:seed`, que en producción
    // pediría confirmación interactiva).
    $seeder = new DatabaseSeeder;
    $seeder->setContainer(app());
    $seeder->run();

    expect(User::where('email', 'admin@rnfc.test')->exists())->toBeFalse();
});

it('crea el admin de conveniencia en entornos locales/testing', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::where('email', 'admin@rnfc.test')->first();
    expect($admin)->not->toBeNull();
    expect($admin->hasRole('admin'))->toBeTrue();
});
