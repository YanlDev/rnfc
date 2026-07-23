<?php

use App\Enums\RolGlobal;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Models\User;
use App\Services\DocumentoService;
use App\Support\TipoDocumento;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function adminSeg(): User
{
    $u = User::factory()->create(['email_verified_at' => now()]);
    $u->assignRole(RolGlobal::Admin->value);

    return $u;
}

function carpetaSeg(): Carpeta
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

it('rechaza subir un SVG (tipo ejecutable no permitido)', function () {
    $carpeta = carpetaSeg();

    $this->actingAs(adminSeg())
        ->post(
            route('obras.documentos.store', [$carpeta->obra_id, $carpeta]),
            ['archivo' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml')],
        )
        ->assertSessionHasErrors('archivo');

    expect(Documento::count())->toBe(0);
});

it('rechaza subir un HTML', function () {
    $carpeta = carpetaSeg();

    $this->actingAs(adminSeg())
        ->post(
            route('obras.documentos.store', [$carpeta->obra_id, $carpeta]),
            ['archivo' => UploadedFile::fake()->create('x.html', 4, 'text/html')],
        )
        ->assertSessionHasErrors('archivo');

    expect(Documento::count())->toBe(0);
});

it('acepta subir un PDF válido', function () {
    $carpeta = carpetaSeg();

    $this->actingAs(adminSeg())
        ->post(
            route('obras.documentos.store', [$carpeta->obra_id, $carpeta]),
            ['archivo' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf')],
        )
        ->assertRedirect();

    expect(Documento::count())->toBe(1);
});

it('el preview fuerza descarga y nosniff para tipos no seguros', function () {
    $carpeta = carpetaSeg();

    // Creamos directamente un documento con MIME no inline-seguro (simula un
    // archivo Office o binario ya almacenado).
    $doc = app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('planilla.xlsx', 50, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
    );

    $resp = $this->actingAs(adminSeg())
        ->get(route('obras.documentos.preview', [$carpeta->obra_id, $doc]));

    $resp->assertOk();
    $resp->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($resp->headers->get('Content-Disposition'))->toStartWith('attachment');
});

it('el preview de un PDF se sirve inline', function () {
    $carpeta = carpetaSeg();
    $doc = app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('plano.pdf', 50, 'application/pdf'),
    );

    $resp = $this->actingAs(adminSeg())
        ->get(route('obras.documentos.preview', [$carpeta->obra_id, $doc]));

    $resp->assertOk();
    $resp->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($resp->headers->get('Content-Disposition'))->toStartWith('inline');
});

it('la descarga siempre fuerza attachment', function () {
    $carpeta = carpetaSeg();
    $doc = app(DocumentoService::class)->subir(
        $carpeta,
        UploadedFile::fake()->create('plano.pdf', 50, 'application/pdf'),
    );

    $resp = $this->actingAs(adminSeg())
        ->get(route('obras.documentos.descargar', [$carpeta->obra_id, $doc]));

    $resp->assertOk();
    expect($resp->headers->get('Content-Disposition'))->toStartWith('attachment');
});

it('TipoDocumento reconoce tipos inline seguros y bloquea ejecutables', function () {
    expect(TipoDocumento::esInlineSeguro('application/pdf'))->toBeTrue();
    expect(TipoDocumento::esInlineSeguro('image/png'))->toBeTrue();
    expect(TipoDocumento::esInlineSeguro('image/svg+xml'))->toBeFalse();
    expect(TipoDocumento::esInlineSeguro('text/html'))->toBeFalse();

    // Ensamblado (chunked): CAD como octet-stream pasa por extensión.
    expect(TipoDocumento::permitidoEnsamblado('application/octet-stream', 'plano.dwg'))->toBeTrue();
    expect(TipoDocumento::permitidoEnsamblado('image/svg+xml', 'x.svg'))->toBeFalse();
    expect(TipoDocumento::permitidoEnsamblado('text/html', 'x.html'))->toBeFalse();
});

it('iniciar una subida en trozos rechaza extensiones no permitidas', function () {
    $carpeta = carpetaSeg();

    $this->actingAs(adminSeg())
        ->postJson(route('uploads.iniciar', $carpeta->obra_id), [
            'nombre' => 'malicioso.svg',
            'tamano' => 1024,
            'carpeta_id' => $carpeta->id,
        ])
        ->assertStatus(422);
});
