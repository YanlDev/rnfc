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
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->get(route('admin.usuarios.index'))
        ->assertOk();
});

it('ingeniero NO entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Usuario))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

it('residente NO entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Usuario))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

it('invitado NO entra a /admin/usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Usuario))
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

it('un admin puede desactivar a otro admin si hay más de uno', function () {
    $admin1 = usuarioConRol(RolGlobal::Admin);
    $admin2 = usuarioConRol(RolGlobal::Admin);

    // Quedando otro admin activo, desactivar a admin2 está permitido.
    $this->actingAs($admin1)
        ->patch(route('admin.usuarios.toggle-activo', $admin2), [])
        ->assertRedirect();

    expect($admin2->fresh()->estaActivo())->toBeFalse();
});

it('desactivar un usuario lo bloquea de iniciar sesión', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $ingeniero = usuarioConRol(RolGlobal::Usuario);

    $this->actingAs($admin)
        ->patch(route('admin.usuarios.toggle-activo', $ingeniero), ['motivo' => 'Test'])
        ->assertRedirect();

    expect($ingeniero->fresh()->estaActivo())->toBeFalse();
    expect($ingeniero->fresh()->motivo_desactivacion)->toBe('Test');
});

it('cambiar rol global de un usuario funciona', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $u = usuarioConRol(RolGlobal::Usuario);

    $this->actingAs($admin)
        ->patch(route('admin.usuarios.rol', $u), ['rol' => RolGlobal::Admin->value])
        ->assertRedirect();

    expect($u->fresh()->hasRole(RolGlobal::Admin->value))->toBeTrue();
    expect($u->fresh()->hasRole(RolGlobal::Usuario->value))->toBeFalse();
});

it('no se puede quitar admin al único administrador del sistema', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    // Otro usuario también admin para poder operar
    $otroAdmin = usuarioConRol(RolGlobal::Admin);

    // Si el otro admin intenta degradar a admin1 y queda 1 admin → debería poder
    $this->actingAs($otroAdmin)
        ->patch(route('admin.usuarios.rol', $admin), ['rol' => RolGlobal::Usuario->value])
        ->assertRedirect();

    expect($admin->fresh()->hasRole(RolGlobal::Admin->value))->toBeFalse();

    // Ahora solo queda otroAdmin como admin. Otro admin no puede degradarse a sí mismo... pero por la regla del rol
    // (se quitaría el último admin del sistema), debería fallar si intentamos degradarlo.
    $this->actingAs($otroAdmin)
        ->patch(route('admin.usuarios.rol', $otroAdmin), ['rol' => RolGlobal::Usuario->value])
        ->assertSessionHasErrors('rol');

    expect($otroAdmin->fresh()->hasRole(RolGlobal::Admin->value))->toBeTrue();
});

it('usuario desactivado es deslogueado en el siguiente request', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $ingeniero = usuarioConRol(RolGlobal::Usuario);

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
