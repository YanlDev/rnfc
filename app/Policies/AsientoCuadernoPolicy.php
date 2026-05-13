<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Enums\TipoAutorCuaderno;
use App\Models\AsientoCuaderno;
use App\Models\Obra;
use App\Models\User;

class AsientoCuadernoPolicy
{
    /**
     * Ver el cuaderno de una obra:
     *  - admin / gerente general → cualquier obra
     *  - usuario asignado en pivot → su obra, excepto si es Invitado (read-only de la obra, no del cuaderno)
     */
    public function viewAny(User $user, Obra $obra): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        $pivot = $obra->usuarios()
            ->where('users.id', $user->id)
            ->first()?->pivot;

        if (! $pivot) {
            return false;
        }

        return $pivot->rol_obra !== RolObra::Invitado->value;
    }

    public function view(User $user, AsientoCuaderno $asiento): bool
    {
        return $this->viewAny($user, $asiento->obra);
    }

    /**
     * Crear un asiento en el cuaderno:
     *  - admin / gerente general → siempre
     *  - administrador de obra → en su obra
     *  - el resto → no
     */
    public function createEn(User $user, Obra $obra, TipoAutorCuaderno $tipo): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesAdministrativos())) {
            return true;
        }

        return $obra->usuarios()
            ->where('users.id', $user->id)
            ->wherePivot('rol_obra', RolObra::AdministradorObra->value)
            ->exists();
    }

    public function delete(User $user, AsientoCuaderno $asiento): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesAdministrativos())) {
            return true;
        }

        return $asiento->obra->usuarios()
            ->where('users.id', $user->id)
            ->wherePivot('rol_obra', RolObra::AdministradorObra->value)
            ->exists();
    }
}
