<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carpeta extends Model
{
    protected $table = 'carpetas';

    protected $fillable = [
        'obra_id',
        'parent_id',
        'nombre',
        'ruta',
        'orden',
        'creado_por',
    ];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('orden')->orderBy('nombre');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /**
     * Convierte un nombre a slug seguro para filesystem (mantiene mayúsculas
     * y subrayados; sólo limpia tildes y caracteres no válidos).
     */
    public static function slugify(string $nombre): string
    {
        $sinTildes = \Transliterator::create('Any-Latin; Latin-ASCII')?->transliterate($nombre)
            ?? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
        $limpio = (string) preg_replace('/[^A-Za-z0-9_\-]+/', '_', $sinTildes ?: $nombre);

        return trim($limpio, '_');
    }
}
