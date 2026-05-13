<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;

/*
|--------------------------------------------------------------------------
| Matriz de acceso: módulo OBRAS
|--------------------------------------------------------------------------
| Verifica qué rol puede ver/crear/editar/eliminar obras según el modelo:
|   - Admin / Gerente General → todo
|   - Ingeniero / Residente / Invitado (global) → solo ven obras donde están en pivot
|   - Administrador de obra (pivot) → puede editar su obra
|   - Resto en pivot → solo pueden ver su obra
*/

// =============== VER LISTADO ===============

it('admin ve el listado de obras', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('obras.index'))
        ->assertOk();
});

it('gerente general ve el listado de obras', function () {
    $this->actingAs(usuarioConRol(RolGlobal::GerenteGeneral))
        ->get(route('obras.index'))
        ->assertOk();
});

it('ingeniero puede entrar al listado (verá solo las suyas)', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->get(route('obras.index'))
        ->assertOk();
});

it('invitado puede entrar al listado (verá solo aquellas a las que fue invitado)', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Invitado))
        ->get(route('obras.index'))
        ->assertOk();
});

// =============== VER DETALLE ===============

it('admin puede ver cualquier obra', function () {
    $obra = Obra::factory()->create();
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('obras.show', $obra))
        ->assertOk();
});

it('ingeniero NO puede ver una obra ajena', function () {
    $obra = Obra::factory()->create();
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->get(route('obras.show', $obra))
        ->assertForbidden();
});

it('administrador de obra puede ver su obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->get(route('obras.show', $obra))
        ->assertOk();
});

it('invitado de obra puede ver la obra a la que fue invitado', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Invitado, RolGlobal::Invitado);

    $this->actingAs($user)
        ->get(route('obras.show', $obra))
        ->assertOk();
});

// =============== CREAR ===============

it('admin puede crear obras', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('obras.create'))
        ->assertOk();
});

it('gerente general puede crear obras', function () {
    $this->actingAs(usuarioConRol(RolGlobal::GerenteGeneral))
        ->get(route('obras.create'))
        ->assertOk();
});

it('ingeniero NO puede crear obras', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->get(route('obras.create'))
        ->assertForbidden();
});

it('administrador de obra NO puede crear obras nuevas (solo edita la suya)', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->get(route('obras.create'))
        ->assertForbidden();
});

// =============== EDITAR ===============

it('administrador de obra puede editar su obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->get(route('obras.edit', $obra))
        ->assertOk();
});

it('administrador de obra NO puede editar una obra ajena', function () {
    $miObra = Obra::factory()->create();
    $obraAjena = Obra::factory()->create();
    $user = usuarioEnObra($miObra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->get(route('obras.edit', $obraAjena))
        ->assertForbidden();
});

it('residente de obra NO puede editar la obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::ResidenteObra);

    $this->actingAs($user)
        ->get(route('obras.edit', $obra))
        ->assertForbidden();
});

it('invitado de obra NO puede editar la obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::Invitado, RolGlobal::Invitado);

    $this->actingAs($user)
        ->get(route('obras.edit', $obra))
        ->assertForbidden();
});

// =============== ELIMINAR ===============

it('admin puede eliminar obras', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->delete(route('obras.destroy', $obra))
        ->assertRedirect();
});

it('administrador de obra NO puede eliminar su propia obra', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->delete(route('obras.destroy', $obra))
        ->assertForbidden();
});

it('ingeniero NO puede eliminar obras', function () {
    $obra = Obra::factory()->create();
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->delete(route('obras.destroy', $obra))
        ->assertForbidden();
});
