<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Invitacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => $this->nameRules(),
            'email' => [
                ...$this->emailRules(),
                function ($attribute, $value, $fail) {
                    if (! $this->tieneInvitacionActiva($value)) {
                        $fail('Este sistema es de acceso restringido. Solo puedes crear una cuenta si has recibido una invitación válida por correo. Contacta al administrador.');
                    }
                },
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $this->vincularInvitacionesPendientes($user);

            return $user;
        });
    }

    /**
     * Verifica que exista al menos una invitación activa para el email.
     */
    private function tieneInvitacionActiva(string $email): bool
    {
        return Invitacion::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->whereNull('aceptada_at')
            ->whereNull('cancelada_at')
            ->where('expira_at', '>', now())
            ->exists();
    }

    /**
     * Si la persona se registra con un correo que tiene invitaciones activas,
     * la vincula a esas obras y marca las invitaciones como aceptadas.
     */
    private function vincularInvitacionesPendientes(User $user): void
    {
        $tokenSesion = session('invitacion_token');

        $query = Invitacion::query()
            ->where(function ($q) use ($user) {
                $q->whereRaw('LOWER(email) = ?', [strtolower($user->email)]);
            })
            ->whereNull('aceptada_at')
            ->whereNull('cancelada_at')
            ->where('expira_at', '>', now());

        $invitaciones = $query->get();

        foreach ($invitaciones as $invitacion) {
            $yaVinculado = $invitacion->obra->usuarios()
                ->where('users.id', $user->id)
                ->exists();

            if (! $yaVinculado) {
                $invitacion->obra->usuarios()->attach($user->id, [
                    'rol_obra' => $invitacion->rol_obra->value,
                    'asignado_at' => now(),
                ]);
            }

            $invitacion->update(['aceptada_at' => now()]);
        }

        if ($tokenSesion) {
            session()->forget('invitacion_token');
        }
    }
}
