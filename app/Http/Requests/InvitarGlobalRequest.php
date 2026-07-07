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
            'rol_global' => ['required', Rule::in(RolGlobal::rolesAdministrativos())],
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
            $email = mb_strtolower(trim((string) $this->input('email')));

            $usuario = User::withTrashed()->where('email', $email)->first();

            if ($usuario === null) {
                return;
            }

            // El email de un usuario en papelera sigue ocupado (unique): la
            // invitación se enviaría pero el registro fallaría siempre.
            if ($usuario->trashed()) {
                $validator->errors()->add('email', 'Este correo pertenece a un usuario que está en la papelera. Restáuralo desde la tabla de usuarios en lugar de invitarlo.');

                return;
            }

            $validator->errors()->add('email', 'Este correo ya tiene una cuenta registrada. Usa "Cambiar rol" desde la tabla de usuarios.');
        });
    }
}
