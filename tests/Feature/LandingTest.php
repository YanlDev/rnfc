<?php

use App\Models\GaleriaHome;
use Inertia\Testing\AssertableInertia as Assert;

test('el home renderiza la página Inertia del landing', function () {
    GaleriaHome::create(['ruta' => 'home/uno.jpg', 'titulo' => 'Obra 1', 'orden' => 1]);
    GaleriaHome::create(['ruta' => 'home/dos.jpg', 'titulo' => 'Obra 2', 'orden' => 2]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('landing')
            ->has('galeria', 2)
        );
});
