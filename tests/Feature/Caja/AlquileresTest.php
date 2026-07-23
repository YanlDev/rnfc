<?php

use App\Enums\RolGlobal;
use App\Enums\TipoComprobante;
use App\Enums\TipoMovimientoCaja;
use App\Models\Alquiler;
use App\Models\AlquilerPago;
use App\Models\CajaMovimiento;
use App\Models\Obra;
use App\Models\User;

function adminAlq(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

it('crea un alquiler recurrente', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(adminAlq())
        ->post(route('obras.alquileres.store', $obra), [
            'inquilino' => 'ING GENARO',
            'monto_mensual' => 400,
            'forma_pago' => 'adelantado',
            'fecha_inicio' => '2026-01-15',
        ])
        ->assertRedirect();

    $a = Alquiler::first();
    expect($a->inquilino)->toBe('ING GENARO');
    expect((float) $a->monto_mensual)->toBe(400.0);
});

it('pagar un mes crea el egreso (recibo) en la caja', function () {
    $obra = Obra::factory()->create();
    $alquiler = $obra->alquileres()->create([
        'inquilino' => 'ING MANUEL',
        'monto_mensual' => 250,
        'forma_pago' => 'fin_de_mes',
        'fecha_inicio' => '2026-01-01',
    ]);

    $this->actingAs(adminAlq())
        ->post(route('obras.alquileres.pagar', [$obra, $alquiler]), [
            'periodo' => '2026-06',
            'fecha_pago' => now()->toDateString(),
            'monto' => 250,
        ])
        ->assertRedirect();

    $pago = AlquilerPago::first();
    expect($pago)->not->toBeNull();
    expect($pago->periodo->format('Y-m'))->toBe('2026-06');

    $movimiento = $pago->cajaMovimiento;
    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo)->toBe(TipoMovimientoCaja::Egreso);
    expect($movimiento->tipo_comprobante)->toBe(TipoComprobante::Recibo);
    expect($movimiento->proveedor)->toBe('ING MANUEL');
    expect((float) $movimiento->monto)->toBe(250.0);
    expect($movimiento->descripcion)->toContain('PAGO ALQUILER ING MANUEL');
});

it('no permite pagar dos veces el mismo mes', function () {
    $obra = Obra::factory()->create();
    $alquiler = $obra->alquileres()->create([
        'inquilino' => 'OFICINA',
        'monto_mensual' => 200,
        'forma_pago' => 'adelantado',
        'fecha_inicio' => '2026-01-01',
    ]);
    $admin = adminAlq();

    $datos = [
        'periodo' => '2026-05',
        'fecha_pago' => now()->toDateString(),
        'monto' => 200,
    ];

    $this->actingAs($admin)
        ->post(route('obras.alquileres.pagar', [$obra, $alquiler]), $datos)
        ->assertRedirect();

    $this->actingAs($admin)
        ->post(route('obras.alquileres.pagar', [$obra, $alquiler]), $datos)
        ->assertStatus(422);

    expect(AlquilerPago::count())->toBe(1);
});

it('anular un pago elimina también el egreso vinculado', function () {
    $obra = Obra::factory()->create();
    $alquiler = $obra->alquileres()->create([
        'inquilino' => 'ING JONATHAN',
        'monto_mensual' => 300,
        'forma_pago' => 'adelantado',
        'fecha_inicio' => '2026-01-01',
    ]);
    $admin = adminAlq();

    $this->actingAs($admin)
        ->post(route('obras.alquileres.pagar', [$obra, $alquiler]), [
            'periodo' => '2026-03',
            'fecha_pago' => now()->toDateString(),
            'monto' => 300,
        ])
        ->assertRedirect();

    $pago = AlquilerPago::first();

    $this->actingAs($admin)
        ->delete(route('obras.alquileres.pagos.anular', [$obra, $alquiler, $pago]))
        ->assertRedirect();

    expect(AlquilerPago::count())->toBe(0);
    expect(CajaMovimiento::count())->toBe(0);          // soft-deleted
    expect(CajaMovimiento::withTrashed()->count())->toBe(1);
});

it('usuario sin acceso a la obra no puede crear alquileres', function () {
    $obra = Obra::factory()->create();
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Usuario->value);

    $this->actingAs($u)
        ->post(route('obras.alquileres.store', $obra), [
            'inquilino' => 'X',
            'monto_mensual' => 100,
            'forma_pago' => 'adelantado',
            'fecha_inicio' => '2026-01-01',
        ])
        ->assertForbidden();
});
