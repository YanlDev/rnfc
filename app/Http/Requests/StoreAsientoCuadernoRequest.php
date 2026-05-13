<?php

namespace App\Http\Requests;

use App\Enums\TipoAutorCuaderno;
use App\Models\Obra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsientoCuadernoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $obra = $this->route('obra');
        $tipo = TipoAutorCuaderno::tryFrom((string) $this->input('tipo_autor'));

        return $obra instanceof Obra
            && $tipo !== null
            && ($this->user()?->can('createEn', [\App\Models\AsientoCuaderno::class, $obra, $tipo]) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo_autor' => ['required', Rule::enum(TipoAutorCuaderno::class)],
            'fecha' => ['required', 'date'],
            'contenido' => ['required', 'string', 'max:10000'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:51200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo_autor.required' => 'Indica a qué cuaderno pertenece el asiento.',
            'fecha.required' => 'La fecha del asiento es obligatoria.',
            'contenido.required' => 'El contenido del asiento es obligatorio.',
            'archivo.mimes' => 'El adjunto debe ser PDF o imagen (JPG/PNG).',
            'archivo.max' => 'El adjunto no debe pesar más de 50 MB.',
        ];
    }
}
