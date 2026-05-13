<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Mail\InvitacionObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Support\Facades\Mail;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesSeeder::class);
    Mail::fake();
});

function admin(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

it('admin puede vincular directo a un usuario que ya tiene cuenta', function () {
    $obra = Obra::factory()->create();
    $existente = User::factory()->create(['email' => 'colaborador@rnfc.test']);

    $this->actingAs(admin())
        ->post(route('obras.equipo.invitar', $obra), [
            'email' => 'colaborador@rnfc.test',
            'rol_obra' => RolObra::ResidenteObra->value,
        ])
        ->assertRedirect();

    expect($obra->usuarios()->where('users.id', $existente->id)->exists())->toBeTrue();
    Mail::assertNothingSent();
});

it('al invitar a un correo nuevo se crea invitación y se envía mail', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(admin())
        ->post(route('obras.equipo.invitar', $obra), [
            'email' => 'nuevo@externo.com',
            'rol_obra' => RolObra::Asistente->value,
        ])
        ->assertRedirect();

    $inv = Invitacion::where('email', 'nuevo@externo.com')->first();
    expect($inv)->not->toBeNull();
    expect($inv->obra_id)->toBe($obra->id);
    expect($inv->estaActiva())->toBeTrue();

    Mail::assertQueued(InvitacionObra::class, fn ($m) => $m->hasTo('nuevo@externo.com'));
});

it('no permite duplicar invitaciones pendientes', function () {
    $obra = Obra::factory()->create();
    Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'pendiente@externo.com',
        'rol_obra' => RolObra::Asistente->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ]);

    $this->actingAs(admin())
        ->post(route('obras.equipo.invitar', $obra), [
            'email' => 'pendiente@externo.com',
            'rol_obra' => RolObra::Asistente->value,
        ])
        ->assertSessionHasErrors('email');
});

it('aceptar invitación al registrarse vincula automáticamente a la obra', function () {
    $obra = Obra::factory()->create();
    $invitacion = Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'recluta@externo.com',
        'rol_obra' => RolObra::ResidenteObra->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ]);

    $this->post('/register', [
        'name' => 'Recluta',
        'email' => 'recluta@externo.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $user = User::where('email', 'recluta@externo.com')->first();
    expect($user)->not->toBeNull();
    expect($obra->usuarios()->where('users.id', $user->id)->exists())->toBeTrue();
    expect($obra->usuarios()->wherePivot('rol_obra', RolObra::ResidenteObra->value)->exists())->toBeTrue();
    expect($invitacion->fresh()->aceptada_at)->not->toBeNull();
});

it('un invitado global (no admin/supervisor) no puede ver obras a las que no pertenece', function () {
    $obra = Obra::factory()->create();
    $invitado = User::factory()->create(['email_verified_at' => now()]);
    $invitado->assignRole(RolGlobal::Invitado->value);

    $this->actingAs($invitado)
        ->get(route('obras.show', $obra))
        ->assertForbidden();
});

it('un usuario vinculado a una obra puede verla aunque no sea admin', function () {
    $obra = Obra::factory()->create();
    $miembro = User::factory()->create(['email_verified_at' => now()]);
    $miembro->assignRole(RolGlobal::Ingeniero->value);
    $obra->usuarios()->attach($miembro->id, [
        'rol_obra' => RolObra::Asistente->value,
        'asignado_at' => now(),
    ]);

    $this->actingAs($miembro)
        ->get(route('obras.show', $obra))
        ->assertOk();
});

it('cancelar invitación marca cancelada_at', function () {
    $obra = Obra::factory()->create();
    $inv = Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'cancelar@externo.com',
        'rol_obra' => RolObra::Asistente->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ]);

    $this->actingAs(admin())
        ->delete(route('obras.invitaciones.cancelar', [$obra, $inv]))
        ->assertRedirect();

    expect($inv->fresh()->cancelada_at)->not->toBeNull();
});

it('admin puede remover a un miembro del equipo', function () {
    $obra = Obra::factory()->create();
    $miembro = User::factory()->create();
    $obra->usuarios()->attach($miembro->id, [
        'rol_obra' => RolObra::Asistente->value,
        'asignado_at' => now(),
    ]);

    $this->actingAs(admin())
        ->delete(route('obras.equipo.remover', [$obra, $miembro]))
        ->assertRedirect();

    expect($obra->usuarios()->where('users.id', $miembro->id)->exists())->toBeFalse();
});
