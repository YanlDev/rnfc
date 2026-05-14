<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnviarMensajeContactoRequest;
use App\Mail\MensajeContacto;
use App\Models\GaleriaHome;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class LandingController extends Controller
{
    public function home(): View
    {
        $galeria = GaleriaHome::orderBy('orden')->orderBy('id')->get();

        return view('landing.index', compact('galeria'));
    }

    public function enviarMensaje(EnviarMensajeContactoRequest $request): RedirectResponse
    {
        // Honeypot anti-spam: si el campo "website" viene lleno, descarta sin error.
        if (! empty($request->input('website'))) {
            return back()->with('success', 'Mensaje recibido. Te responderemos a la brevedad.');
        }

        $data = $request->validated();
        $destino = config('mail.from.address', 'contacto@rnfcconsultoria.com');

        Mail::to($destino)->queue(new MensajeContacto(
            nombre: $data['nombre'],
            correo: $data['correo'],
            telefono: $data['telefono'] ?? null,
            asunto: $data['asunto'] ?? 'Mensaje desde la web',
            mensaje: $data['mensaje'],
        ));

        return back()
            ->with('success', '¡Gracias por escribirnos! Nos pondremos en contacto contigo a la brevedad.')
            ->withFragment('contacto');
    }
}
