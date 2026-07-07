<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

it('guarda el email siempre en minúsculas', function () {
    $user = User::factory()->create(['email' => '  Juan.Perez@RNFC.Test ']);

    expect($user->email)->toBe('juan.perez@rnfc.test');
    $this->assertDatabaseHas('users', ['email' => 'juan.perez@rnfc.test']);
});

it('permite iniciar sesión escribiendo el email con mayúsculas', function () {
    User::factory()->create([
        'email' => 'residente@rnfc.test',
        'email_verified_at' => now(),
    ]);

    $this->post(route('login'), [
        'email' => 'Residente@RNFC.Test',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
});

it('vincula directo al equipo a un usuario existente aunque el email se escriba con otra capitalización', function () {
    $obra = Obra::factory()->create();
    $existente = User::factory()->create(['email' => 'ing@rnfc.test']);

    $this->actingAs(admin())
        ->post(route('obras.equipo.invitar', $obra), [
            'email' => 'ING@rnfc.test',
            'rol_obra' => RolObra::Asistente->value,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    // Se vincula sin crear invitación pendiente.
    expect($obra->usuarios()->where('users.id', $existente->id)->exists())->toBeTrue();
    expect(Invitacion::where('email', 'ing@rnfc.test')->exists())->toBeFalse();
});

it('no permite invitar a una obra el email de un usuario en la papelera', function () {
    $obra = Obra::factory()->create();
    $eliminado = User::factory()->create(['email' => 'borrado@rnfc.test']);
    $eliminado->delete();

    $this->actingAs(admin())
        ->post(route('obras.equipo.invitar', $obra), [
            'email' => 'borrado@rnfc.test',
            'rol_obra' => RolObra::Asistente->value,
        ])
        ->assertSessionHasErrors('email');

    expect(Invitacion::where('email', 'borrado@rnfc.test')->exists())->toBeFalse();
});

it('no permite invitación global al email de un usuario en la papelera', function () {
    $eliminado = User::factory()->create(['email' => 'borrado-global@rnfc.test']);
    $eliminado->delete();

    $this->actingAs(admin())
        ->post(route('admin.invitar'), [
            'email' => 'borrado-global@rnfc.test',
            'rol_global' => RolGlobal::Admin->value,
        ])
        ->assertSessionHasErrors('email');

    expect(Invitacion::where('email', 'borrado-global@rnfc.test')->exists())->toBeFalse();
});
