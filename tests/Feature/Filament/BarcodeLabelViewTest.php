<?php

test('barcode label view uses Filament-prefixed classes', function (): void {
    $html = view('barcode.label', [
        'storeName' => 'Toko Contoh',
        'productName' => 'Produk Demo',
        'barcode' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"></svg>',
        'sku' => 'SKU-001',
        'price' => 15000,
        'format' => 'svg',
    ])->render();

    expect($html)->toContain('fi-barcode-label');
    expect($html)->toContain('fi-barcode-label__card');
    expect($html)->toContain('fi-barcode-label__price');
    expect($html)->toContain('Produk Demo');
    expect($html)->toContain('SKU-001');
});
