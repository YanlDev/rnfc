<?php

namespace App\Support;

class Bytes
{
    /**
     * Formatea una cantidad de bytes a una cadena legible (B, KB, MB, GB).
     * Fuente única de verdad para todo el proyecto.
     */
    public static function humano(int $bytes): string
    {
        if ($bytes <= 0) {
            return '—';
        }
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 ** 2) {
            return number_format($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1024 ** 3) {
            return number_format($bytes / (1024 ** 2), 1).' MB';
        }

        return number_format($bytes / (1024 ** 3), 2).' GB';
    }
}
