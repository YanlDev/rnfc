<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;
use Illuminate\Support\Facades\DB;

function adminDeObra(Obra $obra): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);
    $obra->usuarios()->attach($u->id, [
        'rol_obra' => RolObra::Administrador->value,
        'asignado_at' => now(),
    ]);

    return $u;
}

function miembroSimple(Obra $obra, RolObra $rol = RolObra::Asistente): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);
    $obra->usuarios()->attach($u->id, [
        'rol_obra' => $rol->value,
        'asignado_at' => now(),
    ]);

    return $u;
}

it('el admin de obra puede emitir certificados', function () {
    $obra = Obra::factory()->create();
    $admin = adminDeObra($obra);

    expect($admin->can('viewAny', App\Models\Certificado::class))->toBeTrue();
    expect($admin->can('create', App\Models\Certificado::class))->toBeTrue();

    $this->actingAs($admin)->get(route('certificados.index'))->assertOk();
});

it('un miembro normal no ve certificados', function () {
    $obra = Obra::factory()->create();
    $asistente = miembroSimple($obra);

    expect($asistente->can('viewAny', App\Models\Certificado::class))->toBeFalse();
    $this->actingAs($asistente)->get(route('certificados.index'))->assertForbidden();
});

it('el admin de obra configura los permisos de su obra', function () {
    $obra = Obra::factory()->create();
    $admin = adminDeObra($obra);

    $this->actingAs($admin)->get(route('obras.permisos.index', $obra))->assertOk();

    $this->actingAs($admin)
        ->put(route('obras.permisos.update', $obra), [
            'matriz' => [
                RolObra::Supervisor->value => ['documento.subir' => true],
            ],
        ])
        ->assertRedirect();

    $supervisor = miembroSimple($obra, RolObra::Supervisor);
    expect(PermisosObra::puede($supervisor, $obra, 'documento.subir'))->toBeTrue();
});

it('no puede configurar permisos de una obra ajena', function () {
    $obra = Obra::factory()->create();
    $otra = Obra::factory()->create();
    $admin = adminDeObra($obra);

    $this->actingAs($admin)
        ->get(route('obras.permisos.index', $otra))
        ->assertForbidden();

    $this->actingAs($admin)
        ->put(route('obras.permisos.update', $otra), ['matriz' => []])
        ->assertForbidden();
});

it('la matriz por obra no puede conceder caja chica a otros roles', function () {
    $obra = Obra::factory()->create();
    $admin = adminDeObra($obra);

    $this->actingAs($admin)
        ->put(route('obras.permisos.update', $obra), [
            'matriz' => [
                RolObra::Residente->value => ['caja.ver' => true],
            ],
        ])
        ->assertRedirect();

    $residente = miembroSimple($obra, RolObra::Residente);
    expect(PermisosObra::puede($residente, $obra, 'caja.ver'))->toBeFalse();
});

it('la matriz por obra no altera los poderes del rol Administrador', function () {
    $obra = Obra::factory()->create();
    $admin = adminDeObra($obra);

    // Intenta quitarle la caja al Administrador desde el panel de su obra.
    $this->actingAs($admin)
        ->put(route('obras.permisos.update', $obra), [
            'matriz' => [
                RolObra::Administrador->value => ['caja.ver' => false],
                RolObra::Residente->value => ['documento.ver' => true],
            ],
        ])
        ->assertRedirect();

    // El Administrador conserva la caja (fila preservada por sincronizarObra).
    expect(PermisosObra::puede($admin, $obra, 'caja.ver'))->toBeTrue();
});

it('un miembro no administrador no accede al panel de permisos de la obra', function () {
    $obra = Obra::factory()->create();
    $asistente = miembroSimple($obra);

    $this->actingAs($asistente)
        ->get(route('obras.permisos.index', $obra))
        ->assertForbidden();
});
