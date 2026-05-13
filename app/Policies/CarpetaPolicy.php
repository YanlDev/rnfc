<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Carpeta;
use App\Models\Obra;
use App\Models\User;

class CarpetaPolicy
{
    public function viewAny(User $user, Obra $obra): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        return $obra->usuarios()->where('users.id', $user->id)->exists();
    }

    public function view(User $user, Carpeta $carpeta): bool
    {
        return $this->viewAny($user, $carpeta->obra);
    }

    /**
     * Crear / renombrar carpetas → solo administrador de obra (o admin global).
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

    public function update(User $user, Carpeta $carpeta): bool
    {
        return $this->create($user, $carpeta->obra);
    }

    public function delete(User $user, Carpeta $carpeta): bool
    {
        return $this->create($user, $carpeta->obra);
    }
}
