<?php

namespace App\Models;

use App\Enums\TipoAutorCuaderno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AsientoCuaderno extends Model
{
    use SoftDeletes;

    protected $table = 'asientos_cuaderno';

    protected $fillable = [
        'obra_id',
        'tipo_autor',
        'numero',
        'fecha',
        'contenido',
        'archivo_path',
        'archivo_nombre_original',
        'archivo_mime',
        'archivo_tamano',
        'autor_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo_autor' => TipoAutorCuaderno::class,
            'fecha' => 'date',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function scopeDelCuaderno(Builder $q, TipoAutorCuaderno $tipo): void
    {
        $q->where('tipo_autor', $tipo->value);
    }

    public function tieneArchivo(): bool
    {
        return ! empty($this->archivo_path);
    }

    public function esPdf(): bool
    {
        return $this->archivo_mime === 'application/pdf';
    }

    public function tamanoFormateado(): string
    {
        $bytes = (int) ($this->archivo_tamano ?? 0);
        if ($bytes === 0) {
            return '—';
        }
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 ** 2) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / (1024 ** 2), 1).' MB';
    }

    /**
     * Calcula el siguiente número de asiento para una obra + cuaderno.
     */
    public static function siguienteNumero(int $obraId, TipoAutorCuaderno $tipo): int
    {
        $ultimo = self::withTrashed()
            ->where('obra_id', $obraId)
            ->where('tipo_autor', $tipo->value)
            ->max('numero');

        return (int) ($ultimo ?? 0) + 1;
    }
}
