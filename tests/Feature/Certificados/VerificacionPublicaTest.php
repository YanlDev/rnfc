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
