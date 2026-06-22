<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Enums\TipoAutorCuaderno;
use App\Models\Obra;

/*
|--------------------------------------------------------------------------
| Matriz de acceso: CUADERNO DE OBRA
|--------------------------------------------------------------------------
| Reglas:
|   - Admin / Gerente General → ver y escribir en cualquier obra
|   - Administrador de obra → ver y escribir en SU obra
|   - Resto en pivot (residente, especialistas, apoyo) → solo VER
|   - Invitado en pivot → NO ve cuaderno
|   - Usuario sin asignación a la obra → NO ve
*/

// =============== VER ===============

it('admin ve el cuaderno de cualquier obra', function () {
    $obra = Obra::factory()->create();
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('obras.cuaderno.index', $obra))
        ->assertOk();
});

it('administrador de obra ve el cuaderno de su obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Administrador);

    $this->actingAs($user)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertOk();
});

it('residente de obra ve el cuaderno de su obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Residente);

    $this->actingAs($user)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertOk();
});

it('especialista de obra ve el cuaderno', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Especialista);

    $this->actingAs($user)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertOk();
});

it('asistente de obra ve el cuaderno (por defecto)', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Asistente);

    $this->actingAs($user)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertOk();
});

it('usuario sin asignación NO ve el cuaderno de una obra ajena', function () {
    $obra = Obra::factory()->create();
    $this->actingAs(usuarioConRol(RolGlobal::Usuario))
        ->get(route('obras.cuaderno.index', $obra))
        ->assertForbidden();
});

// =============== ESCRIBIR (CREAR ASIENTO) ===============

it('admin puede crear asiento en cualquier obra', function () {
    $obra = Obra::factory()->create();
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => '2026-05-13',
            'contenido' => 'Asiento de prueba',
        ])
        ->assertRedirect();
});

it('administrador de obra puede crear asiento en su obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Administrador);

    $this->actingAs($user)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-13',
            'contenido' => 'Asiento del admin de obra',
        ])
        ->assertRedirect();
});

it('residente de obra puede crear asiento (por defecto)', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Residente);

    $this->actingAs($user)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-13',
            'contenido' => 'Asiento del residente',
        ])
        ->assertRedirect();
});

it('especialista de obra NO puede crear asiento', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Especialista);

    $this->actingAs($user)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => '2026-05-13',
            'contenido' => 'Intento',
        ])
        ->assertForbidden();
});

it('invitado de obra NO puede crear asiento', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Asistente, RolGlobal::Usuario);

    $this->actingAs($user)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-13',
            'contenido' => 'Intento',
        ])
        ->assertForbidden();
});
