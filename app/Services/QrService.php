<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrService
{
    /**
     * Genera un QR como data URI PNG embebible en HTML/Blade.
     */
    public function dataUri(string $contenido, int $size = 220): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $contenido,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 8,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return $builder->build()->getDataUri();
    }
}
