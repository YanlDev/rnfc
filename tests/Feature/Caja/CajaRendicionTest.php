<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\CajaMovimiento;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function adminCaja(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

function usuarioCaja(Obra $obra, string $rol = RolObra::Administrador->value): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);
    $obra->usuarios()->attach($u->id, ['rol_obra' => $rol, 'asignado_at' => now()]);

    return $u;
}

beforeEach(function () {
    Storage::fake('documentos');
});

it('registra un gasto con tipo de comprobante y proveedor', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(adminCaja())
        ->post(route('obras.caja.store', $obra), [
            'tipo' => 'egreso',
            'tipo_comprobante' => 'factura',
            'proveedor' => 'GRIFOS YEFREE',
            'descripcion' => 'WG GA003 DIESEL',
            'monto' => 150,
            'fecha' => now()->toDateString(),
        ])
        ->assertRedirect();

    $m = CajaMovimiento::first();
    expect($m->tipo_comprobante->value)->toBe('factura');
    expect($m->proveedor)->toBe('GRIFOS YEFREE');
});

it('exige tipo de comprobante en los gastos pero no en los depósitos', function () {
    $obra = Obra::factory()->create();
    $admin = adminCaja();

    $this->actingAs($admin)
        ->post(route('obras.caja.store', $obra), [
            'tipo' => 'egreso',
            'descripcion' => 'Sin comprobante',
            'monto' => 10,
            'fecha' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('tipo_comprobante');

    $this->actingAs($admin)
        ->post(route('obras.caja.store', $obra), [
            'tipo' => 'ingreso',
            'metodo' => 'yape',
            'descripcion' => 'DEPÓSITO YAPE',
            'monto' => 500,
            'fecha' => now()->toDateString(),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(CajaMovimiento::first()->metodo->value)->toBe('yape');
});

it('edita una celda inline vía PATCH', function () {
    $obra = Obra::factory()->create();
    $admin = adminCaja();

    $m = $obra->cajaMovimientos()->create([
        'tipo' => 'egreso',
        'tipo_comprobante' => 'boleta',
        'descripcion' => 'BALON DE GAS',
        'monto' => 55,
        'fecha' => now()->toDateString(),
    ]);

    $this->actingAs($admin)
        ->patch(route('obras.caja.update', [$obra, $m]), ['monto' => 56.5])
        ->assertRedirect();

    expect((float) $m->fresh()->monto)->toBe(56.5);
});

it('adjunta un comprobante a un movimiento existente', function () {
    $obra = Obra::factory()->create();

    $m = $obra->cajaMovimientos()->create([
        'tipo' => 'egreso',
        'tipo_comprobante' => 'recibo',
        'descripcion' => 'COPIA DE LLAVES',
        'monto' => 25,
        'fecha' => now()->toDateString(),
    ]);

    $this->actingAs(adminCaja())
        ->post(route('obras.caja.comprobante.subir', [$obra, $m]), [
            'comprobante' => UploadedFile::fake()->create('recibo.pdf', 100, 'application/pdf'),
        ])
        ->assertRedirect();

    $m->refresh();
    expect($m->tieneComprobante())->toBeTrue();
    Storage::disk('documentos')->assertExists($m->comprobante_path);
});

it('miembro sin permiso de caja no puede editar movimientos', function () {
    $obra = Obra::factory()->create();
    $lector = usuarioCaja($obra, RolObra::Asistente->value);

    $m = $obra->cajaMovimientos()->create([
        'tipo' => 'egreso',
        'tipo_comprobante' => 'boleta',
        'descripcion' => 'GASTO',
        'monto' => 10,
        'fecha' => now()->toDateString(),
    ]);

    $this->actingAs($lector)
        ->patch(route('obras.caja.update', [$obra, $m]), ['monto' => 999])
        ->assertForbidden();
});

it('no permite editar un movimiento de otra obra', function () {
    $obra = Obra::factory()->create();
    $otra = Obra::factory()->create();

    $m = $otra->cajaMovimientos()->create([
        'tipo' => 'egreso',
        'tipo_comprobante' => 'boleta',
        'descripcion' => 'GASTO',
        'monto' => 10,
        'fecha' => now()->toDateString(),
    ]);

    $this->actingAs(adminCaja())
        ->patch(route('obras.caja.update', [$obra, $m]), ['monto' => 999])
        ->assertNotFound();
});

it('el índice expone subtotales por tipo de comprobante y proveedores', function () {
    $obra = Obra::factory()->create();

    foreach ([['factura', 100, 'GRIFOS YEFREE'], ['boleta', 50, 'BIANCAS'], ['recibo', 200, null]] as [$tc, $monto, $prov]) {
        $obra->cajaMovimientos()->create([
            'tipo' => 'egreso',
            'tipo_comprobante' => $tc,
            'proveedor' => $prov,
            'descripcion' => 'GASTO',
            'monto' => $monto,
            'fecha' => now()->toDateString(),
        ]);
    }

    $this->actingAs(adminCaja())
        ->get(route('obras.caja.index', $obra))
        ->assertInertia(fn ($page) => $page
            ->component('obras/caja/index')
            ->where('resumen.por_comprobante.factura', 100)
            ->where('resumen.por_comprobante.boleta', 50)
            ->where('resumen.por_comprobante.recibo', 200)
            ->where('proveedores', ['BIANCAS', 'GRIFOS YEFREE']),
        );
});
