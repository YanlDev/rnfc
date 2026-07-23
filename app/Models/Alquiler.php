<?php

namespace App\Models;

use App\Enums\FormaPagoAlquiler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alquiler extends Model
{
    use SoftDeletes;

    protected $table = 'alquileres';

    protected $fillable = [
        'obra_id',
        'inquilino',
        'arrendador',
        'monto_mensual',
        'forma_pago',
        'fecha_inicio',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'forma_pago' => FormaPagoAlquiler::class,
            'monto_mensual' => 'decimal:2',
            'fecha_inicio' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(AlquilerPago::class);
    }
}
