<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BrandingController extends Controller
{
    public function __construct(private readonly BrandingService $branding) {}

    public function edit(): Response
    {
        return Inertia::render('settings/branding', [
            'urls' => $this->branding->urls(),
            'slots' => BrandingService::slots(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slot' => ['required', 'string', 'in:firma,iso1,iso2,iso3'],
            'archivo' => ['required', 'image', 'mimes:png', 'max:2048'],
        ]);

        $this->branding->guardar($data['slot'], $request->file('archivo'));

        return back()->with('success', 'Imagen actualizada.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slot' => ['required', 'string', 'in:firma,iso1,iso2,iso3'],
        ]);

        $this->branding->eliminar($data['slot']);

        return back()->with('success', 'Imagen eliminada.');
    }
}
