<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlquilerPago extends Model
{
    protected $table = 'alquiler_pagos';

    protected $fillable = [
        'alquiler_id',
        'periodo',
        'fecha_pago',
        'monto',
        'caja_movimiento_id',
    ];

    protected function casts(): array
    {
        return [
            'periodo' => 'date',
            'fecha_pago' => 'date',
            'monto' => 'decimal:2',
        ];
    }

    public function alquiler(): BelongsTo
    {
        return $this->belongsTo(Alquiler::class);
    }

    public function cajaMovimiento(): BelongsTo
    {
        return $this->belongsTo(CajaMovimiento::class);
    }
}
