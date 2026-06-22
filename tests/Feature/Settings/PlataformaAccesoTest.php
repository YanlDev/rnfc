<?php

use App\Enums\RolGlobal;

test('un usuario normal no puede ver la configuración de marca', function () {
    $usuario = usuarioConRol(RolGlobal::Usuario);

    $this->actingAs($usuario)
        ->get(route('branding.edit'))
        ->assertForbidden();
});

test('un usuario normal no puede modificar la galería del home', function () {
    $usuario = usuarioConRol(RolGlobal::Usuario);

    $this->actingAs($usuario)
        ->get(route('home.edit'))
        ->assertForbidden();
});

test('un administrador sí puede ver la configuración de marca', function () {
    $this->actingAs(admin())
        ->get(route('branding.edit'))
        ->assertOk();
});

test('un administrador sí puede ver la galería del home', function () {
    $this->actingAs(admin())
        ->get(route('home.edit'))
        ->assertOk();
});
