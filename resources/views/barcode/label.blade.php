<style>
    #barcode-label-container {
        font-family: sans-serif;
        padding: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .label-container {
        background: #fff;
        border: 1px solid #ccc;
        padding: 15px;
        border-radius: 8px;
        width: 250px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .store-name {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6b7280;
        margin-bottom: 5px;
    }
    .product-name {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: #111827;
    }
    .barcode-img {
        margin: 10px 0;
        max-width: 100%;
        display: flex;
        justify-content: center;
    }
    .barcode-img svg {
        max-width: 100%;
        height: auto;
    }
    .sku {
        font-family: monospace;
        font-size: 0.875rem;
        color: #374151;
        margin-bottom: 5px;
    }
    .price {
        font-size: 1.1rem;
        font-weight: 700;
        color: #d97706;
    }
    @media print {
        #barcode-label-container {
            padding: 0;
        }
        .label-container {
            box-shadow: none;
            border: 1px solid #000;
            page-break-inside: avoid;
        }
    }
</style>

<div id="barcode-label-container">
    <div class="label-container">
        <div class="store-name">{{ $storeName }}</div>
        <div class="product-name">{{ $productName }}</div>
        
        <div class="barcode-img">
            @if($format === 'svg')
                {!! $barcode !!}
            @else
                <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode">
            @endif
        </div>
        
        <div class="sku">{{ $sku }}</div>
        <div class="price">Rp {{ number_format($price, 0, ',', '.') }}</div>
    </div>
</div>
