<?php

use App\Enums\RolGlobal;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
});

it('limita a 10 por minuto los envíos de invitación global', function () {
    $adminUser = admin();

    foreach (range(1, 10) as $i) {
        $this->actingAs($adminUser)
            ->post(route('admin.invitar'), [
                'email' => "persona{$i}@externo.com",
                'rol_global' => RolGlobal::Admin->value,
            ])
            ->assertRedirect();
    }

    $this->actingAs($adminUser)
        ->post(route('admin.invitar'), [
            'email' => 'persona11@externo.com',
            'rol_global' => RolGlobal::Admin->value,
        ])
        ->assertStatus(429);
});
