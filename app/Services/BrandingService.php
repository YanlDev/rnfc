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
     * Imágenes por defecto (ya presentes en public/) usadas cuando no se ha
     * subido un archivo propio para el slot. Rutas relativas a public_path().
     */
    private const DEFECTOS = [
        'iso1' => 'brand/ISO 9001.png',
        'iso2' => 'brand/ISO 14001.png',
        'iso3' => 'brand/ISO 37001.png',
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
            if (Storage::disk(self::DISCO)->exists($ruta)) {
                $out[$key] = Storage::disk(self::DISCO)->url($ruta).'?v='.Storage::disk(self::DISCO)->lastModified($ruta);

                continue;
            }
            $out[$key] = $this->urlDefecto($key);
        }

        /** @var array{firma: ?string, iso1: ?string, iso2: ?string, iso3: ?string} */
        return $out;
    }

    /**
     * URL pública de la imagen por defecto del slot (si existe en public/).
     */
    private function urlDefecto(string $key): ?string
    {
        $rel = self::DEFECTOS[$key] ?? null;
        if ($rel === null || ! is_file(public_path($rel))) {
            return null;
        }

        return '/'.implode('/', array_map('rawurlencode', explode('/', $rel)));
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
            if (Storage::disk(self::DISCO)->exists($ruta)) {
                $bytes = Storage::disk(self::DISCO)->get($ruta);
                $out[$key] = 'data:image/png;base64,'.base64_encode((string) $bytes);

                continue;
            }

            $rel = self::DEFECTOS[$key] ?? null;
            if ($rel !== null && is_file(public_path($rel))) {
                $bytes = (string) file_get_contents(public_path($rel));
                $out[$key] = 'data:image/png;base64,'.base64_encode($bytes);

                continue;
            }

            $out[$key] = null;
        }

        /** @var array{firma: ?string, iso1: ?string, iso2: ?string, iso3: ?string} */
        return $out;
    }

    /**
     * Indica qué slots tienen un archivo subido por el usuario (no el de
     * por defecto).
     *
     * @return array{firma: bool, iso1: bool, iso2: bool, iso3: bool}
     */
    public function personalizados(): array
    {
        $out = [];
        foreach (self::ARCHIVOS as $key => $archivo) {
            $out[$key] = Storage::disk(self::DISCO)->exists(self::DIR.'/'.$archivo);
        }

        /** @var array{firma: bool, iso1: bool, iso2: bool, iso3: bool} */
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
            'iso1' => 'ISO 9001',
            'iso2' => 'ISO 14001',
            'iso3' => 'ISO 37001',
        ];
    }
}
