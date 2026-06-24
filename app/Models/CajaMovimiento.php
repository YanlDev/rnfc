<?php

namespace App\Models;

use App\Enums\CategoriaCaja;
use App\Enums\TipoMovimientoCaja;
use App\Support\Bytes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CajaMovimiento extends Model
{
    use SoftDeletes;

    protected $table = 'caja_movimientos';

    protected $fillable = [
        'obra_id',
        'tipo',
        'categoria',
        'monto',
        'descripcion',
        'fecha',
        'comprobante_path',
        'comprobante_nombre_original',
        'comprobante_mime',
        'comprobante_tamano',
        'registrado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMovimientoCaja::class,
            'categoria' => CategoriaCaja::class,
            'monto' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function esIngreso(): bool
    {
        return $this->tipo === TipoMovimientoCaja::Ingreso;
    }

    public function tieneComprobante(): bool
    {
        return ! empty($this->comprobante_path);
    }

    public function esPdf(): bool
    {
        return $this->comprobante_mime === 'application/pdf';
    }

    public function esImagen(): bool
    {
        return str_starts_with((string) $this->comprobante_mime, 'image/');
    }

    public function tamanoComprobante(): string
    {
        return Bytes::humano((int) ($this->comprobante_tamano ?? 0));
    }
}
