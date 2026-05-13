<?php

namespace App\Models;

use App\Enums\TipoEvento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCalendario extends Model
{
    protected $table = 'eventos_calendario';

    protected $fillable = [
        'obra_id',
        'tipo',
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'todo_el_dia',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoEvento::class,
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'todo_el_dia' => 'boolean',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function estaVencido(): bool
    {
        $referencia = $this->fecha_fin ?? $this->fecha_inicio;

        return $referencia->isPast();
    }

    public function color(): string
    {
        return $this->tipo->color();
    }
}
