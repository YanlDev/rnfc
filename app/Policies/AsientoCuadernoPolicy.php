<?php

namespace App\Policies;

use App\Enums\TipoAutorCuaderno;
use App\Models\AsientoCuaderno;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

class AsientoCuadernoPolicy
{
    public function viewAny(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'cuaderno.ver');
    }

    public function view(User $user, AsientoCuaderno $asiento): bool
    {
        return $this->viewAny($user, $asiento->obra);
    }

    public function createEn(User $user, Obra $obra, TipoAutorCuaderno $tipo): bool
    {
        return PermisosObra::puede($user, $obra, 'cuaderno.escribir');
    }

    public function delete(User $user, AsientoCuaderno $asiento): bool
    {
        return PermisosObra::puede($user, $asiento->obra, 'cuaderno.eliminar');
    }
}
