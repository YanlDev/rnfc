<?php

use App\Enums\RolGlobal;
use App\Mail\InvitacionGlobal;
use App\Models\Invitacion;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    Mail::fake();
    Notification::fake();
});

it('admin puede enviar invitación global a un correo sin cuenta', function () {
    $this->actingAs(admin())
        ->post(route('admin.invitar'), [
            'email' => 'nuevo@externo.com',
            'rol_global' => RolGlobal::GerenteGeneral->value,
        ])
        ->assertRedirect();

    $inv = Invitacion::where('email', 'nuevo@externo.com')
        ->whereNotNull('rol_global')
        ->first();

    expect($inv)->not->toBeNull();
    expect($inv->rol_global->value)->toBe(RolGlobal::GerenteGeneral->value);
    expect($inv->estaActiva())->toBeTrue();

    Mail::assertQueued(InvitacionGlobal::class, fn ($m) => $m->hasTo('nuevo@externo.com'));
});

it('no permite invitar global a un correo que ya tiene cuenta', function () {
    User::factory()->create(['email' => 'existente@rnfc.test']);

    $this->actingAs(admin())
        ->post(route('admin.invitar'), [
            'email' => 'existente@rnfc.test',
            'rol_global' => RolGlobal::Admin->value,
        ])
        ->assertSessionHasErrors('email');
});

it('no permite duplicar invitaciones globales pendientes', function () {
    Invitacion::create([
        'email' => 'pendiente@externo.com',
        'rol_global' => RolGlobal::Admin->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ]);

    $this->actingAs(admin())
        ->post(route('admin.invitar'), [
            'email' => 'pendiente@externo.com',
            'rol_global' => RolGlobal::Admin->value,
        ])
        ->assertSessionHasErrors('email');
});

it('solo admin o gerente general puede enviar invitaciones globales', function () {
    $user = User::factory()->create();
    $user->assignRole(RolGlobal::Ingeniero->value);

    $this->actingAs($user)
        ->post(route('admin.invitar'), [
            'email' => 'otro@externo.com',
            'rol_global' => RolGlobal::Admin->value,
        ])
        ->assertForbidden();
});

it('aceptar invitación global al registrarse asigna el rol correctamente', function () {
    Invitacion::create([
        'email' => 'gerente@externo.com',
        'rol_global' => RolGlobal::GerenteGeneral->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ]);

    $this->post('/register', [
        'name' => 'Gerente General',
        'email' => 'gerente@externo.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::where('email', 'gerente@externo.com')->first();
    expect($user)->not->toBeNull();
    expect($user->hasRole(RolGlobal::GerenteGeneral->value))->toBeTrue();

    $inv = Invitacion::where('email', 'gerente@externo.com')->first();
    expect($inv->fresh()->aceptada_at)->not->toBeNull();
});

it('usuario con invitación global es redirigido al dashboard al aceptar con sesión activa', function () {
    $user = User::factory()->create(['email' => 'futuro@externo.com']);

    $inv = Invitacion::create([
        'email' => 'futuro@externo.com',
        'rol_global' => RolGlobal::Admin->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ]);

    $this->actingAs($user)
        ->post(route('invitaciones.aceptar', $inv->token))
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->hasRole(RolGlobal::Admin->value))->toBeTrue();
    expect($inv->fresh()->aceptada_at)->not->toBeNull();
});
