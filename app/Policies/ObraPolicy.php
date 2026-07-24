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
     * Crear obras: el Admin de plataforma siempre, y los usuarios habilitados
     * individualmente (administradoras que crean y manejan sus propias obras).
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos())
            || $user->puede_crear_obras;
    }

    public function update(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'obra.editar');
    }

    public function gestionarEquipo(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'equipo.gestionar');
    }

    /**
     * Configurar la matriz de permisos del equipo de ESTA obra: el Admin de
     * obra (o el Admin de plataforma). No afecta a otras obras ni a la global.
     */
    public function gestionarPermisos(User $user, Obra $obra): bool
    {
        return PermisosObra::esAdministradorDeObra($user, $obra);
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
