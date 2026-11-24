<?php

namespace App\Services;

use App\Enums\BarcodeType;
use InvalidArgumentException;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    public function generateBarcode(
        string $sku,
        string $format = 'png',
        string|BarcodeType $type = BarcodeType::Code128
    ): string {
        $resolvedType = $type instanceof BarcodeType
            ? $type
            : (BarcodeType::tryFrom($type) ?? BarcodeType::Code128);

        $code = trim($sku);

        if ($resolvedType === BarcodeType::Ean13) {
            $code = $this->normalizeEan13($code);
        }

        if ($format === 'svg') {
            $generator = new BarcodeGeneratorSVG;
            $barcodeType = $resolvedType === BarcodeType::Ean13
                ? $generator::TYPE_EAN_13
                : $generator::TYPE_CODE_128;

            return $generator->getBarcode($code, $barcodeType);
        }

        $generator = new BarcodeGeneratorPNG;
        $barcodeType = $resolvedType === BarcodeType::Ean13
            ? $generator::TYPE_EAN_13
            : $generator::TYPE_CODE_128;

        return base64_encode($generator->getBarcode($code, $barcodeType));
    }

    public function isValidEan13(string $code): bool
    {
        $digits = trim($code);

        if (preg_match('/^\d{12}$/', $digits) === 1) {
            return true;
        }

        if (preg_match('/^\d{13}$/', $digits) !== 1) {
            return false;
        }

        $expectedCheckDigit = $this->calculateEan13CheckDigit(substr($digits, 0, 12));

        return (int) $digits[12] === $expectedCheckDigit;
    }

    public function normalizeEan13(string $code): string
    {
        $digits = trim($code);

        if (preg_match('/^\d{12}$/', $digits) === 1) {
            return $digits.$this->calculateEan13CheckDigit($digits);
        }

        if (preg_match('/^\d{13}$/', $digits) === 1) {
            $expectedCheckDigit = $this->calculateEan13CheckDigit(substr($digits, 0, 12));

            if ((int) $digits[12] !== $expectedCheckDigit) {
                throw new InvalidArgumentException(
                    "Digit cek EAN-13 tidak valid (seharusnya diakhiri angka {$expectedCheckDigit})."
                );
            }

            return $digits;
        }

        throw new InvalidArgumentException('EAN-13 harus terdiri dari 12 atau 13 digit angka.');
    }

    public function calculateEan13CheckDigit(string $twelveDigits): int
    {
        if (preg_match('/^\d{12}$/', $twelveDigits) !== 1) {
            throw new InvalidArgumentException('Diperlukan tepat 12 digit angka untuk menghitung digit cek EAN-13.');
        }

        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $twelveDigits[$i];
            $sum += ($i % 2 === 0) ? $digit : ($digit * 3);
        }

        return (10 - ($sum % 10)) % 10;
    }

    public function generateEan13Code(string $prefix = '899'): string
    {
        $cleanPrefix = preg_replace('/\D/', '', $prefix) ?: '899';
        $cleanPrefix = substr($cleanPrefix, 0, 11);
        $remaining = 12 - strlen($cleanPrefix);

        $body = '';
        for ($i = 0; $i < $remaining; $i++) {
            $body .= (string) random_int(0, 9);
        }

        $twelveDigits = $cleanPrefix.$body;

        return $twelveDigits.$this->calculateEan13CheckDigit($twelveDigits);
    }
}
