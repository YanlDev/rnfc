<?php

namespace App\Models;

use App\Enums\EstadoObra;
use Database\Factories\ObraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obra extends Model
{
    /** @use HasFactory<ObraFactory> */
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'ubicacion',
        'latitud',
        'longitud',
        'entidad_contratante',
        'monto_contractual',
        'fecha_inicio',
        'fecha_fin_prevista',
        'fecha_fin_real',
        'estado',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin_prevista' => 'date',
            'fecha_fin_real' => 'date',
            'monto_contractual' => 'decimal:2',
            'latitud' => 'float',
            'longitud' => 'float',
            'estado' => EstadoObra::class,
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'obra_user')
            ->withPivot(['rol_obra', 'asignado_at'])
            ->withTimestamps();
    }

    public function invitaciones(): HasMany
    {
        return $this->hasMany(Invitacion::class);
    }

    public function carpetas(): HasMany
    {
        return $this->hasMany(Carpeta::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function asientosCuaderno(): HasMany
    {
        return $this->hasMany(AsientoCuaderno::class);
    }

    public function eventosCalendario(): HasMany
    {
        return $this->hasMany(EventoCalendario::class);
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class);
    }
}
