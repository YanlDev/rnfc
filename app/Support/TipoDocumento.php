<?php

namespace App\Support;

/**
 * Fuente única de verdad sobre qué archivos se aceptan en el gestor documental
 * y cómo es seguro servirlos.
 *
 * Seguridad: un archivo servido *inline* con un MIME activo (SVG, HTML, XML)
 * ejecuta scripts en el origen de la app (XSS almacenado). Por eso sólo un
 * conjunto reducido de tipos "planos" se sirve inline; el resto se fuerza como
 * descarga (attachment) con `X-Content-Type-Options: nosniff`.
 */
class TipoDocumento
{
    /**
     * Extensiones aceptadas al subir. Se usa para la regla `mimes:` (subida
     * directa) y como respaldo al validar la subida en trozos.
     *
     * Nota: SVG queda deliberadamente fuera (es ejecutable). Los planos CAD
     * (dwg/dxf) se aceptan aunque finfo no los reconozca.
     *
     * @var array<int, string>
     */
    public const EXTENSIONES = [
        'pdf',
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        'dwg', 'dxf',
        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'txt', 'csv',
        'zip', 'rar', '7z',
    ];

    /**
     * MIME types reconocidos como válidos al reensamblar una subida en trozos.
     * `application/octet-stream` se admite porque los CAD y algunos binarios
     * caen ahí; la seguridad no depende de esto sino de cómo se sirve el
     * archivo (ver esInlineSeguro()).
     *
     * @var array<int, string>
     */
    public const MIMES = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain', 'text/csv',
        'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed',
        'application/octet-stream',
    ];

    /**
     * MIME types que es seguro renderizar inline en el navegador: no ejecutan
     * scripts. Todo lo demás (SVG, HTML, Office, binarios) se sirve como
     * descarga.
     *
     * @var array<int, string>
     */
    public const INLINE_SEGUROS = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    ];

    /**
     * Regla `mimes:` de Laravel a partir de las extensiones permitidas.
     */
    public static function reglaMimes(): string
    {
        return 'mimes:'.implode(',', self::EXTENSIONES);
    }

    /**
     * ¿Es seguro servir este MIME inline (sin forzar descarga)?
     */
    public static function esInlineSeguro(?string $mime): bool
    {
        return $mime !== null && in_array(strtolower($mime), self::INLINE_SEGUROS, true);
    }

    /**
     * Valida un archivo ya ensamblado (subida en trozos): el MIME detectado
     * debe estar permitido, o su extensión debe estar en la lista blanca
     * (cubre CAD y binarios que finfo reporta como octet-stream).
     */
    public static function permitidoEnsamblado(?string $mime, string $nombreOriginal): bool
    {
        $mime = $mime !== null ? strtolower($mime) : null;
        if ($mime !== null && in_array($mime, self::MIMES, true)) {
            return true;
        }

        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        return $ext !== '' && in_array($ext, self::EXTENSIONES, true);
    }
}
