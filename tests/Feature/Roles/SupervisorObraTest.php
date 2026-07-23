<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

function supervisorEn(Obra $obra): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);
    $obra->usuarios()->attach($u->id, [
        'rol_obra' => RolObra::Supervisor->value,
        'asignado_at' => now(),
    ]);

    return $u;
}

it('el supervisor tiene lectura y escritura de cuaderno por defecto', function () {
    $obra = Obra::factory()->create();
    $sup = supervisorEn($obra);

    expect(PermisosObra::puede($sup, $obra, 'documento.ver'))->toBeTrue();
    expect(PermisosObra::puede($sup, $obra, 'cuaderno.ver'))->toBeTrue();
    expect(PermisosObra::puede($sup, $obra, 'cuaderno.escribir'))->toBeTrue();
    expect(PermisosObra::puede($sup, $obra, 'calendario.ver'))->toBeTrue();
});

it('el supervisor no tiene acceso a la caja chica ni gestión', function () {
    $obra = Obra::factory()->create();
    $sup = supervisorEn($obra);

    expect(PermisosObra::puede($sup, $obra, 'caja.ver'))->toBeFalse();
    expect(PermisosObra::puede($sup, $obra, 'documento.eliminar'))->toBeFalse();
    expect(PermisosObra::puede($sup, $obra, 'equipo.gestionar'))->toBeFalse();

    $this->actingAs($sup)
        ->get(route('obras.caja.index', $obra))
        ->assertForbidden();
});

it('supervisor es un rol de obra invitable', function () {
    expect(RolObra::values())->toContain('supervisor');
});
