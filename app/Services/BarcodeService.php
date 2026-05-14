<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    public function generateBarcode(string $sku, string $format = 'png'): string
    {
        if ($format === 'svg') {
            $generator = new BarcodeGeneratorSVG;

            return $generator->getBarcode($sku, $generator::TYPE_CODE_128);
        }

        $generator = new BarcodeGeneratorPNG;

        return base64_encode($generator->getBarcode($sku, $generator::TYPE_CODE_128));
    }
}
