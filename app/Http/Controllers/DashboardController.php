<?php

namespace App\Http\Controllers;

use App\Enums\EstadoObra;
use App\Models\Obra;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $totalObras = Obra::count();
        $enEjecucion = Obra::where('estado', EstadoObra::EnEjecucion->value)->count();
        $finalizadas = Obra::where('estado', EstadoObra::Finalizada->value)->count();
        $paralizadas = Obra::where('estado', EstadoObra::Paralizada->value)->count();

        return Inertia::render('dashboard', [
            'kpis' => [
                'totalObras' => $totalObras,
                'enEjecucion' => $enEjecucion,
                'finalizadas' => $finalizadas,
                'paralizadas' => $paralizadas,
            ],
        ]);
    }
}
