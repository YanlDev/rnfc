<?php

namespace App\Http\Requests;

use App\Enums\RolObra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        $obra = $this->route('obra');

        return $obra !== null && ($this->user()?->can('update', $obra) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'rol_obra' => ['required', Rule::enum(RolObra::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'rol_obra.required' => 'Selecciona el rol que tendrá en la obra.',
        ];
    }
}
