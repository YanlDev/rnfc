<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

function creadoraDeObras(): User
{
    $u = User::factory()->create([
        'email_verified_at' => now(),
        'puede_crear_obras' => true,
    ]);
    $u->assignRole(RolGlobal::Usuario->value);

    return $u;
}

function datosObra(): array
{
    return [
        'nombre' => 'MEJORAMIENTO DE VIAS VECINALES DE PRUEBA',
        'estado' => 'en_ejecucion',
    ];
}

it('usuario habilitado crea una obra y queda como administrador de obra', function () {
    $creadora = creadoraDeObras();

    $this->actingAs($creadora)
        ->post(route('obras.store'), datosObra())
        ->assertRedirect();

    $obra = Obra::first();
    expect($obra)->not->toBeNull();
    expect($obra->creado_por)->toBe($creadora->id);
    expect(PermisosObra::rolEnObra($creadora, $obra))->toBe(RolObra::Administrador->value);

    // Como admin de obra: maneja caja, equipo y datos de su obra.
    expect(PermisosObra::puede($creadora, $obra, 'caja.ver'))->toBeTrue();
    expect(PermisosObra::puede($creadora, $obra, 'equipo.gestionar'))->toBeTrue();
    expect(PermisosObra::puede($creadora, $obra, 'obra.editar'))->toBeTrue();
});

it('usuario sin habilitación no puede crear obras', function () {
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);

    $this->actingAs($u)
        ->post(route('obras.store'), datosObra())
        ->assertForbidden();

    $this->actingAs($u)
        ->get(route('obras.create'))
        ->assertForbidden();
});

it('cada creadora ve solo sus obras; el admin general ve todas', function () {
    $creadoraA = creadoraDeObras();
    $creadoraB = creadoraDeObras();

    $this->actingAs($creadoraA)->post(route('obras.store'), datosObra());
    $this->actingAs($creadoraB)->post(route('obras.store'), [
        'nombre' => 'OBRA DE LA OTRA ADMINISTRADORA',
        'estado' => 'en_ejecucion',
    ]);

    $obraA = Obra::where('creado_por', $creadoraA->id)->first();
    $obraB = Obra::where('creado_por', $creadoraB->id)->first();

    // A ve la suya, no la de B.
    $this->actingAs($creadoraA)->get(route('obras.show', $obraA))->assertOk();
    $this->actingAs($creadoraA)->get(route('obras.show', $obraB))->assertForbidden();

    // El listado de A sólo contiene su obra.
    $this->actingAs($creadoraA)
        ->get(route('obras.index'))
        ->assertInertia(fn ($page) => $page
            ->where('obras.data.0.id', $obraA->id)
            ->count('obras.data', 1),
        );

    // Admin general ve ambas.
    $admin = admin();
    $this->actingAs($admin)->get(route('obras.show', $obraA))->assertOk();
    $this->actingAs($admin)->get(route('obras.show', $obraB))->assertOk();
});

it('el diálogo de rol habilita crear obras, incluso en un usuario restaurado', function () {
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);
    $u->delete();
    $u->restore();

    // Mismo rol Usuario, pero marcando la casilla "podrá crear obras".
    $this->actingAs(admin())
        ->patch(route('admin.usuarios.rol', $u), [
            'rol' => RolGlobal::Usuario->value,
            'puede_crear_obras' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($u->fresh()->puede_crear_obras)->toBeTrue();
});

it('el flag crear-obras se limpia si el rol deja de ser Usuario', function () {
    $u = User::factory()->create([
        'email_verified_at' => now(),
        'puede_crear_obras' => true,
    ]);
    $u->assignRole(RolGlobal::Usuario->value);

    $this->actingAs(admin())
        ->patch(route('admin.usuarios.rol', $u), [
            'rol' => RolGlobal::Gerente->value,
            'puede_crear_obras' => true,
        ])
        ->assertRedirect();

    expect($u->fresh()->puede_crear_obras)->toBeFalse();
});

it('el admin puede activar y desactivar el permiso de crear obras', function () {
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);

    $this->actingAs(admin())
        ->patch(route('admin.usuarios.crear-obras', $u))
        ->assertRedirect();

    expect($u->fresh()->puede_crear_obras)->toBeTrue();

    $this->actingAs(admin())
        ->patch(route('admin.usuarios.crear-obras', $u))
        ->assertRedirect();

    expect($u->fresh()->puede_crear_obras)->toBeFalse();
});

it('el admin de plataforma no se agrega al pivot al crear (ya ve todo)', function () {
    $admin = admin();

    $this->actingAs($admin)
        ->post(route('obras.store'), datosObra())
        ->assertRedirect();

    $obra = Obra::first();
    expect(PermisosObra::rolEnObra($admin, $obra))->toBeNull();
    expect($obra->creado_por)->toBe($admin->id);
});
