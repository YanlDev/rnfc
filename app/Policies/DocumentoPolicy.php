<?php

namespace App\Policies;

use App\Models\Carpeta;
use App\Models\Documento;
use App\Models\User;
use App\Support\PermisosObra;

class DocumentoPolicy
{
    public function view(User $user, Documento $documento): bool
    {
        return PermisosObra::puede($user, $documento->obra, 'documento.ver');
    }

    public function create(User $user, Carpeta $carpeta): bool
    {
        return PermisosObra::puede($user, $carpeta->obra, 'documento.subir');
    }

    public function update(User $user, Documento $documento): bool
    {
        return PermisosObra::puede($user, $documento->obra, 'documento.subir');
    }

    public function delete(User $user, Documento $documento): bool
    {
        return PermisosObra::puede($user, $documento->obra, 'documento.eliminar');
    }
}
