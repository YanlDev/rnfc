<?php

namespace App\Policies;

use App\Models\Carpeta;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

class CarpetaPolicy
{
    public function viewAny(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'documento.ver');
    }

    public function view(User $user, Carpeta $carpeta): bool
    {
        return $this->viewAny($user, $carpeta->obra);
    }

    public function create(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'carpeta.gestionar');
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
