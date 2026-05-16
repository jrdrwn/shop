<?php

test('pos page renders searchable product data attributes', function () {
    $view = view('filament.pages.pos', [
        'products' => [
            [
                'id' => 1,
                'name' => 'Kopi Gula Aren',
                'price' => 18000,
                'stock' => 5,
                'sku' => 'SKU-1',
                'category_id' => 1,
                'image_url' => null,
                'has_variants' => false,
            ],
        ],
        'categories' => [],
        'taxPercentage' => 0,
        'serviceChargePercentage' => 0,
        'activePaymentMethods' => ['cash'],
        'qrisType' => 'manual',
        'tokoName' => 'Tokatur',
        'tokoLogo' => null,
        'midtransClientKey' => null,
    ]);

    $html = $view->render();

    expect($html)->toContain('data-display-name="Kopi Gula Aren"');
    expect($html)->toContain('data-name="kopi gula aren"');
    expect($html)->toContain('data-price="18000"');
});
