<?php

use App\Enums\RolObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

/**
 * Crea una invitación de obra como lo hace la app: hash en BD,
 * devolviendo [invitación, token plano del correo].
 *
 * @return array{0: Invitacion, 1: string}
 */
function invitacionDeObra(Obra $obra, string $email): array
{
    $token = Invitacion::generarToken();

    $invitacion = Invitacion::create([
        'obra_id' => $obra->id,
        'email' => $email,
        'rol_obra' => RolObra::Asistente->value,
        'token' => Invitacion::hashToken($token),
        'expira_at' => now()->addDays(7),
    ]);

    return [$invitacion, $token];
}

it('guarda el token hasheado y el enlace del correo lo encuentra igual', function () {
    $obra = Obra::factory()->create();
    [$invitacion, $token] = invitacionDeObra($obra, 'nuevo@externo.com');

    // En BD no queda el token plano.
    expect($invitacion->token)->not->toBe($token);

    $this->get(route('invitaciones.mostrar', $token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitaciones/aceptar')
            ->where('puedeAceptar', false));
});

it('el GET del enlace no acepta la invitación aunque la sesión coincida', function () {
    $obra = Obra::factory()->create();
    $user = User::factory()->create(['email' => 'miembro@externo.com', 'email_verified_at' => now()]);
    [$invitacion, $token] = invitacionDeObra($obra, 'miembro@externo.com');

    $this->actingAs($user)
        ->get(route('invitaciones.mostrar', $token))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitaciones/aceptar')
            ->where('puedeAceptar', true)
            ->where('token', $token));

    // Antes el GET aceptaba solo (escáneres de correo podían dispararlo).
    expect($invitacion->fresh()->aceptada_at)->toBeNull();
    expect($obra->usuarios()->where('users.id', $user->id)->exists())->toBeFalse();
});

it('el POST acepta la invitación y vincula al equipo de la obra', function () {
    $obra = Obra::factory()->create();
    $user = User::factory()->create(['email' => 'miembro@externo.com', 'email_verified_at' => now()]);
    [$invitacion, $token] = invitacionDeObra($obra, 'miembro@externo.com');

    $this->actingAs($user)
        ->post(route('invitaciones.aceptar', $token))
        ->assertRedirect(route('obras.show', $obra));

    expect($invitacion->fresh()->aceptada_at)->not->toBeNull();
    expect($obra->usuarios()->where('users.id', $user->id)->exists())->toBeTrue();
});

it('no permite aceptar una invitación dirigida a otro correo', function () {
    $obra = Obra::factory()->create();
    $user = User::factory()->create(['email' => 'otro@externo.com', 'email_verified_at' => now()]);
    [$invitacion, $token] = invitacionDeObra($obra, 'destinatario@externo.com');

    $this->actingAs($user)
        ->post(route('invitaciones.aceptar', $token))
        ->assertForbidden();

    expect($invitacion->fresh()->aceptada_at)->toBeNull();
});

it('un token inexistente muestra la pantalla de invitación inválida', function () {
    $this->get(route('invitaciones.mostrar', str_repeat('x', 64)))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('invitaciones/invalida')
            ->where('estado', 'inexistente'));
});
