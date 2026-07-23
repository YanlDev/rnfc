<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Support\PermisosObra;

function miembroObraCaja(Obra $obra, RolObra $rol): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);
    $obra->usuarios()->attach($u->id, ['rol_obra' => $rol->value, 'asignado_at' => now()]);

    return $u;
}

it('sincronizar ignora permisos de caja para roles distintos de administrador', function () {
    PermisosObra::sincronizar([
        RolObra::Residente->value => ['caja.ver' => true, 'documento.ver' => true],
        RolObra::Administrador->value => ['caja.ver' => true],
    ]);

    $filas = DB::table('permisos_obra')->where('permiso', 'caja.ver')->pluck('rol_obra');
    expect($filas->all())->toBe([RolObra::Administrador->value]);

    // El permiso no reservado sí se concedió al residente.
    expect(
        DB::table('permisos_obra')
            ->where('permiso', 'documento.ver')
            ->where('rol_obra', RolObra::Residente->value)
            ->exists(),
    )->toBeTrue();
});

it('una fila antigua de caja para otro rol no concede acceso', function () {
    $obra = Obra::factory()->create();
    $residente = miembroObraCaja($obra, RolObra::Residente);

    // Simula una concesión previa a la restricción, directa en BD.
    DB::table('permisos_obra')->insert([
        'obra_id' => null,
        'rol_obra' => RolObra::Residente->value,
        'permiso' => 'caja.ver',
    ]);
    Cache::forget('permisos_obra_map');

    expect(PermisosObra::puede($residente, $obra, 'caja.ver'))->toBeFalse();

    $this->actingAs($residente)
        ->get(route('obras.caja.index', $obra))
        ->assertForbidden();
});

it('el administrador de obra sí accede a la caja con el permiso concedido', function () {
    $obra = Obra::factory()->create();
    $adminObra = miembroObraCaja($obra, RolObra::Administrador);

    expect(PermisosObra::puede($adminObra, $obra, 'caja.ver'))->toBeTrue();

    $this->actingAs($adminObra)
        ->get(route('obras.caja.index', $obra))
        ->assertOk();
});

it('el catálogo marca los permisos de caja como exclusivos del administrador', function () {
    foreach (['caja.ver', 'caja.registrar', 'caja.gestionar'] as $permiso) {
        expect(PermisosObra::permitidoParaRol($permiso, RolObra::Residente->value))->toBeFalse();
        expect(PermisosObra::permitidoParaRol($permiso, RolObra::Administrador->value))->toBeTrue();
    }

    expect(PermisosObra::permitidoParaRol('documento.ver', RolObra::Residente->value))->toBeTrue();
});
