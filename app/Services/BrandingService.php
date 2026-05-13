<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    private const DISCO = 'public';

    private const DIR = 'branding';

    private const ARCHIVOS = [
        'firma' => 'firma.png',
        'iso1' => 'iso-1.png',
        'iso2' => 'iso-2.png',
        'iso3' => 'iso-3.png',
    ];

    /**
     * Devuelve URLs públicas de los archivos de marca (si existen).
     *
     * @return array{firma: ?string, iso1: ?string, iso2: ?string, iso3: ?string}
     */
    public function urls(): array
    {
        $out = [];
        foreach (self::ARCHIVOS as $key => $archivo) {
            $ruta = self::DIR.'/'.$archivo;
            $out[$key] = Storage::disk(self::DISCO)->exists($ruta)
                ? Storage::disk(self::DISCO)->url($ruta).'?v='.Storage::disk(self::DISCO)->lastModified($ruta)
                : null;
        }

        /** @var array{firma: ?string, iso1: ?string, iso2: ?string, iso3: ?string} */
        return $out;
    }

    /**
     * Devuelve los archivos como data URIs base64 (para incrustar en PDFs/Blade
     * donde DomPDF no puede resolver URLs absolutas confiablemente).
     *
     * @return array{firma: ?string, iso1: ?string, iso2: ?string, iso3: ?string}
     */
    public function dataUris(): array
    {
        $out = [];
        foreach (self::ARCHIVOS as $key => $archivo) {
            $ruta = self::DIR.'/'.$archivo;
            if (! Storage::disk(self::DISCO)->exists($ruta)) {
                $out[$key] = null;

                continue;
            }
            $bytes = Storage::disk(self::DISCO)->get($ruta);
            $out[$key] = 'data:image/png;base64,'.base64_encode((string) $bytes);
        }

        /** @var array{firma: ?string, iso1: ?string, iso2: ?string, iso3: ?string} */
        return $out;
    }

    public function guardar(string $key, UploadedFile $file): void
    {
        if (! isset(self::ARCHIVOS[$key])) {
            throw new \InvalidArgumentException("Slot de marca desconocido: {$key}");
        }
        Storage::disk(self::DISCO)->putFileAs(self::DIR, $file, self::ARCHIVOS[$key]);
    }

    public function eliminar(string $key): void
    {
        if (! isset(self::ARCHIVOS[$key])) {
            return;
        }
        Storage::disk(self::DISCO)->delete(self::DIR.'/'.self::ARCHIVOS[$key]);
    }

    /**
     * @return array<string, string>
     */
    public static function slots(): array
    {
        return [
            'firma' => 'Firma del Ing. Roger Neptali Flores Coaquira',
            'iso1' => 'Logo ISO #1',
            'iso2' => 'Logo ISO #2',
            'iso3' => 'Logo ISO #3',
        ];
    }
}
