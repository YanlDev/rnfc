<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Enums\TipoAutorCuaderno;
use App\Models\AsientoCuaderno;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function adminCuad(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

beforeEach(function () {
    Storage::fake('documentos');
});

it('admin puede crear un asiento del cuaderno del supervisor con PDF', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(adminCuad())
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Avance de partidas de concreto en columnas eje A-A',
            'archivo' => UploadedFile::fake()->create('asiento-osce.pdf', 200, 'application/pdf'),
        ])
        ->assertRedirect();

    $a = AsientoCuaderno::first();
    expect($a)->not->toBeNull();
    expect($a->numero)->toBe(1);
    expect($a->tipo_autor)->toBe(TipoAutorCuaderno::Supervisor);
    expect($a->tieneArchivo())->toBeTrue();
    Storage::disk('documentos')->assertExists($a->archivo_path);
});

it('numeración es independiente por tipo de cuaderno', function () {
    $obra = Obra::factory()->create();
    $admin = adminCuad();

    foreach ([TipoAutorCuaderno::Supervisor, TipoAutorCuaderno::Residente] as $tipo) {
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($admin)
                ->post(route('obras.cuaderno.store', $obra), [
                    'tipo_autor' => $tipo->value,
                    'fecha' => '2026-05-10',
                    'contenido' => 'Asiento de prueba',
                ])
                ->assertRedirect();
        }
    }

    expect(AsientoCuaderno::delCuaderno(TipoAutorCuaderno::Supervisor)->pluck('numero')->toArray())
        ->toBe([1, 2, 3]);
    expect(AsientoCuaderno::delCuaderno(TipoAutorCuaderno::Residente)->pluck('numero')->toArray())
        ->toBe([1, 2, 3]);
});

it('administrador de obra puede escribir en ambos cuadernos de su obra', function () {
    $obra = Obra::factory()->create();
    $adminObra = User::factory()->create(['email_verified_at' => now()]);
    $adminObra->assignRole(RolGlobal::Ingeniero->value);
    $obra->usuarios()->attach($adminObra->id, [
        'rol_obra' => RolObra::AdministradorObra->value,
        'asignado_at' => now(),
    ]);

    foreach ([TipoAutorCuaderno::Residente, TipoAutorCuaderno::Supervisor] as $tipo) {
        $this->actingAs($adminObra)
            ->post(route('obras.cuaderno.store', $obra), [
                'tipo_autor' => $tipo->value,
                'fecha' => '2026-05-10',
                'contenido' => "Asiento del administrador en {$tipo->value}",
            ])
            ->assertRedirect();
    }

    expect(AsientoCuaderno::count())->toBe(2);
});

it('residente de obra NO puede escribir en el cuaderno (solo admin de obra puede)', function () {
    $obra = Obra::factory()->create();
    $residente = User::factory()->create(['email_verified_at' => now()]);
    $residente->assignRole(RolGlobal::Ingeniero->value);
    $obra->usuarios()->attach($residente->id, [
        'rol_obra' => RolObra::ResidenteObra->value,
        'asignado_at' => now(),
    ]);

    foreach ([TipoAutorCuaderno::Residente, TipoAutorCuaderno::Supervisor] as $tipo) {
        $this->actingAs($residente)
            ->post(route('obras.cuaderno.store', $obra), [
                'tipo_autor' => $tipo->value,
                'fecha' => '2026-05-10',
                'contenido' => 'Intento',
            ])
            ->assertForbidden();
    }

    // Pero sí puede ver el cuaderno
    $this->actingAs($residente)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertOk();
});

it('un invitado de la obra NO puede ver ni escribir en el cuaderno', function () {
    $obra = Obra::factory()->create();
    $invitado = User::factory()->create(['email_verified_at' => now()]);
    $invitado->assignRole(RolGlobal::Invitado->value);
    $obra->usuarios()->attach($invitado->id, [
        'rol_obra' => RolObra::Invitado->value,
        'asignado_at' => now(),
    ]);

    $this->actingAs($invitado)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertForbidden();

    $this->actingAs($invitado)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-10',
            'contenido' => 'No autorizado',
        ])
        ->assertForbidden();
});

it('admin/gerente general y administrador de obra pueden eliminar; otros no', function () {
    $obra = Obra::factory()->create();
    $admin = adminCuad();
    $ingeniero = User::factory()->create(['email_verified_at' => now()]);
    $ingeniero->assignRole(RolGlobal::Ingeniero->value);
    $obra->usuarios()->attach($ingeniero->id, [
        'rol_obra' => RolObra::ResidenteObra->value,
        'asignado_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Asiento',
        ]);
    $asiento = AsientoCuaderno::first();

    // Residente de obra NO puede eliminar
    $this->actingAs($ingeniero)
        ->delete(route('obras.cuaderno.destroy', [$obra, $asiento]))
        ->assertForbidden();

    expect(AsientoCuaderno::find($asiento->id))->not->toBeNull();

    // Admin global SÍ puede
    $this->actingAs($admin)
        ->delete(route('obras.cuaderno.destroy', [$obra, $asiento]))
        ->assertRedirect();

    // Soft delete: el registro queda con deleted_at para trazabilidad legal.
    expect(AsientoCuaderno::withTrashed()->find($asiento->id)->trashed())->toBeTrue();
});

it('un asiento eliminado no afecta la numeración', function () {
    $obra = Obra::factory()->create();
    $admin = adminCuad();

    foreach ([1, 2, 3] as $i) {
        $this->actingAs($admin)
            ->post(route('obras.cuaderno.store', $obra), [
                'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
                'fecha' => '2026-05-10',
                'contenido' => "Asiento {$i}",
            ]);
    }
    $segundo = AsientoCuaderno::where('numero', 2)->first();

    $this->actingAs($admin)
        ->delete(route('obras.cuaderno.destroy', [$obra, $segundo]));

    // El siguiente asiento debe seguir siendo el N° 4, no reusar el 2.
    expect(
        AsientoCuaderno::siguienteNumero($obra->id, TipoAutorCuaderno::Supervisor),
    )->toBe(4);
});

it('descargar un PDF requiere ver la obra', function () {
    $obra = Obra::factory()->create();
    $admin = adminCuad();

    $this->actingAs($admin)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => '2026-05-10',
            'contenido' => 'A',
            'archivo' => UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
        ]);
    $a = AsientoCuaderno::first();

    $ajeno = User::factory()->create(['email_verified_at' => now()]);
    $ajeno->assignRole(RolGlobal::Invitado->value);

    $this->actingAs($ajeno)
        ->get(route('obras.cuaderno.descargar', [$obra, $a]))
        ->assertForbidden();
});
