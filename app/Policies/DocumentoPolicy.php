<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\User;

class DocumentoPolicy
{
    /**
     * Ver un documento:
     *  - admin / gerente general → siempre
     *  - usuario en pivot → su obra (incluye invitado, lectura)
     */
    public function view(User $user, Documento $documento): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        return $documento->obra
            ->usuarios()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Subir documentos:
     *  - admin / gerente general → siempre
     *  - cualquier rol en pivot EXCEPTO invitado
     */
    public function create(User $user, Carpeta $carpeta): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesVisionGlobal())) {
            return true;
        }

        return $carpeta->obra
            ->usuarios()
            ->where('users.id', $user->id)
            ->where('rol_obra', '!=', RolObra::Invitado->value)
            ->exists();
    }

    public function update(User $user, Documento $documento): bool
    {
        return $this->create($user, $documento->carpeta);
    }

    /**
     * Eliminar documentos:
     *  - admin / gerente general → siempre
     *  - administrador de obra → en su obra
     */
    public function delete(User $user, Documento $documento): bool
    {
        if ($user->hasAnyRole(RolGlobal::rolesAdministrativos())) {
            return true;
        }

        return $documento->obra
            ->usuarios()
            ->where('users.id', $user->id)
            ->wherePivot('rol_obra', RolObra::AdministradorObra->value)
            ->exists();
    }
}
