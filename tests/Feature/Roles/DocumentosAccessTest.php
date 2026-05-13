<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Matriz de acceso: DOCUMENTOS
|--------------------------------------------------------------------------
| Reglas:
|   - Admin / Gerente General → todo
|   - Cualquiera en pivot (excepto invitado) → puede SUBIR
|   - Solo Admin global / Administrador de obra → puede ELIMINAR
|   - Solo Administrador de obra (o admin global) → puede crear/renombrar CARPETAS
*/

beforeEach(function () {
    Storage::fake('documentos');
});

function crearCarpetaRaiz(Obra $obra): Carpeta
{
    return Carpeta::create([
        'obra_id' => $obra->id,
        'nombre' => 'Carpeta raíz',
        'ruta' => '/raiz',
        'parent_id' => null,
    ]);
}

function subirDocumento(Obra $obra, Carpeta $carpeta, $test): mixed
{
    return $test->post(route('obras.documentos.store', [$obra, $carpeta]), [
        'archivo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        'nombre_original' => 'doc.pdf',
    ]);
}

// =============== SUBIR DOCUMENTOS ===============

it('admin puede subir documentos a cualquier obra', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);

    subirDocumento($obra, $carpeta, $this->actingAs(usuarioConRol(RolGlobal::Admin)))
        ->assertRedirect();
});

it('administrador de obra puede subir documentos en su obra', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    subirDocumento($obra, $carpeta, $this->actingAs($user))->assertRedirect();
});

it('residente de obra puede subir documentos', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $user = usuarioEnObra($obra, RolObra::ResidenteObra);

    subirDocumento($obra, $carpeta, $this->actingAs($user))->assertRedirect();
});

it('especialista puede subir documentos', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $user = usuarioEnObra($obra, RolObra::EspecialistaCalidad);

    subirDocumento($obra, $carpeta, $this->actingAs($user))->assertRedirect();
});

it('invitado en obra NO puede subir documentos', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $user = usuarioEnObra($obra, RolObra::Invitado, RolGlobal::Invitado);

    subirDocumento($obra, $carpeta, $this->actingAs($user))->assertForbidden();
});

it('usuario sin asignación NO puede subir documentos', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);

    subirDocumento($obra, $carpeta, $this->actingAs(usuarioConRol(RolGlobal::Ingeniero)))
        ->assertForbidden();
});

// =============== ELIMINAR DOCUMENTOS ===============

it('admin puede eliminar cualquier documento', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $doc = Documento::create([
        'obra_id' => $obra->id,
        'carpeta_id' => $carpeta->id,
        'version' => 1,
        'nombre_original' => 'doc.pdf',
        'nombre_archivo' => 'doc.pdf',
        'archivo_path' => 'documentos/test.pdf',
        'mime' => 'application/pdf',
        'tamano' => 100,
    ]);

    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->delete(route('obras.documentos.destroy', [$obra, $doc]))
        ->assertRedirect();
});

it('administrador de obra puede eliminar documentos de su obra', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $doc = Documento::create([
        'obra_id' => $obra->id,
        'carpeta_id' => $carpeta->id,
        'version' => 1,
        'nombre_original' => 'doc.pdf',
        'nombre_archivo' => 'doc.pdf',
        'archivo_path' => 'documentos/test.pdf',
        'mime' => 'application/pdf',
        'tamano' => 100,
    ]);
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->delete(route('obras.documentos.destroy', [$obra, $doc]))
        ->assertRedirect();
});

it('residente de obra NO puede eliminar documentos', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $doc = Documento::create([
        'obra_id' => $obra->id,
        'carpeta_id' => $carpeta->id,
        'version' => 1,
        'nombre_original' => 'doc.pdf',
        'nombre_archivo' => 'doc.pdf',
        'archivo_path' => 'documentos/test.pdf',
        'mime' => 'application/pdf',
        'tamano' => 100,
    ]);
    $user = usuarioEnObra($obra, RolObra::ResidenteObra);

    $this->actingAs($user)
        ->delete(route('obras.documentos.destroy', [$obra, $doc]))
        ->assertForbidden();
});

it('especialista NO puede eliminar documentos', function () {
    $obra = Obra::factory()->create();
    $carpeta = crearCarpetaRaiz($obra);
    $doc = Documento::create([
        'obra_id' => $obra->id,
        'carpeta_id' => $carpeta->id,
        'version' => 1,
        'nombre_original' => 'doc.pdf',
        'nombre_archivo' => 'doc.pdf',
        'archivo_path' => 'documentos/test.pdf',
        'mime' => 'application/pdf',
        'tamano' => 100,
    ]);
    $user = usuarioEnObra($obra, RolObra::EspecialistaCalidad);

    $this->actingAs($user)
        ->delete(route('obras.documentos.destroy', [$obra, $doc]))
        ->assertForbidden();
});

// =============== CARPETAS ===============

it('administrador de obra puede crear carpetas', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::AdministradorObra);

    $this->actingAs($user)
        ->post(route('obras.carpetas.store', $obra), [
            'nombre' => 'Nueva carpeta',
            'parent_id' => null,
        ])
        ->assertRedirect();
});

it('residente NO puede crear carpetas', function () {
    $obra = Obra::factory()->create();
    $user = usuarioEnObra($obra, RolObra::ResidenteObra);

    $this->actingAs($user)
        ->post(route('obras.carpetas.store', $obra), [
            'nombre' => 'Nueva carpeta',
            'parent_id' => null,
        ])
        ->assertForbidden();
});
