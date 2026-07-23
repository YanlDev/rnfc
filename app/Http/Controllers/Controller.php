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
        bool $cachePrivado = false,
    ): StreamedResponse {
        $inline = TipoDocumento::esInlineSeguro($mime);

        $headers = [
            'Content-Type' => $mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ];

        // Previsualizaciones: cache privado corto para que miniaturas PDF y
        // visores no re-descarguen el archivo en cada vista. Corto porque una
        // nueva versión del documento se sirve por la misma URL.
        if ($cachePrivado) {
            $headers['Cache-Control'] = 'private, max-age=900';
        }

        return $disk->response(
            $path,
            $nombreDescarga,
            $headers,
            $inline ? 'inline' : 'attachment',
        );
    }
}
