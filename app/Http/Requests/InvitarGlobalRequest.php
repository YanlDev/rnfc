<?php

namespace App\Http\Requests;

use App\Enums\RolGlobal;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitarGlobalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'rol_global' => ['required', Rule::enum(RolGlobal::class)],
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
            'rol_global.required' => 'Selecciona el rol global que tendrá.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $email = strtolower($this->input('email'));

            $yaRegistrado = User::whereRaw('LOWER(email) = ?', [$email])->exists();

            if ($yaRegistrado) {
                $validator->errors()->add('email', 'Este correo ya tiene una cuenta registrada. Usa "Cambiar rol" desde la tabla de usuarios.');
            }
        });
    }
}
