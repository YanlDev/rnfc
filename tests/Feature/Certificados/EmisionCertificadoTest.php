<?php

use App\Enums\RolGlobal;
use App\Enums\TipoCertificado;
use App\Models\Certificado;
use App\Models\Obra;

/*
|--------------------------------------------------------------------------
| Emisión de certificados: validación de obra según el tipo
|--------------------------------------------------------------------------
*/

function datosCertificado(array $extra = []): array
{
    return array_merge([
        'tipo' => TipoCertificado::Residente->value,
        'beneficiario_nombre' => 'Juan Pérez Quispe',
        'fecha_emision' => '2026-07-01',
    ], $extra);
}

it('un tipo que requiere obra NO se emite sin obra', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('certificados.store'), datosCertificado())
        ->assertSessionHasErrors('obra_id');

    expect(Certificado::count())->toBe(0);
});

it('un tipo que requiere obra se emite con obra registrada', function () {
    $obra = Obra::factory()->create();

    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('certificados.store'), datosCertificado(['obra_id' => $obra->id]))
        ->assertSessionHasNoErrors();

    expect(Certificado::count())->toBe(1)
        ->and(Certificado::first()->obra_id)->toBe($obra->id);
});

it('un tipo que requiere obra se emite con obra escrita manualmente', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('certificados.store'), datosCertificado([
            'obra_nombre_libre' => 'Mejoramiento de la carretera PU-135',
        ]))
        ->assertSessionHasNoErrors();

    expect(Certificado::first()->obra_nombre_libre)->toBe('Mejoramiento de la carretera PU-135');
});

it('capacitación se emite sin obra', function () {
    $this->actingAs(usuarioConRol(RolGlobal::Admin))
        ->post(route('certificados.store'), datosCertificado([
            'tipo' => TipoCertificado::Capacitacion->value,
        ]))
        ->assertSessionHasNoErrors();

    expect(Certificado::count())->toBe(1);
});

it('el certificado emitido recibe código, hash y emisor del servidor', function () {
    $admin = usuarioConRol(RolGlobal::Admin);
    $obra = Obra::factory()->create();

    $this->actingAs($admin)
        ->post(route('certificados.store'), datosCertificado([
            'obra_id' => $obra->id,
            // Intento de falsificar campos server-only vía mass assignment:
            'codigo' => 'RNFC-2020-FALSO1',
            'hash_verificacion' => str_repeat('a', 64),
            'emitido_por' => 999999,
        ]))
        ->assertSessionHasNoErrors();

    $cert = Certificado::first();

    expect($cert->codigo)->not->toBe('RNFC-2020-FALSO1')
        ->and($cert->codigo)->toMatch('/^RNFC-[0-9]{4}-[A-Z0-9]{6}$/')
        ->and($cert->hash_verificacion)->toBe($cert->calcularHash())
        ->and($cert->emitido_por)->toBe($admin->id);
});

it('eliminar un certificado notifica a los administradores', function () {
    Illuminate\Support\Facades\Notification::fake();

    $admin = usuarioConRol(RolGlobal::Admin);
    $obra = Obra::factory()->create();
    $cert = Certificado::factory()->create(['obra_id' => $obra->id]);

    $this->actingAs($admin)
        ->delete(route('certificados.destroy', $cert))
        ->assertRedirect(route('certificados.index'));

    expect($cert->fresh()->trashed())->toBeTrue();

    Illuminate\Support\Facades\Notification::assertSentTo(
        $admin,
        App\Notifications\CertificadoEliminado::class,
    );
});
