<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubscriptionPlan: string implements HasColor, HasLabel
{
    case Free = 'free';
    case Pro = 'pro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pro => 'Pro',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Free => 'gray',
            self::Pro => 'warning',
        };
    }

    /**
     * Default feature limits for each plan.
     * null means unlimited.
     *
     * @return array{
     *   max_products: int|null,
     *   max_categories: int|null,
     *   max_staff: int|null,
     *   max_payment_methods: int|null,
     *   can_export_reports: bool,
     *   can_use_inventory: bool,
     *   can_use_variants: bool,
     *   can_use_discounts: bool,
     * }
     */
    public function defaultLimits(): array
    {
        return match ($this) {
            self::Free => [
                'max_products' => 10,
                'max_categories' => 3,
                'max_staff' => 1,
                'max_payment_methods' => 2,
                'can_export_reports' => false,
                'can_use_inventory' => false,
                'can_use_variants' => false,
                'can_use_discounts' => false,
            ],
            self::Pro => [
                'max_products' => null,
                'max_categories' => null,
                'max_staff' => null,
                'max_payment_methods' => null,
                'can_export_reports' => true,
                'can_use_inventory' => true,
                'can_use_variants' => true,
                'can_use_discounts' => true,
            ],
        };
    }

    /**
     * Human-readable description shown in plan selector.
     */
    public function description(): string
    {
        return match ($this) {
            self::Free => 'Gratis selamanya, cocok untuk cafe baru.',
            self::Pro => 'Semua fitur tanpa batas untuk bisnis cafe Anda.',
        };
    }

    /**
     * Marketing features list shown in plan selector.
     *
     * @return list<string>
     */
    public function marketingFeatures(): array
    {
        return match ($this) {
            self::Free => [
                '10 Produk',
                '3 Kategori',
                '1 Staff',
                '2 Metode Pembayaran',
                'Laporan Dasar',
            ],
            self::Pro => [
                'Produk Tidak Terbatas',
                'Kategori Tidak Terbatas',
                'Staff Tidak Terbatas',
                'Metode Pembayaran Tidak Terbatas',
                'Ekspor Laporan',
                'Manajemen Inventori',
                'Varian Produk',
                'Diskon Produk',
            ],
        };
    }

    /**
     * Price in IDR for the plan.
     */
    public function price(): int
    {
        return match ($this) {
            self::Free => 0,
            self::Pro => 150000,
        };
    }

    /**
     * Duration in months.
     */
    public function durationMonths(): int
    {
        return match ($this) {
            self::Free => 0,
            self::Pro => 1,
        };
    }
}
