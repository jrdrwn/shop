<div class="flex items-center justify-center p-2 font-sans print:p-0">
    <div class="w-[250px] rounded-lg border border-gray-300 bg-white p-4 text-center shadow-md print:shadow-none print:border-black [print-color-adjust:exact]">
        <div class="mb-1 text-[0.7rem] font-semibold uppercase tracking-widest text-gray-500">{{ $storeName }}</div>
        <div class="mb-1 text-base font-bold text-gray-900">{{ $productName }}</div>

        <div class="my-2 flex items-center justify-center">
            @if($format === 'svg')
                <div class="max-w-full [&_svg]:max-w-full [&_svg]:h-auto">
                    {!! $barcode !!}
                </div>
            @else
                <img src="data:image/png;base64,{{ $barcode }}" alt="Barcode" class="max-w-full">
            @endif
        </div>

        <div class="mb-1 font-mono text-sm text-gray-700">{{ $sku }}</div>
        <div class="text-lg font-bold text-amber-600">Rp {{ number_format($price, 0, ',', '.') }}</div>
    </div>
</div>
