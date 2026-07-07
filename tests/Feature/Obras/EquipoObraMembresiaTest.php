<?php

use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

it('cambiar rol a un miembro del equipo funciona', function () {
    $obra = Obra::factory()->create();
    $miembro = usuarioEnObra($obra, RolObra::Asistente);

    $this->actingAs(admin())
        ->patch(route('obras.equipo.cambiar-rol', [$obra, $miembro]), [
            'rol_obra' => RolObra::Residente->value,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $pivot = $obra->usuarios()->where('users.id', $miembro->id)->first()->pivot;
    expect($pivot->rol_obra)->toBe(RolObra::Residente->value);
});

it('devuelve 404 al cambiar rol de alguien que no es miembro de la obra', function () {
    $obra = Obra::factory()->create();
    $ajeno = User::factory()->create();

    $this->actingAs(admin())
        ->patch(route('obras.equipo.cambiar-rol', [$obra, $ajeno]), [
            'rol_obra' => RolObra::Residente->value,
        ])
        ->assertNotFound();
});

it('devuelve 404 al remover a alguien que no es miembro de la obra', function () {
    $obra = Obra::factory()->create();
    $ajeno = User::factory()->create();

    $this->actingAs(admin())
        ->delete(route('obras.equipo.remover', [$obra, $ajeno]))
        ->assertNotFound();
});
