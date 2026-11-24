<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BarcodeType: string implements HasColor, HasLabel
{
    case Code128 = 'code_128';
    case Ean13 = 'ean_13';

    public function getLabel(): string
    {
        return match ($this) {
            self::Code128 => 'Code 128 (Versi Lama)',
            self::Ean13 => 'EAN-13 (Standar Retail 13 Digit)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Code128 => 'Code 128',
            self::Ean13 => 'EAN-13',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Code128 => 'gray',
            self::Ean13 => 'success',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Code128->value => self::Code128->getLabel(),
            self::Ean13->value => self::Ean13->getLabel(),
        ];
    }
}
