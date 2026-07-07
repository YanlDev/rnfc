<?php

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Mail\InvitacionGlobal;
use App\Models\Invitacion;
use App\Models\Obra;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

function invitacionGlobal(array $attrs = []): Invitacion
{
    return Invitacion::create(array_merge([
        'email' => 'pendiente@externo.com',
        'rol_global' => RolGlobal::Admin->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ], $attrs));
}

it('reenviar una invitación global pendiente renueva token y expiración', function () {
    $inv = invitacionGlobal(['expira_at' => now()->addDay()]);
    $tokenOriginal = $inv->token;

    $this->actingAs(admin())
        ->post(route('admin.invitaciones.reenviar', $inv))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $inv->refresh();
    expect($inv->token)->not->toBe($tokenOriginal);
    expect($inv->expira_at->isAfter(now()->addDays(6)))->toBeTrue();
    Mail::assertQueued(InvitacionGlobal::class);
});

it('no reenvía una invitación global ya aceptada', function () {
    $inv = invitacionGlobal(['aceptada_at' => now()->subDay()]);
    $tokenOriginal = $inv->token;

    $this->actingAs(admin())
        ->post(route('admin.invitaciones.reenviar', $inv))
        ->assertSessionHasErrors('invitacion');

    expect($inv->fresh()->token)->toBe($tokenOriginal);
    Mail::assertNothingQueued();
});

it('no reenvía ni revive una invitación global cancelada', function () {
    $inv = invitacionGlobal(['cancelada_at' => now()->subDay()]);

    $this->actingAs(admin())
        ->post(route('admin.invitaciones.reenviar', $inv))
        ->assertSessionHasErrors('invitacion');

    // Antes el reenvío limpiaba cancelada_at y resucitaba la invitación.
    expect($inv->fresh()->cancelada_at)->not->toBeNull();
    Mail::assertNothingQueued();
});

it('no cancela una invitación global ya aceptada', function () {
    $inv = invitacionGlobal(['aceptada_at' => now()->subDay()]);

    $this->actingAs(admin())
        ->delete(route('admin.invitaciones.cancelar', $inv))
        ->assertSessionHasErrors('invitacion');

    expect($inv->fresh()->cancelada_at)->toBeNull();
});

it('no reenvía una invitación de obra aceptada ni una cancelada', function (array $estado) {
    $obra = Obra::factory()->create();
    $inv = Invitacion::create(array_merge([
        'obra_id' => $obra->id,
        'email' => 'obra@externo.com',
        'rol_obra' => RolObra::Asistente->value,
        'token' => Invitacion::generarToken(),
        'expira_at' => now()->addDays(7),
    ], $estado));
    $tokenOriginal = $inv->token;

    $this->actingAs(admin())
        ->post(route('obras.invitaciones.reenviar', [$obra, $inv]))
        ->assertSessionHasErrors('invitacion');

    expect($inv->fresh()->token)->toBe($tokenOriginal);
    Mail::assertNothingQueued();
})->with([
    'aceptada' => [['aceptada_at' => '2026-07-01 10:00:00']],
    'cancelada' => [['cancelada_at' => '2026-07-01 10:00:00']],
]);
