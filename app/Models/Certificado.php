<?php

namespace App\Models;

use App\Enums\TipoCertificado;
use Database\Factories\CertificadoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Certificado extends Model
{
    /** @use HasFactory<CertificadoFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables por el usuario al emitir un certificado.
     * Los campos generados por el servidor (codigo, hash_verificacion,
     * emitido_por, revocado_at) se asignan SIEMPRE de forma explícita en el
     * controlador para impedir su falsificación vía mass assignment.
     */
    protected $fillable = [
        'tipo',
        'beneficiario_nombre',
        'beneficiario_documento',
        'beneficiario_profesion',
        'obra_id',
        'obra_nombre_libre',
        'obra_entidad_libre',
        'cargo',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'lugar_emision',
        'emisor_nombre',
        'emisor_cargo',
        'emisor_cip',
        'fecha_emision',
        'motivo_revocacion',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCertificado::class,
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'fecha_emision' => 'date',
            'revocado_at' => 'datetime',
        ];
    }

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    /**
     * Nombre de obra efectivo: el de la obra relacionada si existe,
     * o el nombre libre escrito al emitir el certificado.
     */
    public function getObraNombreEfectivoAttribute(): ?string
    {
        return $this->obra?->nombre ?? $this->obra_nombre_libre;
    }

    /**
     * Entidad contratante efectiva (mismo criterio).
     */
    public function getObraEntidadEfectivaAttribute(): ?string
    {
        return $this->obra?->entidad_contratante ?? $this->obra_entidad_libre;
    }

    public function emisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitido_por');
    }

    /**
     * Documento del beneficiario enmascarado para superficies públicas
     * (página de verificación): deja visibles sólo los dos primeros y los
     * dos últimos caracteres. Ej: "12345678" -> "12••••78".
     */
    public function getBeneficiarioDocumentoEnmascaradoAttribute(): ?string
    {
        $doc = trim((string) $this->beneficiario_documento);

        if ($doc === '') {
            return null;
        }

        if (mb_strlen($doc) <= 4) {
            return str_repeat('•', mb_strlen($doc));
        }

        return mb_substr($doc, 0, 2)
            .str_repeat('•', mb_strlen($doc) - 4)
            .mb_substr($doc, -2);
    }

    public function estaVigente(): bool
    {
        return $this->revocado_at === null;
    }

    /**
     * Genera un código legible único: RNFC-YYYY-XXXXXX (alfanumérico mayúsculas, sin caracteres ambiguos).
     */
    public static function generarCodigo(?int $anio = null): string
    {
        $anio ??= (int) now()->format('Y');

        do {
            // Alfabeto sin caracteres ambiguos (sin 0, O, 1, I, L)
            $alfabeto = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
            $sufijo = '';
            for ($i = 0; $i < 6; $i++) {
                $sufijo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
            $codigo = "RNFC-{$anio}-{$sufijo}";
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }

    /**
     * Hash determinístico para verificación (no es firma criptográfica, sólo integridad).
     */
    public function calcularHash(): string
    {
        return hash('sha256', implode('|', [
            $this->codigo,
            $this->tipo?->value,
            $this->beneficiario_nombre,
            $this->beneficiario_documento ?? '',
            $this->fecha_emision?->format('Y-m-d') ?? '',
            config('app.key'),
        ]));
    }
}
