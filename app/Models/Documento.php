<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'obra_id',
        'carpeta_id',
        'documento_padre_id',
        'version',
        'nombre_original',
        'nombre_archivo',
        'archivo_path',
        'mime',
        'tamano',
        'subido_por',
    ];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'documento_padre_id');
    }

    /**
     * Versiones históricas (sólo aplica al documento raíz/vigente).
     */
    public function versionesHistoricas(): HasMany
    {
        return $this->hasMany(self::class, 'documento_padre_id')
            ->orderByDesc('version');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /**
     * Scope para listar sólo las versiones vigentes (raíces).
     */
    public function scopeVigentes(Builder $q): void
    {
        $q->whereNull('documento_padre_id');
    }

    public function esImagen(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function esPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function tamanoFormateado(): string
    {
        $bytes = (int) $this->tamano;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 ** 2) {
            return number_format($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1024 ** 3) {
            return number_format($bytes / (1024 ** 2), 1).' MB';
        }

        return number_format($bytes / (1024 ** 3), 2).' GB';
    }
}
