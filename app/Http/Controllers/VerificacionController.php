<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificacionController extends Controller
{
    /**
     * Formulario público para ingresar el código a verificar.
     */
    public function form(): View
    {
        return view('certificados.verificar-form');
    }

    /**
     * Envío del formulario: redirige a /verificar/{codigo} con el código sanitizado.
     */
    public function buscar(Request $request): RedirectResponse|View
    {
        $codigo = strtoupper(trim((string) $request->input('codigo', '')));

        if (preg_match('/^RNFC-[0-9]{4}-[A-Z0-9]{6}$/', $codigo)) {
            return redirect()->route('verificar', ['codigo' => $codigo]);
        }

        return view('certificados.verificar-form', [
            'error' => 'El código ingresado no tiene el formato válido. Ejemplo: RNFC-2026-ABC123.',
            'codigo_ingresado' => $codigo,
        ]);
    }

    /**
     * Página pública de verificación de un certificado por su código.
     */
    public function mostrar(string $codigo): View
    {
        $certificado = Certificado::with('obra:id,nombre,codigo,entidad_contratante')
            ->where('codigo', $codigo)
            ->first();

        return view('certificados.verificar', [
            'certificado' => $certificado,
            'codigo' => $codigo,
        ]);
    }
}
