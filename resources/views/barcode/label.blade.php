<div class="fi-barcode-label">
    <div class="fi-barcode-label__card">
        <div class="fi-barcode-label__store">{{ $storeName }}</div>
        <div class="fi-barcode-label__product">{{ $productName }}</div>

        <div class="fi-barcode-label__barcode">
            @if ($format === 'svg')
                <div>
                    {!! $barcode !!}
                </div>
            @else
                <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode">
            @endif
        </div>

        <div class="fi-barcode-label__sku">{{ $sku }}</div>
        <div class="fi-barcode-label__price">Rp {{ number_format($price, 0, ',', '.') }}</div>
    </div>
</div>
