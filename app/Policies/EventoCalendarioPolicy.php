<?php

namespace App\Policies;

use App\Models\EventoCalendario;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

class EventoCalendarioPolicy
{
    public function viewAny(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'calendario.ver');
    }

    public function view(User $user, EventoCalendario $evento): bool
    {
        return $this->viewAny($user, $evento->obra);
    }

    public function create(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'calendario.gestionar');
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
