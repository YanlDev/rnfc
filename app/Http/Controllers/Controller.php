<?php

namespace App\Http\Controllers;

use App\Support\TipoDocumento;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Sirve un archivo del disco defendiéndose de XSS almacenado:
     *  - Sólo PDF/imágenes rasterizadas se muestran inline; el resto se fuerza
     *    como descarga (un SVG/HTML servido inline ejecutaría scripts en el
     *    origen de la app).
     *  - `X-Content-Type-Options: nosniff` impide que el navegador reinterprete
     *    el contenido ignorando el Content-Type declarado.
     */
    protected function servirArchivoSeguro(
        Filesystem $disk,
        string $path,
        string $nombreDescarga,
        ?string $mime,
    ): StreamedResponse {
        $inline = TipoDocumento::esInlineSeguro($mime);

        return $disk->response(
            $path,
            $nombreDescarga,
            [
                'Content-Type' => $mime ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            ],
            $inline ? 'inline' : 'attachment',
        );
    }
}
