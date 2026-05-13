<?php

use App\Enums\RolGlobal;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Models\User;
use App\Services\DocumentoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function adminDocs(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

function carpetaConObra(): Carpeta
{
    $obra = Obra::factory()->create();

    return Carpeta::create([
        'obra_id' => $obra->id,
        'parent_id' => null,
        'nombre' => 'Contratos',
        'ruta' => 'Contratos',
        'orden' => 0,
    ]);
}

beforeEach(function () {
    Storage::fake('documentos');
});

it('admin puede subir un archivo a una carpeta', function () {
    $carpeta = carpetaConObra();

    $this->actingAs(adminDocs())
        ->post(
            route('obras.documentos.store', [$carpeta->obra_id, $carpeta]),
            ['archivo' => UploadedFile::fake()->create('contrato.pdf', 1024, 'application/pdf')],
        )
        ->assertRedirect();

    $doc = Documento::where('carpeta_id', $carpeta->id)->first();
    expect($doc)->not->toBeNull();
    expect($doc->nombre_original)->toBe('contrato.pdf');
    expect($doc->version)->toBe(1);
    expect($doc->documento_padre_id)->toBeNull();
    Storage::disk('documentos')->assertExists($doc->archivo_path);
});

it('subir nueva versión crea snapshot histórico y actualiza la raíz', function () {
    $carpeta = carpetaConObra();
    $service = app(DocumentoService::class);

    $v1 = $service->subir(
        $carpeta,
        UploadedFile::fake()->create('plano.pdf', 100, 'application/pdf'),
    );
    $rutaV1 = $v1->archivo_path;

    $service->subirNuevaVersion(
        $v1,
        UploadedFile::fake()->create('plano.pdf', 200, 'application/pdf'),
    );

    $raiz = Documento::find($v1->id);
    expect($raiz->version)->toBe(2);
    expect($raiz->documento_padre_id)->toBeNull();
    expect($raiz->archivo_path)->not->toBe($rutaV1);

    $historica = Documento::where('documento_padre_id', $raiz->id)->first();
    expect($historica)->not->toBeNull();
    expect($historica->version)->toBe(1);
    expect($historica->archivo_path)->toBe($rutaV1);
});

it('eliminar el documento raíz borra sus versiones históricas y los archivos físicos', function () {
    $carpeta = carpetaConObra();
    $service = app(DocumentoService::class);

    $v1 = $service->subir(
        $carpeta,
        UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'),
    );
    $service->subirNuevaVersion(
        $v1,
        UploadedFile::fake()->create('a.pdf', 200, 'application/pdf'),
    );
    $rutaActual = $v1->fresh()->archivo_path;
    $rutaHistorica = Documento::where('documento_padre_id', $v1->id)->first()->archivo_path;

    $service->eliminar($v1->fresh());

    expect(Documento::where('obra_id', $carpeta->obra_id)->count())->toBe(0);
    Storage::disk('documentos')->assertMissing($rutaActual);
    Storage::disk('documentos')->assertMissing($rutaHistorica);
});

it('listar documentos de una carpeta sólo trae versiones vigentes', function () {
    $carpeta = carpetaConObra();
    $service = app(DocumentoService::class);

    $a = $service->subir($carpeta, UploadedFile::fake()->create('a.pdf', 100, 'application/pdf'));
    $service->subir($carpeta, UploadedFile::fake()->create('b.pdf', 100, 'application/pdf'));
    $service->subirNuevaVersion($a, UploadedFile::fake()->create('a.pdf', 200, 'application/pdf'));

    expect(Documento::vigentes()->where('carpeta_id', $carpeta->id)->count())->toBe(2);
    expect(Documento::where('carpeta_id', $carpeta->id)->count())->toBe(3); // 2 vigentes + 1 histórica
});

it('eliminar la carpeta elimina sus documentos en cascada', function () {
    $carpeta = carpetaConObra();
    app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('x.pdf', 100, 'application/pdf'),
    );

    expect(Documento::count())->toBe(1);
    $carpeta->delete();
    expect(Documento::count())->toBe(0);
});

it('descarga requiere autorización (invitado fuera de la obra no accede)', function () {
    $carpeta = carpetaConObra();
    $doc = app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('secreto.pdf', 100, 'application/pdf'),
    );

    $ajeno = User::factory()->create(['email_verified_at' => now()]);
    $ajeno->assignRole(RolGlobal::Invitado->value);

    $this->actingAs($ajeno)
        ->get(route('obras.documentos.descargar', [$carpeta->obra_id, $doc]))
        ->assertForbidden();
});

it('un invitado vinculado a la obra puede previsualizar pero no subir', function () {
    $carpeta = carpetaConObra();
    $doc = app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('plano.pdf', 100, 'application/pdf'),
    );

    $miembro = User::factory()->create(['email_verified_at' => now()]);
    $miembro->assignRole(RolGlobal::Invitado->value);
    $carpeta->obra->usuarios()->attach($miembro->id, [
        'rol_obra' => 'invitado',
        'asignado_at' => now(),
    ]);

    $this->actingAs($miembro)
        ->get(route('obras.documentos.preview', [$carpeta->obra_id, $doc]))
        ->assertOk();

    $this->actingAs($miembro)
        ->post(
            route('obras.documentos.store', [$carpeta->obra_id, $carpeta]),
            ['archivo' => UploadedFile::fake()->create('nuevo.pdf', 100, 'application/pdf')],
        )
        ->assertForbidden();
});
