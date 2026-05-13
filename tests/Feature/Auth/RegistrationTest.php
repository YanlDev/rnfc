<?php

use App\Enums\RolObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('users without invitation cannot register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Persona sin invitación',
        'email' => 'sin-invitacion@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    expect(User::where('email', 'sin-invitacion@example.com')->exists())->toBeFalse();
});

test('user with active invitation can register and gets attached to obra', function () {
    $admin = User::factory()->create();
    $obra = Obra::factory()->create();

    Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'invitado@example.com',
        'rol_obra' => RolObra::ResidenteObra->value,
        'token' => str_repeat('a', 64),
        'invitado_por' => $admin->id,
        'expira_at' => now()->addDays(7),
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Invitado Test',
        'email' => 'invitado@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'invitado@example.com')->firstOrFail();
    expect($obra->fresh()->usuarios()->where('users.id', $user->id)->exists())->toBeTrue();
});

test('expired invitation does not allow registration', function () {
    $admin = User::factory()->create();
    $obra = Obra::factory()->create();

    Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'expirada@example.com',
        'rol_obra' => RolObra::ResidenteObra->value,
        'token' => str_repeat('b', 64),
        'invitado_por' => $admin->id,
        'expira_at' => now()->subDay(),
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Expirada',
        'email' => 'expirada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
