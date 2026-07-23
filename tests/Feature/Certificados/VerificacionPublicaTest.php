<?php

use App\Models\Certificado;

it('enmascara el documento del beneficiario dejando sólo los extremos', function () {
    $cert = Certificado::factory()->make(['beneficiario_documento' => '12345678']);

    expect($cert->beneficiario_documento_enmascarado)->toBe('12••••78');
});

it('enmascara por completo documentos muy cortos', function () {
    $cert = Certificado::factory()->make(['beneficiario_documento' => '1234']);

    expect($cert->beneficiario_documento_enmascarado)->toBe('••••');
});

it('devuelve null cuando no hay documento', function () {
    $cert = Certificado::factory()->make(['beneficiario_documento' => null]);

    expect($cert->beneficiario_documento_enmascarado)->toBeNull();
});

it('la página pública de verificación no expone el documento completo', function () {
    $cert = Certificado::factory()->create(['beneficiario_documento' => '87654321']);

    $this->get(route('verificar', $cert->codigo))
        ->assertOk()
        ->assertSee('87••••21')
        ->assertDontSee('87654321');
});

it('un certificado válido muestra "Certificado válido"', function () {
    $cert = Certificado::factory()->create();

    $this->get(route('verificar', $cert->codigo))
        ->assertOk()
        ->assertSee('Certificado válido');
});

it('un certificado eliminado se muestra como anulado, no como inexistente', function () {
    $cert = Certificado::factory()->create();
    $cert->delete();

    $this->get(route('verificar', $cert->codigo))
        ->assertOk()
        ->assertSee('Certificado revocado')
        ->assertDontSee('Certificado no encontrado');
});

it('un certificado con datos alterados NO se muestra como válido', function () {
    $cert = Certificado::factory()->create();

    // Alteración directa en BD (sin pasar por el modelo): el hash ya no coincide.
    Illuminate\Support\Facades\DB::table('certificados')
        ->where('id', $cert->id)
        ->update(['beneficiario_nombre' => 'Nombre Falsificado']);

    $this->get(route('verificar', $cert->codigo))
        ->assertOk()
        ->assertSee('No se pudo verificar este certificado')
        ->assertDontSee('Certificado válido')
        ->assertDontSee('Nombre Falsificado');
});
