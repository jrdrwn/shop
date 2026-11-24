@php
    $selectedType = $barcodeType ?? 'code_128';
    $hasDualModes = ! empty($code128Barcode) && ! empty($ean13Barcode);
@endphp

<div
    id="barcode-label-container"
    class="fi-barcode-label"
    x-data="{ activeType: '{{ $selectedType }}' }"
    style="flex-direction: column; gap: 0.75rem;"
>
    @if ($hasDualModes)
        <div style="display: inline-flex; gap: 0.35rem; padding: 0.25rem; border-radius: 0.5rem; background: #f3f4f6;">
            <button
                type="button"
                @click="activeType = 'code_128'"
                :style="activeType === 'code_128' ? 'background:#059669;color:#fff;' : 'background:transparent;color:#374151;'"
                style="padding: 0.35rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; border: none; cursor: pointer;"
            >
                Code 128 (Lama)
            </button>
            <button
                type="button"
                @click="activeType = 'ean_13'"
                :style="activeType === 'ean_13' ? 'background:#059669;color:#fff;' : 'background:transparent;color:#374151;'"
                style="padding: 0.35rem 0.75rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 700; border: none; cursor: pointer;"
            >
                EAN-13
            </button>
        </div>
    @endif

    <div class="fi-barcode-label__card">
        <div class="fi-barcode-label__store">{{ $storeName }}</div>
        <div class="fi-barcode-label__product">{{ $productName }}</div>
        <div class="fi-barcode-label__type" style="margin-bottom: 0.25rem; color: #6b7280; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;" x-text="activeType === 'ean_13' ? 'EAN-13' : 'CODE 128'">
            {{ $selectedType === 'ean_13' ? 'EAN-13' : 'CODE 128' }}
        </div>

        <div class="fi-barcode-label__barcode">
            @if ($hasDualModes)
                <div x-show="activeType === 'code_128'">
                    {!! $code128Barcode !!}
                </div>
                <div x-show="activeType === 'ean_13'" x-cloak>
                    {!! $ean13Barcode !!}
                </div>
            @elseif ($format === 'svg')
                <div>
                    {!! $barcode !!}
                </div>
            @else
                <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode">
            @endif
        </div>

        <div class="fi-barcode-label__sku">
            @if ($hasDualModes)
                <span x-show="activeType === 'code_128'">{{ $sku }}</span>
                <span x-show="activeType === 'ean_13'" x-cloak>{{ $ean13Sku ?? $sku }}</span>
            @else
                {{ $sku }}
            @endif
        </div>
        <div class="fi-barcode-label__price">Rp {{ number_format($price, 0, ',', '.') }}</div>
    </div>

    @if (! $hasDualModes && empty($ean13Barcode) && isset($code128Barcode))
        <p style="font-size: 0.75rem; color: #6b7280; text-align: center; margin: 0; max-width: 260px;">
            SKU ini menggunakan <strong>Code 128</strong>. Untuk format <strong>EAN-13</strong>, edit produk lalu isi 12–13 digit angka atau klik <em>Generate EAN-13</em>.
        </p>
    @endif
</div>
