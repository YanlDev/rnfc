<?php

use App\Enums\RolGlobal;
use App\Enums\TipoAutorCuaderno;
use App\Enums\TipoCertificado;
use App\Models\Certificado;
use App\Models\Obra;
use App\Services\DocumentoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Rol global GERENTE (visor global)
|--------------------------------------------------------------------------
| El Gerente ve TODAS las obras y su contenido, y emite certificados, pero no
| modifica nada dentro de las obras ni administra la plataforma. Estos tests
| fijan ese contrato y previenen escalamiento de privilegios.
*/

// =============== VISIÓN GLOBAL (LECTURA) ===============

it('gerente ve el listado de obras', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->get(route('obras.index'))
        ->assertOk();
});

it('gerente ve una obra a la que NO pertenece (sin estar en el pivot)', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->get(route('obras.show', $obra))
        ->assertOk();
});

it('gerente puede previsualizar y descargar documentos de cualquier obra', function () {
    Storage::fake('documentos');
    $obra = Obra::factory()->create();
    $carpeta = $obra->carpetas()->create([
        'nombre' => 'Planos', 'ruta' => 'Planos', 'orden' => 0,
    ]);
    $doc = app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('plano.pdf', 100, 'application/pdf'),
    );

    $gerente = usuarioConRol(RolGlobal::Gerente);

    $this->actingAs($gerente)
        ->get(route('obras.documentos.preview', [$obra->id, $doc]))
        ->assertOk();
    $this->actingAs($gerente)
        ->get(route('obras.documentos.descargar', [$obra->id, $doc]))
        ->assertOk();
});

it('gerente puede ver la caja de cualquier obra', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->get(route('obras.caja.index', $obra))
        ->assertOk();
});

it('gerente ve y puede emitir certificados', function () {
    $gerente = usuarioConRol(RolGlobal::Gerente);

    $this->actingAs($gerente)->get(route('certificados.index'))->assertOk();

    $this->actingAs($gerente)
        ->post(route('certificados.store'), [
            'tipo' => TipoCertificado::cases()[0]->value,
            'beneficiario_nombre' => 'Juan Pérez',
            'obra_nombre_libre' => 'Obra de prueba',
            'fecha_emision' => now()->format('Y-m-d'),
        ])
        ->assertRedirect();

    expect(Certificado::count())->toBe(1);
});

// =============== NO PUEDE ESCRIBIR (NO-ESCALAMIENTO) ===============

it('gerente NO puede subir documentos', function () {
    Storage::fake('documentos');
    $obra = Obra::factory()->create();
    $carpeta = $obra->carpetas()->create([
        'nombre' => 'Planos', 'ruta' => 'Planos', 'orden' => 0,
    ]);

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->post(route('obras.documentos.store', [$obra->id, $carpeta]), [
            'archivo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
        ])
        ->assertForbidden();
});

it('gerente NO puede escribir en el cuaderno', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->post(route('obras.cuaderno.store', $obra), [
            'tipo_autor' => TipoAutorCuaderno::Supervisor->value,
            'fecha' => now()->format('Y-m-d'),
            'contenido' => 'intento de escritura',
        ])
        ->assertForbidden();
});

it('gerente NO puede registrar movimientos de caja', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->post(route('obras.caja.store', $obra), [
            'tipo' => 'ingreso',
            'monto' => 100,
            'descripcion' => 'x',
            'fecha' => now()->format('Y-m-d'),
        ])
        ->assertForbidden();
});

it('gerente NO puede eliminar una obra', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->delete(route('obras.destroy', $obra))
        ->assertForbidden();

    expect(Obra::whereKey($obra->id)->exists())->toBeTrue();
});

it('gerente NO puede editar una obra', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->put(route('obras.update', $obra), [
            'codigo' => $obra->codigo,
            'nombre' => 'hackeado',
            'estado' => $obra->estado->value,
        ])
        ->assertForbidden();
});

it('gerente NO puede revocar un certificado', function () {
    $cert = Certificado::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->post(route('certificados.revocar', $cert))
        ->assertForbidden();

    expect($cert->fresh()->estaVigente())->toBeTrue();
});

// =============== NO ADMINISTRA LA PLATAFORMA ===============

it('gerente NO accede al panel de administración de usuarios', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->get(route('admin.usuarios.index'))
        ->assertForbidden();
});

it('gerente NO accede a la matriz de permisos', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->get(route('admin.permisos.index'))
        ->assertForbidden();
});

it('gerente NO puede invitar usuarios globales', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Gerente))
        ->post(route('admin.invitar'), [
            'email' => 'nuevo@x.com',
            'rol_global' => 'admin',
        ])
        ->assertForbidden();
});

// =============== INVITACIÓN COMO GERENTE ===============

it('un admin puede invitar a alguien como gerente', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('admin.invitar'), [
            'email' => 'futuro.gerente@x.com',
            'rol_global' => RolGlobal::Gerente->value,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('invitaciones', [
        'email' => 'futuro.gerente@x.com',
        'rol_global' => RolGlobal::Gerente->value,
    ]);
});
