<?php

use App\Enums\EstadoObra;
use App\Enums\RolGlobal;
use App\Models\Obra;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Inertia\Testing\AssertableInertia as Assert;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesSeeder::class);
});

function adminUser(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

function invitadoUser(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Invitado->value);

    return $u;
}

it('admin puede listar obras', function () {
    Obra::factory()->count(3)->create();

    $this->actingAs(adminUser())
        ->get(route('obras.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('obras/index')
            ->has('obras.data', 3)
        );
});

it('usuario no autenticado es redirigido al login', function () {
    $this->get(route('obras.index'))
        ->assertRedirect(route('login'));
});

it('admin puede crear una obra', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('obras.store'), [
            'codigo' => 'OBR-2026-0001',
            'nombre' => 'Mejoramiento de pistas Av. Los Olivos',
            'estado' => EstadoObra::Planificacion->value,
            'entidad_contratante' => 'Municipalidad de Lima',
            'monto_contractual' => 500000.50,
        ])
        ->assertRedirect();

    $obra = Obra::where('codigo', 'OBR-2026-0001')->first();
    expect($obra)->not->toBeNull();
    expect($obra->creado_por)->toBe($admin->id);
    expect($obra->estado)->toBe(EstadoObra::Planificacion);
});

it('código de obra debe ser único', function () {
    Obra::factory()->create(['codigo' => 'OBR-2026-0001']);

    $this->actingAs(adminUser())
        ->post(route('obras.store'), [
            'codigo' => 'OBR-2026-0001',
            'nombre' => 'Otra obra',
            'estado' => EstadoObra::Planificacion->value,
        ])
        ->assertSessionHasErrors('codigo');
});

it('fecha fin prevista debe ser posterior al inicio', function () {
    $this->actingAs(adminUser())
        ->post(route('obras.store'), [
            'codigo' => 'OBR-2026-0002',
            'nombre' => 'Obra mal fechada',
            'estado' => EstadoObra::Planificacion->value,
            'fecha_inicio' => '2026-06-01',
            'fecha_fin_prevista' => '2026-05-01',
        ])
        ->assertSessionHasErrors('fecha_fin_prevista');
});

it('admin puede actualizar una obra', function () {
    $obra = Obra::factory()->create(['nombre' => 'Original']);

    $this->actingAs(adminUser())
        ->put(route('obras.update', $obra), [
            'codigo' => $obra->codigo,
            'nombre' => 'Nombre actualizado',
            'estado' => EstadoObra::EnEjecucion->value,
        ])
        ->assertRedirect();

    expect($obra->fresh()->nombre)->toBe('Nombre actualizado');
    expect($obra->fresh()->estado)->toBe(EstadoObra::EnEjecucion);
});

it('admin puede eliminar una obra (cascada a certificados)', function () {
    $obra = Obra::factory()->create();
    $cert = App\Models\Certificado::factory()->create(['obra_id' => $obra->id]);

    $this->actingAs(adminUser())
        ->delete(route('obras.destroy', $obra))
        ->assertRedirect();

    expect(Obra::find($obra->id))->toBeNull();
    expect(App\Models\Certificado::find($cert->id))->toBeNull();
});

it('invitado no puede crear obras', function () {
    $this->actingAs(invitadoUser())
        ->get(route('obras.create'))
        ->assertForbidden();

    $this->actingAs(invitadoUser())
        ->post(route('obras.store'), [
            'codigo' => 'OBR-2026-9999',
            'nombre' => 'Intento',
            'estado' => EstadoObra::Planificacion->value,
        ])
        ->assertForbidden();
});

it('solo admin/gerente general puede eliminar obras', function () {
    $obra = Obra::factory()->create();
    $ingeniero = User::factory()->create(['email_verified_at' => now()]);
    $ingeniero->assignRole(RolGlobal::Ingeniero->value);

    $this->actingAs($ingeniero)
        ->delete(route('obras.destroy', $obra))
        ->assertForbidden();

    expect(Obra::find($obra->id))->not->toBeNull();
});

it('búsqueda filtra por código y nombre', function () {
    Obra::factory()->create(['codigo' => 'OBR-2026-0001', 'nombre' => 'Pistas Av. Lima']);
    Obra::factory()->create(['codigo' => 'OBR-2026-0002', 'nombre' => 'Puente Río Apurímac']);

    $this->actingAs(adminUser())
        ->get(route('obras.index', ['q' => 'puente']))
        ->assertInertia(fn (Assert $page) => $page->has('obras.data', 1));
});
