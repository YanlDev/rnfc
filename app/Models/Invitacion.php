<?php

namespace App\Models;

use App\Enums\RolGlobal;
use App\Enums\RolObra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitacion extends Model
{
    protected $table = 'invitaciones';

    protected $fillable = [
        'obra_id',
        'email',
        'rol_obra',
        'rol_global',
        'token',
        'invitado_por',
        'expira_at',
        'aceptada_at',
        'cancelada_at',
    ];

    protected function casts(): array
    {
        return [
            'rol_obra' => RolObra::class,
            'rol_global' => RolGlobal::class,
            'expira_at' => 'datetime',
            'aceptada_at' => 'datetime',
            'cancelada_at' => 'datetime',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function invitador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitado_por');
    }

    public function esGlobal(): bool
    {
        return $this->rol_global !== null;
    }

    public function estaActiva(): bool
    {
        return $this->aceptada_at === null
            && $this->cancelada_at === null
            && $this->expira_at->isFuture();
    }

    public function estado(): string
    {
        if ($this->aceptada_at) {
            return 'aceptada';
        }
        if ($this->cancelada_at) {
            return 'cancelada';
        }
        if ($this->expira_at->isPast()) {
            return 'expirada';
        }

        return 'pendiente';
    }

    public static function generarToken(): string
    {
        return Str::random(64);
    }
}
