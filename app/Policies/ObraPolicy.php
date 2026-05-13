<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Obra;
use App\Models\User;

class ObraPolicy
{
    /**
     * Cualquier usuario autenticado con rol global válido puede listar obras.
     * El listado de cada usuario será filtrado por las obras a las que pertenece.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::values());
    }

    /**
     * Para ver una obra específica:
     *   - admin, gerente general o supervisor → todas
     *   - los demás roles → sólo si están vinculados via pivot
     */
    public function view(User $user, Obra $obra): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        return $obra->usuarios()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesVisionGlobal());
    }

    /**
     * Editar: admin global, gerente general, supervisor (global o de la obra).
     */
    public function update(User $user, Obra $obra): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        return $obra->usuarios()
            ->where('users.id', $user->id)
            ->wherePivot('rol_obra', RolObra::AdministradorObra->value)
            ->exists();
    }

    public function delete(User $user, Obra $obra): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    public function restore(User $user, Obra $obra): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    public function forceDelete(User $user, Obra $obra): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }
}
