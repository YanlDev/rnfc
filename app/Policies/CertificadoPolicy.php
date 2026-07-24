<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Models\Certificado;
use App\Models\User;
use App\Support\PermisosObra;

class CertificadoPolicy
{
    /**
     * Ven y emiten certificados: Admin y Gerente (visión global) y las
     * administradoras de obra (para su personal). La eliminación queda
     * reservada al Admin de plataforma.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesVisionGlobal())
            || PermisosObra::esAdministradorDeAlgunaObra($user);
    }

    public function view(User $user, Certificado $certificado): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    public function delete(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    public function restore(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }

    public function forceDelete(User $user, Certificado $certificado): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesAdministrativos());
    }
}
