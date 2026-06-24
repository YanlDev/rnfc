<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadSession extends Model
{
    use HasUlids;

    protected $fillable = [
        'token',
        'obra_id',
        'carpeta_id',
        'documento_id',
        'nombre_original',
        'tamano_total',
        'total_chunks',
        'chunks_recibidos',
        'estado',
        'error',
        'documento_resultante_id',
        'subido_por',
    ];

    protected $casts = [
        'tamano_total' => 'integer',
        'total_chunks' => 'integer',
        'chunks_recibidos' => 'integer',
    ];

    /**
     * El binding de ruta usa el ULID público, no el id autoincremental.
     */
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /**
     * Columnas a las que HasUlids asigna un ULID automáticamente.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['token'];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class);
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class);
    }

    public function esVersion(): bool
    {
        return $this->documento_id !== null;
    }

    /**
     * Directorio temporal donde se acumulan los trozos en disco local.
     */
    public function directorioTemporal(): string
    {
        return storage_path('app/uploads/'.$this->token);
    }
}
