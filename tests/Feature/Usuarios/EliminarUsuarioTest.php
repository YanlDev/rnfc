<?php

use App\Enums\RolObra;
use App\Models\Invitacion;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

it('al eliminar un usuario se cancelan sus invitaciones pendientes', function () {
    $obra = Obra::factory()->create();
    $usuario = User::factory()->create(['email' => 'saliente@rnfc.test']);

    $pendiente = Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'saliente@rnfc.test',
        'rol_obra' => RolObra::Asistente->value,
        'token' => Invitacion::hashToken(Invitacion::generarToken()),
        'expira_at' => now()->addDays(7),
    ]);

    $aceptada = Invitacion::create([
        'obra_id' => $obra->id,
        'email' => 'saliente@rnfc.test',
        'rol_obra' => RolObra::Asistente->value,
        'token' => Invitacion::hashToken(Invitacion::generarToken()),
        'expira_at' => now()->addDays(7),
        'aceptada_at' => now()->subDay(),
    ]);

    $this->actingAs(admin())
        ->delete(route('admin.usuarios.eliminar', $usuario))
        ->assertRedirect();

    expect($usuario->fresh()->trashed())->toBeTrue();
    // La pendiente queda cancelada; la aceptada (historial) no se toca.
    expect($pendiente->fresh()->cancelada_at)->not->toBeNull();
    expect($aceptada->fresh()->cancelada_at)->toBeNull();
});
