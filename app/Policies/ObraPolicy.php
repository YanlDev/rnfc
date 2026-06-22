<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

class ObraPolicy
{
    /**
     * Cualquier usuario autenticado puede entrar al listado; se filtra por
     * las obras a las que pertenece.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::values());
    }

    /**
     * Ver una obra: el Admin de plataforma ve todas; el resto, sólo las suyas.
     */
    public function view(User $user, Obra $obra): bool
    {
        return PermisosObra::esMiembro($user, $obra);
    }

    /**
     * Crear obras es una acción de plataforma (sólo Admin).
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    public function update(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'obra.editar');
    }

    public function gestionarEquipo(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'equipo.gestionar');
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
