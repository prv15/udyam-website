<?php

declare(strict_types=1);

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

final class QrCodeService
{
    public function render(string $url, string $format = 'png'): string
    {
        // Endroid 5 targets the application's PHP 8.1 runtime. PHP 8.4+ emits
        // vendor-signature deprecations which must never leak into image bytes.
        $reporting = error_reporting();
        error_reporting($reporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        try {
            $qr = new QrCode($url, new Encoding('UTF-8'), ErrorCorrectionLevel::High, 1200, 48, RoundBlockSizeMode::Margin, new Color(20, 69, 57), new Color(255, 255, 255));
            return ($format === 'svg' ? new SvgWriter() : new PngWriter())->write($qr)->getString();
        } finally {
            error_reporting($reporting);
        }
    }
}
