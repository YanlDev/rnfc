<?php

use App\Enums\RolGlobal;
use App\Models\Carpeta;
use App\Models\Obra;
use App\Models\User;
use App\Services\PlantillaCarpetasService;

function adminUsr(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

it('aplica plantilla creando grupo, subcarpetas y sub-subcarpetas', function () {
    $obra = Obra::factory()->create();
    $svc = app(PlantillaCarpetasService::class);

    $creadas = $svc->aplicar($obra, [
        'EXPEDIENTE_TECNICO' => ['Planos', 'Memoria_Descriptiva'],
    ]);

    // 1 raíz + 2 subs + 6 hijos automáticos de Planos = 9
    expect($creadas)->toBe(9);

    expect(Carpeta::where('obra_id', $obra->id)->where('ruta', 'EXPEDIENTE_TECNICO')->exists())->toBeTrue();
    expect(Carpeta::where('obra_id', $obra->id)->where('ruta', 'EXPEDIENTE_TECNICO/Planos')->exists())->toBeTrue();
    expect(Carpeta::where('obra_id', $obra->id)->where('ruta', 'EXPEDIENTE_TECNICO/Planos/Arquitectura')->exists())->toBeTrue();
    expect(Carpeta::where('obra_id', $obra->id)->where('ruta', 'EXPEDIENTE_TECNICO/Memoria_Descriptiva')->exists())->toBeTrue();
});

it('aplicar plantilla es idempotente (no duplica carpetas existentes)', function () {
    $obra = Obra::factory()->create();
    $svc = app(PlantillaCarpetasService::class);

    $primera = $svc->aplicar($obra, [
        'GESTION_CONTRACTUAL' => ['Contrato', 'TDR'],
    ]);
    $segunda = $svc->aplicar($obra, [
        'GESTION_CONTRACTUAL' => ['Contrato', 'TDR', 'Garantias'],
    ]);

    expect($primera)->toBe(3);
    expect($segunda)->toBe(1);
    expect(Carpeta::where('obra_id', $obra->id)->count())->toBe(4);
});

it('admin puede aplicar plantilla vía HTTP', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(adminUsr())
        ->post(route('obras.carpetas.plantilla', $obra), [
            'seleccion' => [
                'CORRESPONDENCIA' => ['Oficios', 'Actas'],
            ],
        ])
        ->assertRedirect();

    expect(Carpeta::where('obra_id', $obra->id)->where('ruta', 'CORRESPONDENCIA/Oficios')->exists())->toBeTrue();
});

it('admin puede crear y eliminar carpetas manualmente', function () {
    $obra = Obra::factory()->create();
    $admin = adminUsr();

    $this->actingAs($admin)
        ->post(route('obras.carpetas.store', $obra), [
            'nombre' => 'Mis_Notas',
        ])
        ->assertRedirect();

    $carpeta = Carpeta::where('obra_id', $obra->id)->where('ruta', 'Mis_Notas')->first();
    expect($carpeta)->not->toBeNull();

    $this->actingAs($admin)
        ->delete(route('obras.carpetas.destroy', [$obra, $carpeta]))
        ->assertRedirect();

    expect(Carpeta::find($carpeta->id))->toBeNull();
});

it('eliminar carpeta padre elimina sus subcarpetas en cascada', function () {
    $obra = Obra::factory()->create();
    $svc = app(PlantillaCarpetasService::class);
    $svc->aplicar($obra, ['SSOMA' => ['ATS', 'IPERC']]);

    $raiz = Carpeta::where('obra_id', $obra->id)->where('parent_id', null)->first();
    expect(Carpeta::where('obra_id', $obra->id)->count())->toBe(3);

    $this->actingAs(adminUsr())
        ->delete(route('obras.carpetas.destroy', [$obra, $raiz]))
        ->assertRedirect();

    expect(Carpeta::where('obra_id', $obra->id)->count())->toBe(0);
});

it('eliminar la obra elimina todas sus carpetas', function () {
    $obra = Obra::factory()->create();
    $svc = app(PlantillaCarpetasService::class);
    $svc->aplicar($obra, ['VALORIZACIONES' => ['Valorizaciones_Mensuales']]);

    expect(Carpeta::where('obra_id', $obra->id)->count())->toBe(2);

    $obra->delete();

    expect(Carpeta::where('obra_id', $obra->id)->count())->toBe(0);
});

it('un practicante (no admin/supervisor) no puede aplicar plantilla', function () {
    $obra = Obra::factory()->create();
    $invitado = User::factory()->create(['email_verified_at' => now()]);
    $invitado->assignRole(RolGlobal::Invitado->value);
    $obra->usuarios()->attach($invitado->id, [
        'rol_obra' => 'practicante',
        'asignado_at' => now(),
    ]);

    $this->actingAs($invitado)
        ->post(route('obras.carpetas.plantilla', $obra), [
            'seleccion' => ['GESTION_CONTRACTUAL' => ['Contrato']],
        ])
        ->assertForbidden();
});

it('admin puede renombrar una carpeta', function () {
    $obra = Obra::factory()->create();
    $svc = app(PlantillaCarpetasService::class);
    $svc->aplicar($obra, ['SSOMA' => ['ATS']]);

    $raiz = Carpeta::where('obra_id', $obra->id)->where('parent_id', null)->first();

    $this->actingAs(adminUsr())
        ->patch(route('obras.carpetas.update', [$obra, $raiz]), [
            'nombre' => 'Seguridad y Salud',
        ])
        ->assertRedirect();

    $raizActualizada = $raiz->fresh();
    expect($raizActualizada->nombre)->toBe('Seguridad y Salud');
    expect($raizActualizada->ruta)->toBe('Seguridad_y_Salud');

    // El descendiente ATS debe haber actualizado su ruta también.
    $ats = Carpeta::where('obra_id', $obra->id)->where('nombre', 'ATS')->first();
    expect($ats->ruta)->toBe('Seguridad_y_Salud/ATS');
});

it('renombrar a una ruta que ya existe falla con error de validación', function () {
    $obra = Obra::factory()->create();
    $a = Carpeta::create([
        'obra_id' => $obra->id, 'parent_id' => null,
        'nombre' => 'Alfa', 'ruta' => 'Alfa', 'orden' => 0,
    ]);
    Carpeta::create([
        'obra_id' => $obra->id, 'parent_id' => null,
        'nombre' => 'Beta', 'ruta' => 'Beta', 'orden' => 1,
    ]);

    $this->actingAs(adminUsr())
        ->patch(route('obras.carpetas.update', [$obra, $a]), [
            'nombre' => 'Beta',
        ])
        ->assertSessionHasErrors('nombre');

    expect($a->fresh()->nombre)->toBe('Alfa');
});
