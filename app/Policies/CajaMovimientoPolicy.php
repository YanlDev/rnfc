<?php

namespace App\Policies;

use App\Models\CajaMovimiento;
use App\Models\Obra;
use App\Models\User;
use App\Support\PermisosObra;

class CajaMovimientoPolicy
{
    /**
     * Ver la caja chica de una obra.
     */
    public function viewAny(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'caja.ver');
    }

    /**
     * Registrar un movimiento (ingreso/egreso) en una obra.
     */
    public function create(User $user, Obra $obra): bool
    {
        return PermisosObra::puede($user, $obra, 'caja.registrar');
    }

    /**
     * Eliminar un movimiento (gestión de caja).
     */
    public function delete(User $user, CajaMovimiento $movimiento): bool
    {
        return PermisosObra::puede($user, $movimiento->obra, 'caja.gestionar');
    }

    /**
     * Ver/descargar el comprobante de un movimiento.
     */
    public function view(User $user, CajaMovimiento $movimiento): bool
    {
        return PermisosObra::puede($user, $movimiento->obra, 'caja.ver');
    }
}
