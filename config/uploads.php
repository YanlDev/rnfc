<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subida en trozos (chunked) para archivos grandes
    |--------------------------------------------------------------------------
    |
    | Los planos pueden pesar varios GB. El navegador parte el archivo en
    | trozos de `chunk_size` bytes y los manda uno por uno; el server los
    | reensambla y los empuja a Bunny en streaming desde un Job en cola.
    |
    | `chunk_size` debe ser menor que client_max_body_size (nginx) y
    | upload_max_filesize / post_max_size (PHP). Con 8 MB basta con dejar
    | esos límites en ~15 MB.
    |
    */
    'chunk_size' => (int) env('UPLOAD_CHUNK_SIZE', 8 * 1024 * 1024), // 8 MB

    // Tamaño máximo de un archivo completo (suma de todos los trozos).
    'max_bytes' => (int) env('UPLOAD_MAX_BYTES', 3 * 1024 * 1024 * 1024), // 3 GB

    // Cola donde se despacha la finalización (push a Bunny).
    'queue' => env('UPLOAD_QUEUE', 'uploads'),

    // Horas tras las cuales una sesión sin completar se considera abandonada
    // y se limpia (chunks temporales incluidos).
    'ttl_horas' => (int) env('UPLOAD_TTL_HORAS', 24),
];
