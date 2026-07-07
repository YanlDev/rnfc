<?php

use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\Obra;
use App\Services\CarpetaService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('documentos');
    Notification::fake();
});

function carpetaCon(Obra $obra, string $nombre, ?Carpeta $parent = null): Carpeta
{
    $slug = Carpeta::slugify($nombre);

    return Carpeta::create([
        'obra_id' => $obra->id,
        'parent_id' => $parent?->id,
        'nombre' => $nombre,
        'ruta' => $parent ? "{$parent->ruta}/{$slug}" : $slug,
        'orden' => 0,
    ]);
}

function documentoCon(Carpeta $carpeta, string $archivo): Documento
{
    $path = "obras/{$carpeta->obra_id}/{$carpeta->ruta}/{$archivo}";
    Storage::disk('documentos')->put($path, 'contenido');

    return Documento::create([
        'obra_id' => $carpeta->obra_id,
        'carpeta_id' => $carpeta->id,
        'version' => 1,
        'nombre_original' => $archivo,
        'nombre_archivo' => $archivo,
        'archivo_path' => $path,
        'mime' => 'application/pdf',
        'tamano' => 9,
    ]);
}

it('al eliminar una carpeta se borran los archivos físicos del subárbol', function () {
    $obra = Obra::factory()->create();
    $raiz = carpetaCon($obra, 'Planos');
    $sub = carpetaCon($obra, 'Estructuras', $raiz);

    $docRaiz = documentoCon($raiz, 'a.pdf');
    $docSub = documentoCon($sub, 'b.pdf');

    $this->actingAs(admin())
        ->delete(route('obras.carpetas.destroy', [$obra, $raiz]))
        ->assertRedirect();

    $this->assertDatabaseMissing('carpetas', ['id' => $raiz->id]);
    $this->assertDatabaseMissing('documentos', ['id' => $docRaiz->id]);
    Storage::disk('documentos')->assertMissing($docRaiz->archivo_path);
    Storage::disk('documentos')->assertMissing($docSub->archivo_path);
});

it('borra el archivo en su ruta física original aunque la carpeta haya sido renombrada', function () {
    $obra = Obra::factory()->create();
    $carpeta = carpetaCon($obra, 'Contratos');
    $doc = documentoCon($carpeta, 'contrato.pdf'); // queda en obras/{id}/Contratos/

    // Renombrar cambia la ruta lógica pero NO mueve el archivo físico.
    app(CarpetaService::class)->renombrar($carpeta, 'Contratos Firmados');
    expect($carpeta->fresh()->ruta)->not->toBe('Contratos');

    $this->actingAs(admin())
        ->delete(route('obras.carpetas.destroy', [$obra, $carpeta->fresh()]))
        ->assertRedirect();

    Storage::disk('documentos')->assertMissing($doc->archivo_path);
});

it('al eliminar una obra se borra su directorio completo de archivos y su portada', function () {
    $obra = Obra::factory()->create();
    $carpeta = carpetaCon($obra, 'Planos');
    $doc = documentoCon($carpeta, 'a.pdf');
    Storage::disk('documentos')->put("obras/{$obra->id}/_caja/recibo.jpg", 'x');

    // Portada: vive fuera de obras/{id}, en el directorio compartido.
    $portada = 'obras/imagenes/portada-test.jpg';
    Storage::disk('documentos')->put($portada, 'x');
    $obra->update(['imagen_path' => $portada]);

    $this->actingAs(admin())
        ->delete(route('obras.destroy', $obra))
        ->assertRedirect();

    $this->assertDatabaseMissing('obras', ['id' => $obra->id]);
    Storage::disk('documentos')->assertMissing($doc->archivo_path);
    Storage::disk('documentos')->assertMissing("obras/{$obra->id}/_caja/recibo.jpg");
    Storage::disk('documentos')->assertMissing($portada);
});
