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
        'imagen_path',
        'entidad_contratante',
        'monto_contractual',
        'fecha_inicio',
        'fecha_fin_prevista',
        'fecha_fin_real',
        'estado',
        'creado_por',
    ];

    /**
     * Genera el siguiente código secuencial OBR-YYYY-NNNN del año en curso.
     * El código no lo elige el usuario: se asigna siempre en el servidor.
     */
    public static function generarCodigo(): string
    {
        $anio = (int) now()->format('Y');
        $prefijo = "OBR-{$anio}-";

        $ultima = self::query()
            ->where('codigo', 'like', $prefijo.'%')
            ->orderByDesc('codigo')
            ->value('codigo');

        $siguiente = 1;
        if ($ultima && preg_match('/-(\d+)$/', $ultima, $m)) {
            $siguiente = ((int) $m[1]) + 1;
        }

        // Por si existen huecos o códigos manuales antiguos que colisionen.
        do {
            $codigo = $prefijo.str_pad((string) $siguiente++, 4, '0', STR_PAD_LEFT);
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }

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

    public function cajaMovimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class);
    }

    public function alquileres(): HasMany
    {
        return $this->hasMany(Alquiler::class);
    }

    public function certificados(): HasMany
    {
        return $this->hasMany(Certificado::class);
    }
}
