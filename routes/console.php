<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Avisa diariamente a los miembros de obra los vencimientos que caen
// hoy y dentro de 3/7 días.
Schedule::command('rnfc:notificar-vencimientos')->dailyAt('07:00');
