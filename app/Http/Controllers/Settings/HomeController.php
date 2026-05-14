<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\GaleriaHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    private const DIR = 'galeria-home';

    public function edit(): Response
    {
        return Inertia::render('settings/home', [
            'imagenes' => GaleriaHome::orderBy('orden')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'titulo' => ['nullable', 'string', 'max:120'],
        ]);

        $ruta = $request->file('archivo')->store(self::DIR, 'public');

        $maxOrden = (int) GaleriaHome::max('orden');

        GaleriaHome::create([
            'ruta' => $ruta,
            'titulo' => $request->input('titulo'),
            'orden' => $maxOrden + 1,
        ]);

        return back()->with('success', 'Imagen agregada a la galería del home.');
    }

    public function destroy(GaleriaHome $imagen): RedirectResponse
    {
        if ($imagen->ruta && Storage::disk('public')->exists($imagen->ruta)) {
            Storage::disk('public')->delete($imagen->ruta);
        }
        $imagen->delete();

        return back()->with('success', 'Imagen eliminada.');
    }

    public function reordenar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'orden' => ['required', 'array'],
            'orden.*' => ['integer', 'exists:galeria_home,id'],
        ]);

        foreach ($data['orden'] as $i => $id) {
            GaleriaHome::where('id', $id)->update(['orden' => $i]);
        }

        return back();
    }
}
