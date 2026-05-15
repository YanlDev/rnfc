<?php

namespace App\Http\Requests;

use App\Enums\TipoCertificado;
use App\Models\Certificado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCertificadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Certificado::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::enum(TipoCertificado::class)],
            'beneficiario_nombre' => ['required', 'string', 'max:255'],
            'beneficiario_documento' => ['nullable', 'string', 'max:30'],
            'beneficiario_profesion' => ['nullable', 'string', 'max:255'],
            'obra_id' => ['nullable', 'integer', Rule::exists('obras', 'id')],
            'obra_nombre_libre' => ['nullable', 'string', 'max:255'],
            'obra_entidad_libre' => ['nullable', 'string', 'max:255'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'lugar_emision' => ['nullable', 'string', 'max:255'],
            'emisor_nombre' => ['nullable', 'string', 'max:255'],
            'emisor_cargo' => ['nullable', 'string', 'max:255'],
            'emisor_cip' => ['nullable', 'string', 'max:30'],
            'fecha_emision' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'beneficiario_nombre.required' => 'El nombre del beneficiario es obligatorio.',
            'tipo.required' => 'Selecciona el tipo de certificado.',
            'fecha_emision.required' => 'La fecha de emisión es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
