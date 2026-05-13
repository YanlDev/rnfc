<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;

/*
|--------------------------------------------------------------------------
| Matriz de acceso: CERTIFICADOS
|--------------------------------------------------------------------------
| Reglas:
|   - Admin / Gerente General → emiten, ven, revocan
|   - Resto → no acceden al módulo
*/

it('admin ve el listado de certificados', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('certificados.index'))
        ->assertOk();
});

it('gerente general ve el listado de certificados', function () {
    $this->actingAs(usuarioConRol(RolGlobal::GerenteGeneral))
        ->get(route('certificados.index'))
        ->assertOk();
});

it('ingeniero NO ve certificados', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->get(route('certificados.index'))
        ->assertForbidden();
});

it('residente NO ve certificados', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Residente))
        ->get(route('certificados.index'))
        ->assertForbidden();
});

it('administrador de obra NO accede al módulo de certificados', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->get(route('certificados.index'))
        ->assertForbidden();
});

it('admin puede entrar al formulario de creación', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('certificados.create'))
        ->assertOk();
});

it('ingeniero NO puede entrar al formulario de creación', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->get(route('certificados.create'))
        ->assertForbidden();
});

it('verificación pública es accesible sin login', function () {
    $this->get(route('verificar.form'))->assertOk();
});
