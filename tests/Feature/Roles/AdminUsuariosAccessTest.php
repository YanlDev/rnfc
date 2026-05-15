<?php

use App\Enums\RolGlobal;

/*
|--------------------------------------------------------------------------
| Matriz de acceso: ADMIN / USUARIOS
|--------------------------------------------------------------------------
| Reglas:
|   - Solo Admin y Gerente General acceden a /admin y /admin/usuarios
|   - Reglas de negocio:
|       · No puedes desactivarte a ti mismo
|       · No puedes desactivar al último admin activo
|       · No puedes quitar admin al único admin
*/

it('admin entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('admin.usuarios.index'))
        ->assertOk();
});

it('gerente general entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::GerenteGeneral))
        ->get(route('admin.usuarios.index'))
        ->assertOk();
});

it('ingeniero NO entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Ingeniero))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

it('residente NO entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Residente))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

it('invitado NO entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Invitado))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

// =============== REGLAS DE NEGOCIO ===============

it('admin no puede desactivarse a sí mismo', function () {
    $admin = usuarioConRol(RolGlobal::Admin);

    $this->actingAs($admin)
        ->patch(route('admin.usuarios.toggle-activo', $admin), [])
        ->assertSessionHasErrors('usuario');

    expect($admin->fresh()->estaActivo())->toBeTrue();
});

it('no se puede desactivar al último admin activo', function () {
    $admin1 = usuarioConRol(RolGlobal::Admin);
    $admin2 = usuarioConRol(RolGlobal::Admin);

    // admin1 logueado, intenta desactivar a admin2 → debería poder (quedan 1)
    $this->actingAs($admin1)
        ->patch(route('admin.usuarios.toggle-activo', $admin2), [])
        ->assertRedirect();

    expect($admin2->fresh()->estaActivo())->toBeFalse();

    // Crear un GG para tener otra cuenta admin (mismo grupo administrativo)
    $gerente = usuarioConRol(RolGlobal::GerenteGeneral);

    // Ahora hay 1 admin activo. Si admin1 intenta desactivarse a sí mismo, falla por regla auto.
    // Pero si gerente intenta desactivar a admin1 (único admin activo) → debería fallar
    $this->actingAs($gerente)
        ->patch(route('admin.usuarios.toggle-activo', $admin1), [])
        ->assertSessionHasErrors('usuario');

    expect($admin1->fresh()->estaActivo())->toBeTrue();
});

it('desactivar un usuario lo bloquea de iniciar sesión', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $ingeniero = usuarioConRol(RolGlobal::Ingeniero);

    $this->actingAs($admin)
        ->patch(route('admin.usuarios.toggle-activo', $ingeniero), ['motivo' => 'Test'])
        ->assertRedirect();

    expect($ingeniero->fresh()->estaActivo())->toBeFalse();
    expect($ingeniero->fresh()->motivo_desactivacion)->toBe('Test');
});

it('cambiar rol global de un usuario funciona', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $u = usuarioConRol(RolGlobal::Ingeniero);

    $this->actingAs($admin)
        ->patch(route('admin.usuarios.rol', $u), ['rol' => RolGlobal::Residente->value])
        ->assertRedirect();

    expect($u->fresh()->hasRole(RolGlobal::Residente->value))->toBeTrue();
    expect($u->fresh()->hasRole(RolGlobal::Ingeniero->value))->toBeFalse();
});

it('no se puede quitar admin al único administrador del sistema', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    // Otro usuario también admin para poder operar
    $otroAdmin = usuarioConRol(RolGlobal::Admin);

    // Si el otro admin intenta degradar a admin1 y queda 1 admin → debería poder
    $this->actingAs($otroAdmin)
        ->patch(route('admin.usuarios.rol', $admin), ['rol' => RolGlobal::Ingeniero->value])
        ->assertRedirect();

    expect($admin->fresh()->hasRole(RolGlobal::Admin->value))->toBeFalse();

    // Ahora solo queda otroAdmin como admin. Otro admin no puede degradarse a sí mismo... pero por la regla del rol
    // (se quitaría el último admin del sistema), debería fallar si intentamos degradarlo.
    $this->actingAs($otroAdmin)
        ->patch(route('admin.usuarios.rol', $otroAdmin), ['rol' => RolGlobal::Ingeniero->value])
        ->assertSessionHasErrors('rol');

    expect($otroAdmin->fresh()->hasRole(RolGlobal::Admin->value))->toBeTrue();
});

it('usuario desactivado es deslogueado en el siguiente request', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $ingeniero = usuarioConRol(RolGlobal::Ingeniero);

    // Simulamos que está logueado y luego lo desactivamos directamente en BD
    $this->actingAs($ingeniero);
    $ingeniero->forceFill([
        'desactivado_at' => now(),
        'desactivado_por' => $admin->id,
    ])->save();

    // Cualquier request siguiente debería redirigir al login
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});
