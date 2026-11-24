<?php

use App\Enums\BarcodeType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Toko;
use App\Models\User;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('barcode service generates Code 128 (versi lama) and EAN-13 barcodes', function (): void {
    $service = app(BarcodeService::class);

    $code128Svg = $service->generateBarcode('SKU-KOPI-01', 'svg', BarcodeType::Code128);
    expect($code128Svg)->toContain('<svg');

    $generatedEan13 = $service->generateEan13Code('899');
    expect($generatedEan13)->toMatch('/^899\d{10}$/')
        ->and($service->isValidEan13($generatedEan13))->toBeTrue();

    $ean13Svg = $service->generateBarcode($generatedEan13, 'svg', BarcodeType::Ean13);
    expect($ean13Svg)->toContain('<svg');

    $ean13Png = $service->generateBarcode($generatedEan13, 'png', BarcodeType::Ean13);
    expect(base64_decode($ean13Png, true))->not->toBeFalse();
});

test('barcode service normalizes 12-digit EAN-13 and rejects invalid check digits', function (): void {
    $service = app(BarcodeService::class);

    $normalized = $service->normalizeEan13('899123456789');
    expect(strlen($normalized))->toBe(13)
        ->and($service->isValidEan13($normalized))->toBeTrue();

    $wrongLastDigit = ((int) $normalized[12] + 1) % 10;
    $invalidEan13 = substr($normalized, 0, 12).$wrongLastDigit;

    expect($service->isValidEan13($invalidEan13))->toBeFalse();
    expect(fn () => $service->generateBarcode($invalidEan13, 'svg', BarcodeType::Ean13))
        ->toThrow(InvalidArgumentException::class);
});

test('product saves barcode_type with code_128 default and supports ean_13', function (): void {
    $admin = User::factory()->createOne(['role' => 'super_admin', 'is_active' => true]);
    $toko = Toko::query()->create(['name' => 'Toko Barcode', 'created_by' => $admin->id]);
    $category = Category::query()->create(['toko_id' => $toko->id, 'name' => 'Minuman']);

    $defaultProduct = Product::query()->create([
        'toko_id' => $toko->id,
        'category_id' => $category->id,
        'name' => 'Teh Manis',
        'price' => 5000,
        'stock' => 10,
        'sku' => 'SKU-TEH-01',
        'is_active' => true,
    ]);

    $eanCode = app(BarcodeService::class)->generateEan13Code();
    $eanProduct = Product::query()->create([
        'toko_id' => $toko->id,
        'category_id' => $category->id,
        'name' => 'Air Mineral',
        'price' => 4000,
        'stock' => 20,
        'sku' => $eanCode,
        'barcode_type' => BarcodeType::Ean13->value,
        'is_active' => true,
    ]);

    expect($defaultProduct->fresh()->barcode_type)->toBe(BarcodeType::Code128->value)
        ->and($eanProduct->fresh()->barcode_type)->toBe(BarcodeType::Ean13->value);
});

test('barcode label view renders dual Code 128 and EAN-13 switcher when both barcodes are provided', function (): void {
    $service = app(BarcodeService::class);
    $eanCode = $service->generateEan13Code();

    $html = view('barcode.label', [
        'storeName' => 'Toko Contoh',
        'productName' => 'Produk EAN',
        'barcode' => $service->generateBarcode($eanCode, 'svg', BarcodeType::Ean13),
        'barcodeType' => BarcodeType::Ean13->value,
        'code128Barcode' => $service->generateBarcode($eanCode, 'svg', BarcodeType::Code128),
        'ean13Barcode' => $service->generateBarcode($eanCode, 'svg', BarcodeType::Ean13),
        'ean13Sku' => $eanCode,
        'sku' => $eanCode,
        'price' => 12000,
        'format' => 'svg',
    ])->render();

    expect($html)
        ->toContain('id="barcode-label-container"')
        ->toContain('Code 128 (Lama)')
        ->toContain('EAN-13')
        ->toContain($eanCode);
});
