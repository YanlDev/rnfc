<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Enums\TipoAutorCuaderno;
use App\Models\Obra;
use App\Support\PermisosObra;

/*
|--------------------------------------------------------------------------
| Matriz de permisos por rol de obra (configurable por el Admin)
|--------------------------------------------------------------------------
*/

// =============== Acceso a la pantalla ===============

it('el admin accede a la matriz de permisos', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('admin.permisos.index'))
        ->assertOk();
});

it('un usuario normal NO accede a la matriz de permisos', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Usuario))
        ->get(route('admin.permisos.index'))
        ->assertForbidden();
});

it('un usuario normal NO puede actualizar la matriz', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Usuario))
        ->put(route('admin.permisos.update'), ['matriz' => []])
        ->assertForbidden();
});

// =============== Persistencia ===============

it('el admin actualiza la matriz y se refleja', function () {
    $matriz = PermisosObra::estadoCompleto();
    $matriz['asistente']['cuaderno.escribir'] = true;

    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->put(route('admin.permisos.update'), ['matriz' => $matriz])
        ->assertRedirect();

    expect(in_array('cuaderno.escribir', PermisosObra::matriz()['asistente'], true))->toBeTrue();
});

// =============== Escalamiento dinámico ===============

it('conceder un permiso en la matriz habilita la acción', function () {
    $obra = Obra::factory()->create();
    $asistente = usuarioEnObra($obra, RolObra::Asistente);

    // Por defecto el asistente NO puede escribir el cuaderno.
    $this->actingAs($asistente)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Intento',
        ])
        ->assertForbidden();

    // El admin le concede el permiso.
    $matriz = PermisosObra::estadoCompleto();
    $matriz['asistente']['cuaderno.escribir'] = true;
    PermisosObra::sincronizar($matriz);

    // Ahora sí puede.
    $this->actingAs($asistente)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Ahora autorizado',
        ])
        ->assertRedirect();
});

it('revocar un permiso en la matriz deshabilita la acción', function () {
    $obra = Obra::factory()->create();
    $residente = usuarioEnObra($obra, RolObra::Residente);

    // Por defecto el residente sí puede escribir.
    $this->actingAs($residente)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Permitido',
        ])
        ->assertRedirect();

    // El admin le revoca el permiso.
    $matriz = PermisosObra::estadoCompleto();
    $matriz['residente']['cuaderno.escribir'] = false;
    PermisosObra::sincronizar($matriz);

    // Ya no puede.
    $this->actingAs($residente)
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Residente->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Ya no',
        ])
        ->assertForbidden();
});

// =============== El admin de plataforma siempre puede ===============

it('el admin de plataforma puede aunque la matriz esté vacía', function () {
    PermisosObra::sincronizar([]); // matriz vacía: ningún rol de obra puede nada

    $obra = Obra::factory()->create();

    // Un residente queda sin permisos.
    $residente = usuarioEnObra($obra, RolObra::Residente);
    $this->actingAs($residente)
        ->get(route('obras.cuaderno.index', $obra))
        ->assertForbidden();

    // El admin de plataforma sigue pudiendo todo.
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => '2026-05-10',
            'contenido' => 'Admin siempre puede',
        ])
        ->assertRedirect();
});

// =============== Defaults sembrados ===============

it('los defaults de la matriz son los esperados', function () {
    $matriz = PermisosObra::matriz();

    expect($matriz['administrador'])->toContain('obra.editar', 'equipo.gestionar', 'cuaderno.eliminar')
        ->and($matriz['residente'])->toContain('cuaderno.escribir', 'documento.eliminar')
        ->and($matriz['residente'])->not->toContain('obra.editar', 'equipo.gestionar')
        ->and($matriz['especialista'])->toContain('documento.ver', 'documento.subir')
        ->and($matriz['especialista'])->not->toContain('cuaderno.escribir', 'documento.eliminar');
});
