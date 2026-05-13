<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\EventoCalendario;
use App\Models\Obra;
use App\Models\User;

class EventoCalendarioPolicy
{
    public function viewAny(User $user, Obra $obra): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        return $obra->usuarios()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, EventoCalendario $evento): bool
    {
        return $this->viewAny($user, $evento->obra);
    }

    /**
     * Crear eventos → admin global o administrador de la obra.
     */
    public function create(User $user, Obra $obra): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesAdministrativos())) {
            return true;
        }

        return $obra->usuarios()
            ->where('users.id', $user->id)
            ->wherePivot('rol_obra', RolObra::AdministradorObra->value)
            ->exists();
    }

    public function update(User $user, EventoCalendario $evento): bool
    {
        return $this->create($user, $evento->obra);
    }

    public function delete(User $user, EventoCalendario $evento): bool
    {
        return $this->update($user, $evento);
    }
}
