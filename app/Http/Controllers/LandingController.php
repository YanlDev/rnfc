<?php

namespace App\Http\Controllers;

use App\Models\GaleriaHome;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function home(): Response
    {
        $galeria = GaleriaHome::orderBy('orden')->orderBy('id')->get()
            ->map(fn (GaleriaHome $img) => [
                'id' => $img->id,
                'url' => $img->url,
                'titulo' => $img->titulo,
            ])
            ->all();

        return Inertia::render('landing', [
            'galeria' => $galeria,
        ]);
    }
}
