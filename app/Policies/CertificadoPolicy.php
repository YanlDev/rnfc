<?php

namespace App\Policies;

use App\Enums\RolGlobal;
use App\Models\Certificado;
use App\Models\User;

class CertificadoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesVisionGlobal());
    }

    public function view(User $user, Certificado $certificado): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(RolGlobal::rolesVisionGlobal());
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
