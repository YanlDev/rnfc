<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnviarMensajeContactoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => ['required', 'email', 'max:160'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'asunto' => ['nullable', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'min:10', 'max:2000'],
            'website' => ['nullable', 'string', 'max:50'], // honeypot
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.required' => 'Ingresa tu nombre.',
            'correo.required' => 'Ingresa tu correo electrónico.',
            'correo.email' => 'El correo electrónico no es válido.',
            'mensaje.required' => 'Escribe tu mensaje.',
            'mensaje.min' => 'El mensaje es demasiado corto.',
        ];
    }
}
