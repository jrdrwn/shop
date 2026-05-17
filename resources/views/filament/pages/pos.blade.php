<x-filament-panels::page>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ app(\App\Services\MidtransService::class)->snapUrl() }}" data-client-key="{{ $midtransClientKey ?? app(\App\Services\MidtransService::class)->clientKey() }}"></script>

    <div id="pos-app" class="min-h-screen bg-gray-50 dark:bg-gray-950 p-4 md:p-6 lg:p-8 fi-bg-color-50 dark:fi-bg-color-950">
        <!-- Toast -->
        <div id="pos-toast" class="fixed top-5 left-1/2 -translate-x-1/2 z-[4000] px-6 py-3 rounded-xl shadow-2xl font-semibold text-sm transition-all duration-300 opacity-0 pointer-events-none [&.active]:opacity-100 [&.active]:top-8 bg-gray-900 text-white dark:bg-white dark:text-gray-900 fi-bg-color-900 fi-text-color-0 dark:fi-bg-color-50 dark:fi-text-color-900"></div>

        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[5000] flex flex-col items-center justify-center gap-4 text-white opacity-0 pointer-events-none transition-opacity duration-300 [&.active]:opacity-100 [&.active]:pointer-events-auto hidden">
            <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>
            <p class="font-bold tracking-wide animate-pulse">Memproses Transaksi...</p>
        </div>

        <!-- Custom Confirmation Modal -->
        <div id="cancel-confirm-modal" class="fixed inset-0 bg-black/75 flex items-center justify-center z-[5000] backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 [&.active]:opacity-100 [&.active]:pointer-events-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-[min(380px,90vw)] p-8 text-center shadow-2xl scale-90 transition-all duration-300 transform [div.active_&]:scale-100 fi-pos-card">
                <div class="text-5xl mb-4 text-red-500">⚠️</div>
                <div class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2">Batalkan Pesanan?</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
                    Apakah Anda yakin ingin membatalkan pesanan ini?<br>
                    <strong class="text-gray-700 dark:text-gray-200">Stok barang akan otomatis dikembalikan.</strong>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button id="confirm-no" class="p-3 rounded-xl font-bold text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Tidak, Kembali</button>
                    <button id="confirm-yes" class="p-3 rounded-xl font-bold text-sm bg-red-500 text-white hover:bg-red-600 transition-colors">Ya, Batalkan</button>
                </div>
            </div>
        </div>

        <!-- Receipt Modal -->
        <div id="receipt-modal" class="fixed inset-0 bg-black/75 flex items-center justify-center z-[3000] backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 [&.active]:opacity-100 [&.active]:pointer-events-auto" role="dialog" aria-modal="true">
            <div class="bg-white dark:bg-gray-900 rounded-2xl w-[min(460px,94vw)] max-h-[90vh] flex flex-col overflow-hidden shadow-2xl scale-95 transition-all duration-300 transform [div.active_&]:scale-100 fi-pos-card">
                <div class="text-center p-7 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 fi-pos-border fi-pos-border-dark fi-bg-color-50 dark:fi-bg-color-950">
                    <div class="mb-2">
                        @if($tokoLogo)
                            <img src="{{ asset('storage/' . $tokoLogo) }}" alt="Logo Toko" class="max-h-12 max-w-full mx-auto">
                        @else
                            <img src="{{ asset('/default-logo/light-mode.png') }}" class="logo-light max-h-12 max-w-full mx-auto" alt="Logo Default">
                            <img src="{{ asset('/default-logo/dark-mode.png') }}" class="logo-dark max-h-12 max-w-full mx-auto hidden" alt="Logo Default">
                        @endif
                    </div>
                    <h2 class="m-0 text-lg font-bold tracking-widest text-gray-700 dark:text-gray-200 uppercase fi-text-color-700 dark:fi-text-color-0">{{ $tokoName }}</h2>
                    <p id="receipt-trx-num" class="mt-1 text-xs text-gray-400 font-mono fi-text-color-400">TRX...</p>
                </div>

                <div class="flex-1 overflow-y-auto p-5 flex flex-col gap-2">
                    <div class="text-[0.7rem] font-bold tracking-widest text-gray-400 uppercase mb-1">Detail Pesanan</div>
                    <div id="receipt-items-list" class="flex flex-col gap-1.5"></div>

                    <div class="border-t border-dashed border-gray-200 dark:border-gray-700 my-2"></div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                        <strong id="receipt-subtotal" class="text-gray-900 dark:text-white">Rp 0</strong>
                    </div>
                    <div class="flex justify-between text-sm" id="receipt-tax-row" style="display:none">
                        <span class="text-gray-500 dark:text-gray-400">Pajak (<span id="receipt-tax-rate">0</span>%)</span>
                        <strong id="receipt-tax-amt" class="text-gray-900 dark:text-white">Rp 0</strong>
                    </div>
                    <div class="flex justify-between text-sm" id="receipt-service-row" style="display:none">
                        <span class="text-gray-500 dark:text-gray-400">Service (<span id="receipt-service-rate">0</span>%)</span>
                        <strong id="receipt-service-amt" class="text-gray-900 dark:text-white">Rp 0</strong>
                    </div>
                    <div class="flex justify-between text-sm" id="receipt-discount-row" style="display:none">
                        <span class="text-gray-500 dark:text-gray-400">Diskon</span>
                        <strong id="receipt-discount-amt" class="text-green-600 dark:text-green-400">-Rp 0</strong>
                    </div>

                    <div class="flex justify-between text-lg font-bold py-3 border-y-2 border-gray-100 dark:border-gray-800 my-1 text-gray-900 dark:text-white">
                        <span>TOTAL</span>
                        <strong id="receipt-total">Rp 0</strong>
                    </div>

                    <div class="border-t border-dashed border-gray-200 dark:border-gray-700 my-2"></div>

                    <div class="text-[0.7rem] font-bold tracking-widest text-gray-400 uppercase mb-1">Pembayaran</div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Metode</span>
                        <strong id="receipt-payment-method" class="text-gray-900 dark:text-white uppercase">CASH</strong>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Jumlah Bayar</span>
                        <strong id="receipt-paid" class="text-gray-900 dark:text-white">Rp 0</strong>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Kembalian</span>
                        <strong id="receipt-change" class="text-green-600 dark:text-green-400">Rp 0</strong>
                    </div>

                    <div id="receipt-qris-section" style="display:none">
                        <div class="border-t border-dashed border-gray-200 dark:border-gray-700 my-2"></div>
                        <div class="text-[0.7rem] font-bold tracking-widest text-gray-400 uppercase text-center mb-4">Scan QRIS untuk Bayar</div>
                        <div class="flex flex-col items-center mb-4">
                            <img id="receipt-qris-img" src="" alt="QRIS" class="w-[200px] h-[200px] border-4 border-white dark:border-gray-700 rounded-xl shadow-lg bg-white">
                            <p class="text-[0.7rem] text-gray-400 mt-3 text-center px-4">Silakan scan QRIS di atas untuk menyelesaikan pembayaran.</p>
                        </div>
                    </div>

                    <div class="text-center mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <p id="receipt-time" class="text-[0.75rem] text-gray-400 mb-1"></p>
                        <p class="text-sm text-green-600 dark:text-green-400 font-bold">Terima kasih atas kunjungan Anda</p>
                    </div>
                </div>

                <div class="receipt-actions grid grid-cols-1 gap-3 p-6 pt-2">
                    <button id="print-btn" class="btn-print p-3 rounded-xl font-bold text-sm border border-amber-600 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/30 transition-colors">Cetak Struk</button>
                    <button id="close-receipt-btn" class="p-3 rounded-xl font-bold text-sm bg-amber-600 text-white hover:bg-amber-700 transition-colors shadow-lg shadow-amber-600/20">Transaksi Baru</button>
                </div>

                <div id="qris-cancel-area" class="p-6 pt-0" style="display:none">
                    <button id="qris-cancel-btn" class="w-full p-4 rounded-xl font-black text-sm bg-red-500 hover:bg-red-600 text-white transition-all shadow-lg shadow-red-500/20 uppercase tracking-widest flex items-center justify-center gap-2">
                        <span>❌</span> Batalkan Pesanan
                    </button>
                </div>
            </div>
        </div>

        <!-- Product Detail Modal -->
        <div id="product-detail-modal" class="fixed inset-0 bg-black/75 flex items-center justify-center z-[3200] backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 [&.active]:opacity-100 [&.active]:pointer-events-auto" role="dialog" aria-modal="true">
            <div class="bg-white dark:bg-gray-900 rounded-2xl w-[min(460px,94vw)] overflow-hidden shadow-2xl scale-95 transition-all duration-300 transform [div.active_&]:scale-100">
                <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                    <h3 id="detail-name" class="text-lg font-bold text-gray-900 dark:text-white">Detail Produk</h3>
                    <button type="button" id="detail-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none transition-colors">&times;</button>
                </div>
                <div class="p-6 space-y-5">
                    <div id="detail-image-wrap" class="w-full aspect-square bg-gray-50 dark:bg-gray-800 rounded-2xl overflow-hidden flex items-center justify-center">
                        <img id="detail-image" src="" alt="" class="w-full h-full object-cover hidden">
                        <div id="detail-image-placeholder" class="text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                        </div>
                    </div>
                    <div id="detail-desc" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed hidden"></div>
                    <div class="space-y-2">
                        <div class="text-sm text-gray-500 dark:text-gray-400">SKU</div>
                        <div id="detail-sku" class="font-mono font-bold text-gray-900 dark:text-white">-</div>
                    </div>
                    <div id="detail-category-row" class="space-y-2 hidden">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Kategori</div>
                        <div id="detail-category" class="font-semibold text-gray-900 dark:text-white">-</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Harga</div>
                            <div id="detail-price" class="text-xl font-black text-primary-600 dark:text-primary-400">Rp 0</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Stok</div>
                            <div id="detail-stock" class="text-lg font-black text-gray-900 dark:text-white">0</div>
                        </div>
                    </div>
                    <div id="detail-discount-row" class="flex items-center justify-between bg-green-50 dark:bg-green-950/20 border border-green-100 dark:border-green-900/30 rounded-xl p-3 hidden">
                        <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase">Diskon</span>
                        <span id="detail-discount" class="text-sm font-black text-green-700 dark:text-green-300">0%</span>
                    </div>
                    <div id="detail-variants-row" class="space-y-2 hidden">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Varian Tersedia</div>
                        <div id="detail-variants" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 p-6 pt-0">
                    <button type="button" id="detail-add-btn" class="col-span-2 w-full py-3 bg-primary-600 text-white rounded-2xl font-bold text-sm hover:bg-primary-700 transition-all shadow-lg shadow-primary-600/20">Tambah ke Keranjang</button>
                    <button type="button" id="detail-close-btn" class="col-span-2 w-full py-2 text-gray-500 dark:text-gray-300 font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-800 rounded-xl transition-colors">Tutup</button>
                </div>
            </div>
        </div>


        <section class="flex flex-col lg:flex-row gap-6 min-h-[calc(100vh-10rem)]">
            <!-- Products Section -->
            <section class="flex-1 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-900 p-6 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-primary-600 text-white rounded-2xl shadow-lg shadow-primary-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Menu Produk</h3>
                            <p id="pos-product-count" class="text-xs text-gray-400 font-bold tracking-widest uppercase">{{ count($products) }} item tersedia</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="relative group flex-1 md:flex-initial">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-primary-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            </span>
                            <input type="text" id="barcode-input" placeholder="Cari SKU / Nama / Scan Barcode..." class="w-full md:w-64 pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-sm focus:ring-2 focus:ring-primary-500 outline-none transition-all">
                        </div>
                        <button type="button" id="toggle-camera-btn" class="p-3 bg-primary-600 text-white rounded-2xl hover:bg-primary-700 transition-all shadow-lg shadow-primary-600/20 active:scale-95">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        </button>
                    </div>
                </div>
                <div id="interactive" class="viewport" style="display: none; width: 100%; max-width: 400px; margin: 10px auto; position: relative;"></div>

                @if(count($categories) > 0)
                    <div class="flex flex-wrap gap-2 overflow-x-auto pb-2 scrollbar-hide">
                        <button class="pos-cat-btn px-5 py-2 rounded-full text-sm font-bold transition-all border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 [&.active]:bg-primary-600 [&.active]:text-white [&.active]:border-primary-600 active" data-cat="all">Semua</button>
                        @foreach($categories as $cat)
                            <button class="pos-cat-btn px-5 py-2 rounded-full text-sm font-bold transition-all border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 [&.active]:bg-primary-600 [&.active]:text-white [&.active]:border-primary-600" data-cat="{{ $cat['id'] }}">{{ $cat['name'] }}</button>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4">
                    @forelse($products as $product)
                        @php $isOutOfStock = (int) ($product['stock'] ?? 0) <= 0; @endphp
                        <article class="pos-card group relative flex flex-col rounded-2xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden {{ $isOutOfStock ? 'opacity-60 grayscale' : '' }}" data-product-id="{{ $product['id'] }}"
                            data-category-id="{{ $product['category_id'] ?? '' }}"
                            data-stock="{{ (int) ($product['stock'] ?? 0) }}"
                            data-sku="{{ $product['sku'] ?? '' }}"
                            data-name="{{ strtolower($product['name'] ?? '') }}"
                            data-display-name="{{ $product['name'] ?? '' }}"
                            data-price="{{ (int) ($product['price'] ?? 0) }}"
                            data-image="{{ !empty($product['image_url']) ? Storage::disk('public')->url($product['image_url']) : '' }}"
                            data-product='@json($product)'>
                            <div class="relative aspect-square overflow-hidden bg-gray-50 dark:bg-gray-800">
                                @if(!empty($product['image_url']))
                                    <img src="{{ Storage::disk('public')->url($product['image_url']) }}"
                                        alt="{{ $product['name'] }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center opacity-20">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1">
                                            <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                                        </svg>
                                    </div>
                                @endif
                                @if($isOutOfStock)
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                        <span class="bg-red-500 text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 rounded-full">Habis</span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4 flex-1 flex flex-col">
                                <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100 line-clamp-2 mb-1">{{ $product['name'] }}</h4>
                                <p class="text-primary-600 dark:text-primary-400 font-extrabold text-sm mb-2">Rp {{ number_format((int) $product['price'], 0, ',', '.') }}</p>
                                <div class="mt-auto pt-2 border-t border-gray-50 dark:border-gray-800 flex items-center justify-between">
                                    <p class="text-[10px] uppercase font-bold tracking-wider {{ $isOutOfStock ? 'text-red-500' : 'text-gray-400 dark:text-gray-500' }}" id="stock-{{ $product['id'] }}">
                                        {{ $isOutOfStock ? 'STOK HABIS' : 'STOK: ' . (int) ($product['stock'] ?? 0) }}
                                    </p>
                                    <button type="button" class="add-to-cart p-2 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-600 hover:text-white dark:hover:bg-primary-600 transition-all" data-product='@json($product)'
                                        @disabled($isOutOfStock)>
                                        @if($product['has_variants'] ?? false)
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-gray-400 opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1" class="mb-4">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                            </svg>
                            <p class="font-medium">Belum ada produk aktif</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Sidebar -->
            <aside class="w-full lg:w-[420px] space-y-4">
                <!-- Cart Section -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-primary-600"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            Keranjang
                        </div>
                        <span class="bg-primary-600 text-white text-[10px] px-2 py-0.5 rounded-full font-black" id="cart-count">0</span>
                    </div>
                    <div id="cart-list" class="flex-1 min-h-[150px] max-h-[400px] overflow-y-auto p-4 space-y-3 scrollbar-thin">
                        <div class="flex flex-col items-center justify-center py-10 text-gray-400 opacity-40 italic text-sm">
                            Belum ada item
                        </div>
                    </div>
                </div>

                <!-- Pricing Summary -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden" id="pricing-panel" style="display:none">
                    <div class="p-4 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 flex items-center gap-2 font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">
                         <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-primary-600"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                         Ringkasan
                    </div>
                    <div class="p-5 space-y-3">
                         <div class="flex justify-between items-center text-sm">
                             <span class="text-gray-500 dark:text-gray-400">Pajak</span>
                             <div class="flex items-center gap-2">
                                 <span class="text-[10px] font-bold bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-400">{{ $taxPercentage }}%</span>
                                 <span id="tax-amt-display" class="font-bold text-gray-900 dark:text-white">Rp 0</span>
                             </div>
                         </div>
                         <div class="flex justify-between items-center text-sm">
                             <span class="text-gray-500 dark:text-gray-400">Service Charge</span>
                             <div class="flex items-center gap-2">
                                 <span class="text-[10px] font-bold bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-400">{{ $serviceChargePercentage }}%</span>
                                 <span id="service-amt-display" class="font-bold text-gray-900 dark:text-white">Rp 0</span>
                             </div>
                         </div>
                         <div class="flex justify-between items-center text-sm">
                             <span class="text-gray-500 dark:text-gray-400">Diskon Produk</span>
                             <div class="flex items-center gap-2">
                                 <span class="text-[10px] font-bold bg-green-50 dark:bg-green-950/30 px-1.5 py-0.5 rounded text-green-600 dark:text-green-400">per item</span>
                                 <span id="discount-display" class="font-bold text-green-600 dark:text-green-400">Rp 0</span>
                             </div>
                         </div>

                         <div class="pt-3 border-t border-gray-100 dark:border-gray-800 space-y-2">
                             <div class="flex justify-between text-sm" id="summary-discount-row" style="display:none">
                                 <span class="text-gray-500">Diskon</span>
                                 <span id="display-discount" class="text-green-600 font-bold">-Rp 0</span>
                             </div>
                             <div class="flex justify-between text-sm" id="summary-tax-row" style="display:none">
                                 <span class="text-gray-500">Pajak ({{ $taxPercentage }}%)</span>
                                 <span id="display-tax" class="text-gray-900 dark:text-white font-bold">Rp 0</span>
                             </div>
                             <div class="flex justify-between text-sm" id="summary-service-row" style="display:none">
                                 <span class="text-gray-500">Service ({{ $serviceChargePercentage }}%)</span>
                                 <span id="display-service" class="text-gray-900 dark:text-white font-bold">Rp 0</span>
                             </div>
                             <div class="flex justify-between items-center pt-2">
                                 <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Subtotal</span>
                                 <span id="display-subtotal" class="font-bold text-gray-900 dark:text-white text-sm">Rp 0</span>
                             </div>
                             <div class="flex justify-between items-center pt-2">
                                 <span class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-tighter">Total Akhir</span>
                                 <span id="grand-total" class="text-2xl font-black text-primary-600 dark:text-primary-400 tracking-tighter">Rp 0</span>
                                 <strong id="display-total" style="display:none">0</strong>
                             </div>
                         </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden" id="payment-panel" style="display:none">
                    <div class="p-4 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 flex items-center gap-2 font-bold text-gray-900 dark:text-white uppercase tracking-wider text-sm">
                         <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-primary-600"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                         Pembayaran
                    </div>
                    <div class="p-5 space-y-5">
                        <div class="grid grid-cols-3 gap-2">
                             @php
                                 $firstActive = null;
                                 foreach (['cash', 'debit', 'qris'] as $m) {
                                     if (in_array($m, $activePaymentMethods)) {
                                         $firstActive = $m;
                                         break;
                                     }
                                 }
                             @endphp

                             @if(in_array('cash', $activePaymentMethods))
                                 <button type="button" class="pos-method-btn px-3 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-600 dark:text-gray-400 transition-all [&.active]:bg-primary-600 [&.active]:text-white [&.active]:border-primary-600 {{ $firstActive === 'cash' ? 'active' : '' }}" data-method="cash">CASH</button>
                             @endif
                             @if(in_array('debit', $activePaymentMethods))
                                 <button type="button" class="pos-method-btn px-3 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-600 dark:text-gray-400 transition-all [&.active]:bg-primary-600 [&.active]:text-white [&.active]:border-primary-600 {{ $firstActive === 'debit' ? 'active' : '' }}" data-method="debit">DEBIT</button>
                             @endif
                             @if(in_array('qris', $activePaymentMethods))
                                 <button type="button" class="pos-method-btn px-3 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-gray-600 dark:text-gray-400 transition-all [&.active]:bg-primary-600 [&.active]:text-white [&.active]:border-primary-600 {{ $firstActive === 'qris' ? 'active' : '' }}" data-method="qris">
                                     QRIS
                                 </button>
                             @endif
                        </div>

                        <div class="space-y-2">
                            <label for="paid-amount" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah Bayar (Rp)</label>
                            <input type="number" id="paid-amount" min="0" placeholder="0" class="w-full px-5 py-4 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-2xl font-black text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition-all">
                        </div>

                        <div class="flex justify-between items-center p-4 bg-primary-50 dark:bg-primary-900/20 rounded-2xl border border-primary-100 dark:border-primary-900/30">
                            <span class="text-xs font-bold text-primary-700 dark:text-primary-400 uppercase">Kembalian</span>
                            <strong id="display-change" class="text-xl font-black text-primary-600 dark:text-primary-400 tracking-tighter">Rp 0</strong>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            <button id="checkout-btn" class="w-full py-4 bg-primary-600 text-white rounded-2xl font-bold text-lg hover:bg-primary-700 disabled:opacity-50 disabled:grayscale transition-all shadow-xl shadow-primary-600/20" disabled>Proses Pembayaran</button>
                            <button id="cancel-btn" class="w-full py-3 text-red-500 font-bold text-sm hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl transition-colors">Batal Transaksi</button>
                        </div>
                    </div>
                </div>

                <!-- System Logs for Debugging -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden" id="debug-panel">
                    <div class="p-3 border-b border-gray-50 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-950/30 flex items-center gap-2 font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                        Log Sistem
                    </div>
                    <div id="pos-debug-log" class="p-3 max-h-[100px] overflow-y-auto font-mono text-[10px] text-gray-500 dark:text-gray-400 space-y-1 scrollbar-thin">
                        <div class="flex items-center gap-2">
                            <span class="text-primary-500 font-bold">[Sistem]</span>
                            <span>POS Siap. Mode QRIS: {{ strtoupper($qrisType) }}</span>
                        </div>
                    </div>
                </div>
            </aside>
        </section>

    <!-- Variant Modal -->
    <div id="variant-modal" class="fixed inset-0 bg-black/75 flex items-center justify-center z-[3000] backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 [&:not(.hidden)]:opacity-100 [&:not(.hidden)]:pointer-events-auto hidden" role="dialog" aria-modal="true">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-[min(400px,94vw)] overflow-hidden shadow-2xl scale-95 transition-all duration-300 transform [div:not(.hidden)_&]:scale-100">
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <h3 id="variant-product-name" class="text-lg font-bold text-gray-900 dark:text-white">Pilih Opsi</h3>
                <button type="button" id="variant-close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none transition-colors">&times;</button>
            </div>
            <div id="variant-body" class="p-6 flex flex-col gap-6 max-h-[60vh] overflow-y-auto scrollbar-thin"></div>
            <div class="p-6 pt-0">
                <button type="button" id="variant-confirm" class="w-full py-4 bg-primary-600 text-white rounded-2xl font-bold text-lg hover:bg-primary-700 shadow-xl shadow-primary-600/20 active:scale-[0.98] transition-all">Tambah ke Keranjang</button>
            </div>
        </div>
    </div>

    </div>

    <style>
        * { box-sizing: border-box; }
            @media print {
                body { background: #fff !important; padding: 0 !important; }
                .logo-light { display: inline-block !important; }
                .logo-dark { display: none !important; }
                body * { visibility: hidden !important; }
                #receipt-modal, #receipt-modal * { visibility: visible !important; }
                #variant-modal { display: none !important; visibility: hidden !important; }
                #receipt-modal {
                    position: fixed !important; inset: 0 !important;
                    display: flex !important; align-items: flex-start !important;
                    justify-content: center !important; background: #fff !important;
                    backdrop-filter: none !important;
                }
                #receipt-modal.hidden { display: flex !important; visibility: visible !important; }
                #receipt-modal > div {
                    box-shadow: none !important;
                    border-radius: 0 !important;
                    background: #fff !important;
                }
                .receipt-container, .dark .receipt-container {
                    position: relative !important; box-shadow: none !important;
                    border-radius: 0 !important; width: 80mm !important;
                    max-width: 100% !important; max-height: none !important;
                    overflow: visible !important; background: #fff !important;
                    color: #000 !important; font-size: 10pt !important;
                    page-break-inside: avoid;
                }
                .receipt-item {
                    display: flex !important;
                    justify-content: space-between !important;
                    gap: 8px !important;
                }
                .receipt-item strong { text-align: right !important; }
                .receipt-header { border-bottom: 2px solid #000 !important; padding: .4rem .75rem !important; }
                .receipt-header h2 { font-size: 12pt !important; letter-spacing: .08em !important; }
                .receipt-logo { font-size: 1.5rem !important; }
                .receipt-trx-num { color: #505050 !important; font-size: 8pt !important; }
                .receipt-body { padding: .4rem .75rem !important; gap: .3rem !important; }
                .receipt-section-label { color: #3c3c3c !important; font-size: 7pt !important; margin: .25rem 0 .1rem !important; }
                .receipt-item, .receipt-row { font-size: 9pt !important; }
                .receipt-divider { border-color: #000 !important; margin: .25rem 0 !important; }
                .receipt-total-row { border-color: #000 !important; color: #000 !important; font-size: 11pt !important; padding: .4rem 0 !important; }
                .text-success { color: #00803c !important; }
                .receipt-footer { padding: .25rem 0 !important; }
                .receipt-time { color: #646464 !important; font-size: 8pt !important; }
                .receipt-thanks { color: #00803c !important; font-size: 9pt !important; }
                .receipt-actions, #receipt-qris-section { display: none !important; visibility: hidden !important; }
            }
        </style>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
        <script>
            (() => {
                const TOKO_TAX_RATE = {{ $taxPercentage }};
                const TOKO_SERVICE_RATE = {{ $serviceChargePercentage }};
                const cart = [];
                const cartList = document.getElementById('cart-list');
                const cartCount = document.getElementById('cart-count');
                const pricingPanel = document.getElementById('pricing-panel');
                const paymentPanel = document.getElementById('payment-panel');
                const receiptModal = document.getElementById('receipt-modal');
                const paidAmountInput = document.getElementById('paid-amount');
                const paymentMethodBtns = document.querySelectorAll('.pos-method-btn');
                const checkoutBtn = document.getElementById('checkout-btn');
                const cancelBtn = document.getElementById('cancel-btn');
                const closeReceiptBtn = document.getElementById('close-receipt-btn');
                const printBtn = document.getElementById('print-btn');
                const barcodeInput = document.getElementById('barcode-input');
                const productCountEl = document.getElementById('pos-product-count');
                const toggleCameraBtn = document.getElementById('toggle-camera-btn');
                const interactive = document.getElementById('interactive');
                const detailModal = document.getElementById('product-detail-modal');
                const detailClose = document.getElementById('detail-close');
                const detailCloseBtn = document.getElementById('detail-close-btn');
                const detailAddBtn = document.getElementById('detail-add-btn');
                const detailName = document.getElementById('detail-name');
                const detailSku = document.getElementById('detail-sku');
                const detailPrice = document.getElementById('detail-price');
                const detailStock = document.getElementById('detail-stock');
                const detailImage = document.getElementById('detail-image');
                const detailImageWrap = document.getElementById('detail-image-wrap');
                const detailImagePlaceholder = document.getElementById('detail-image-placeholder');
                const detailDesc = document.getElementById('detail-desc');
                const detailCategoryRow = document.getElementById('detail-category-row');
                const detailCategory = document.getElementById('detail-category');
                const detailDiscountRow = document.getElementById('detail-discount-row');
                const detailDiscount = document.getElementById('detail-discount');
                const detailVariantsRow = document.getElementById('detail-variants-row');
                const detailVariants = document.getElementById('detail-variants');
                let scannerIsRunning = false;
                let activeCategory = 'all';
                let activeDetailProduct = null;

                function toggleRow(el, show) {
                    if (!el) return;
                    el.classList.toggle('hidden', !show);
                }

                function updateVisibleCount() {
                    if (!productCountEl) return;
                    const visibleCount = Array.from(document.querySelectorAll('.pos-card'))
                        .filter(card => card.style.display !== 'none').length;
                    productCountEl.textContent = `${visibleCount} item tersedia`;
                }

                function applyFilters() {
                    const term = (barcodeInput?.value || '').trim().toLowerCase();
                    document.querySelectorAll('.pos-card').forEach(card => {
                        const cardCategory = card.getAttribute('data-category-id') || '';
                        const sku = (card.getAttribute('data-sku') || '').toLowerCase();
                        const name = (card.getAttribute('data-name') || '').toLowerCase();
                        const matchCategory = activeCategory === 'all' || cardCategory === activeCategory;
                        const matchTerm = !term || sku.includes(term) || name.includes(term);
                        card.style.display = matchCategory && matchTerm ? '' : 'none';
                    });
                    updateVisibleCount();
                }

                function openProductDetail(card) {
                    if (!detailModal || !card) return;
                    const rawName = card.getAttribute('data-display-name') || 'Produk';
                    const imageUrl = card.getAttribute('data-image') || '';
                    const productData = card.getAttribute('data-product') || '{}';
                    let product = {};
                    try {
                        product = JSON.parse(productData);
                    } catch (err) {
                        product = {};
                    }

                    const sku = product.sku || card.getAttribute('data-sku') || '-';
                    const price = parseFloat(product.price ?? card.getAttribute('data-price')) || 0;
                    const stock = parseInt(product.stock ?? card.getAttribute('data-stock')) || 0;
                    const description = product.description || product.short_description || '';
                    const category = product.category_name || product.category?.name || '';
                    const discountPct = parseFloat(product.discount_percentage ?? product.discount_pct ?? 0) || 0;
                    const variants = product.variants || {};

                    activeDetailProduct = product && Object.keys(product).length > 0 ? product : null;

                    detailName.textContent = rawName;
                    detailSku.textContent = sku || '-';
                    detailPrice.textContent = formatCurrency(price);
                    detailStock.textContent = stock.toString();

                    if (detailDesc) {
                        if (description) {
                            detailDesc.textContent = description;
                            toggleRow(detailDesc, true);
                        } else {
                            toggleRow(detailDesc, false);
                        }
                    }

                    if (detailCategoryRow && detailCategory) {
                        if (category) {
                            detailCategory.textContent = category;
                            toggleRow(detailCategoryRow, true);
                        } else {
                            toggleRow(detailCategoryRow, false);
                        }
                    }

                    if (detailDiscountRow && detailDiscount) {
                        if (discountPct > 0) {
                            detailDiscount.textContent = `${discountPct}%`;
                            toggleRow(detailDiscountRow, true);
                        } else {
                            toggleRow(detailDiscountRow, false);
                        }
                    }

                    if (detailVariantsRow && detailVariants) {
                        detailVariants.innerHTML = '';
                        const groups = Object.entries(variants);
                        if (groups.length > 0) {
                            groups.forEach(([groupName, options]) => {
                                const label = document.createElement('span');
                                label.className = 'text-[10px] uppercase tracking-widest text-gray-400 dark:text-gray-500 mr-2';
                                label.textContent = `${groupName}:`;
                                detailVariants.appendChild(label);
                                (options || []).forEach(opt => {
                                    const chip = document.createElement('span');
                                    chip.className = 'px-2 py-1 rounded-lg text-xs font-bold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200';
                                    chip.textContent = opt;
                                    detailVariants.appendChild(chip);
                                });
                            });
                            toggleRow(detailVariantsRow, true);
                        } else {
                            toggleRow(detailVariantsRow, false);
                        }
                    }

                    if (imageUrl) {
                        detailImage.src = imageUrl;
                        detailImage.alt = rawName;
                        detailImage.classList.remove('hidden');
                        detailImagePlaceholder.classList.add('hidden');
                        detailImageWrap.classList.remove('items-center');
                    } else {
                        detailImage.src = '';
                        detailImage.alt = '';
                        detailImage.classList.add('hidden');
                        detailImagePlaceholder.classList.remove('hidden');
                        detailImageWrap.classList.add('items-center');
                    }

                    if (detailAddBtn) {
                        detailAddBtn.disabled = stock <= 0;
                        detailAddBtn.classList.toggle('opacity-60', stock <= 0);
                        detailAddBtn.classList.toggle('cursor-not-allowed', stock <= 0);
                    }

                    detailModal.classList.add('active');
                }

                if (detailClose && detailModal) {
                    detailClose.addEventListener('click', () => {
                        detailModal.classList.remove('active');
                    });
                }

                if (detailCloseBtn && detailModal) {
                    detailCloseBtn.addEventListener('click', () => {
                        detailModal.classList.remove('active');
                    });
                }

                if (detailAddBtn) {
                    detailAddBtn.addEventListener('click', () => {
                        if (!activeDetailProduct) return;
                        if (activeDetailProduct.has_variants && activeDetailProduct.variants && Object.keys(activeDetailProduct.variants).length > 0) {
                            detailModal.classList.remove('active');
                            openVariantModal(activeDetailProduct);
                            return;
                        }
                        addToCart(activeDetailProduct, null);
                        detailModal.classList.remove('active');
                    });
                }

                if (barcodeInput) {
                    barcodeInput.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            const sku = barcodeInput.value.trim();
                            if (sku) {
                                findAndAddProductBySku(sku);
                                barcodeInput.value = '';
                                applyFilters();
                            }
                        }
                    });
                    barcodeInput.addEventListener('input', applyFilters);
                }

                if (toggleCameraBtn) {
                    let isProcessingScan = false;
                    toggleCameraBtn.addEventListener('click', () => {
                        if (scannerIsRunning) {
                            Quagga.stop(); interactive.style.display = 'none';
                            toggleCameraBtn.textContent = '📷 Scan Kamera'; scannerIsRunning = false;
                        } else {
                            interactive.style.display = 'block'; toggleCameraBtn.textContent = '⏹️ Berhenti';
                            Quagga.init({
                                inputStream: { name: "Live", type: "LiveStream", target: document.querySelector('#interactive'), constraints: { facingMode: "environment" } },
                                decoder: { readers: ["code_128_reader", "ean_reader", "ean_8_reader", "code_39_reader"] }
                            }, function (err) {
                                if (err) { alert('Gagal membuka kamera: ' + err.message); interactive.style.display = 'none'; toggleCameraBtn.textContent = '📷 Scan Kamera'; return; }
                                Quagga.start(); scannerIsRunning = true;
                            });
                        }
                    });

                    Quagga.onDetected((data) => {
                        const code = data.codeResult.code;
                        if (isProcessingScan) return;
                        isProcessingScan = true;

                        // Show visual feedback with countdown on button
                        toggleCameraBtn.style.background = '#059669';
                        toggleCameraBtn.style.pointerEvents = 'none';

                        findAndAddProductBySku(code);

                        // Show "Next scan in..." countdown
                        let countdown = 1.5;
                        const timerInterval = setInterval(() => {
                            countdown -= 0.5;
                            if (countdown > 0) {
                                toggleCameraBtn.textContent = `⌛ Siap dalam ${countdown}s`;
                            } else {
                                clearInterval(timerInterval);
                            }
                        }, 500);

                        setTimeout(() => {
                            isProcessingScan = false;
                            if (scannerIsRunning) {
                                toggleCameraBtn.textContent = '⏹️ Berhenti';
                                toggleCameraBtn.style.background = '';
                                toggleCameraBtn.style.pointerEvents = 'auto';
                            }
                        }, 1500);
                    });
                }

            function findAndAddProductBySku(sku) {
                const card = document.querySelector(`.pos-card[data-sku="${sku}"]`);
                if (card) {
                    const btn = card.querySelector('.add-to-cart');
                    if (btn) {
                        btn.click();
                        showToast(`Produk masuk: ${sku}`, 'success');
                    }
                } else {
                    showToast(`SKU tidak ditemukan: ${sku}`, 'error');
                    addLog(`WARN: Scan SKU ${sku} gagal - Tidak ada di database.`);
                }
            }
            const toastEl = document.getElementById('pos-toast');

            @php
                $firstActive = 'cash';
                foreach (['cash', 'debit', 'qris'] as $m) {
                    if (in_array($m, $activePaymentMethods)) {
                        $firstActive = $m;
                        break;
                    }
                }
            @endphp
            let selectedPaymentMethod = '{{ $firstActive }}';
            let toastTimer = null;

            window.handleCancelYes = async () => {
                const confirmModal = document.getElementById('cancel-confirm-modal');
                if (confirmModal) confirmModal.classList.remove('active');

                if (!window.currentTransactionNumber) return;

                try {
                    const cancelRes = await fetch(`/cashier/pos/cancel/${window.currentTransactionNumber}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });
                    const cancelData = await cancelRes.json();
                    if (cancelData.success) {
                        showToast('Pesanan dibatalkan.', 'info');
                        setTimeout(() => { location.reload(); }, 500);
                    } else {
                        showToast('Gagal membatalkan: ' + (cancelData.message || 'Unknown'), 'error');
                    }
                } catch (err) {
                    console.error(err);
                    showToast('Error saat membatalkan pesanan.', 'error');
                }
            };

            window.handleCancelNo = () => {
                const confirmModal = document.getElementById('cancel-confirm-modal');
                if (confirmModal) confirmModal.classList.remove('active');
            };

            document.getElementById('confirm-yes').onclick = window.handleCancelYes;
            document.getElementById('confirm-no').onclick = window.handleCancelNo;

            function showToast(msg, type = 'success') {
                const isError = type === 'error';
                toastEl.textContent = msg;
                toastEl.classList.remove('opacity-0', 'pointer-events-none', 'top-5', 'bg-gray-900', 'bg-red-600');
                toastEl.classList.add('active', 'opacity-100', 'pointer-events-auto', 'top-8');

                if (isError) {
                    toastEl.classList.add('bg-red-600', 'text-white');
                } else {
                    toastEl.classList.add('bg-gray-900', 'text-white', 'dark:bg-white', 'dark:text-gray-900');
                }

                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(() => {
                    toastEl.classList.remove('active', 'opacity-100', 'pointer-events-auto', 'top-8');
                    toastEl.classList.add('opacity-0', 'pointer-events-none', 'top-5');
                }, 3500);
                addLog(`[Toast] ${msg}`);
            }

            function addLog(msg) {
                const logEl = document.getElementById('pos-debug-log');
                if (!logEl) return;
                const time = new Date().toLocaleTimeString('id-ID', { hour12: false });
                const entry = document.createElement('div');
                entry.textContent = `[${time}] ${msg}`;
                logEl.prepend(entry);
                if (logEl.children.length > 50) logEl.removeChild(logEl.lastChild);
            }

            function formatCurrency(value) {
                return 'Rp ' + Math.round(value).toLocaleString('id-ID');
            }

            function calculateTotals() {
                // subtotal BEFORE discount
                const grossSubtotal = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
                // per-product discount total
                const discountAmt = cart.reduce((sum, item) => {
                    const disc = Math.round(item.price * (item.discount_pct || 0) / 100) * item.qty;
                    return sum + disc;
                }, 0);
                const subtotal = grossSubtotal - discountAmt;
                const taxAmt = Math.round(subtotal * TOKO_TAX_RATE / 100);
                const serviceAmt = Math.round(subtotal * TOKO_SERVICE_RATE / 100);
                const total = subtotal + taxAmt + serviceAmt;

                return { grossSubtotal, discountAmt, subtotal, taxAmt, serviceAmt, total };
            }

            function updateDisplay() {
                const { discountAmt, subtotal, taxAmt, serviceAmt, total } = calculateTotals();
                const paidAmt = parseFloat(paidAmountInput.value) || 0;
                const change = Math.max(0, paidAmt - total);

                // Update pricing display
                document.getElementById('display-subtotal').textContent = formatCurrency(subtotal);
                document.getElementById('tax-amt-display').textContent = formatCurrency(taxAmt);
                document.getElementById('service-amt-display').textContent = formatCurrency(serviceAmt);
                document.getElementById('discount-display').textContent = discountAmt > 0 ? '-' + formatCurrency(discountAmt) : formatCurrency(0);
                document.getElementById('display-tax').textContent = formatCurrency(taxAmt);
                document.getElementById('display-service').textContent = formatCurrency(serviceAmt);
                document.getElementById('display-discount').textContent = formatCurrency(discountAmt);
                document.getElementById('display-total').textContent = formatCurrency(total);
                document.getElementById('grand-total').textContent = formatCurrency(total);

                // Show/hide rows
                document.getElementById('summary-tax-row').style.display = taxAmt > 0 ? 'flex' : 'none';
                document.getElementById('summary-service-row').style.display = serviceAmt > 0 ? 'flex' : 'none';
                document.getElementById('summary-discount-row').style.display = discountAmt > 0 ? 'flex' : 'none';
                document.getElementById('receipt-tax-row').style.display = taxAmt > 0 ? 'flex' : 'none';
                document.getElementById('receipt-service-row').style.display = serviceAmt > 0 ? 'flex' : 'none';
                document.getElementById('receipt-discount-row').style.display = discountAmt > 0 ? 'flex' : 'none';

                // Update change
                document.getElementById('display-change').textContent = formatCurrency(change);

                // Enable/disable checkout
                checkoutBtn.disabled = paidAmt < total || cart.length === 0;
            }

            function renderCart() {
                cartList.innerHTML = '';
                if (cart.length === 0) {
                    cartList.innerHTML = '<div class="pos-cart-empty">Belum ada item</div>';
                    cartCount.textContent = '0';
                    pricingPanel.style.display = 'none';
                    paymentPanel.style.display = 'none';
                    return;
                }

                cartCount.textContent = cart.length;
                pricingPanel.style.display = 'block';
                paymentPanel.style.display = 'block';

                // Keep debit/QRIS paid amount in sync with new total
                if (selectedPaymentMethod === 'debit' || selectedPaymentMethod === 'qris') {
                    const { total } = calculateTotals();
                    if (total > 0) { paidAmountInput.value = Math.ceil(total); }
                }

                cart.forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'group p-4 bg-gray-50 dark:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-700/50 hover:border-primary-500 transition-all flex items-center gap-3 shadow-sm mb-2';
                    const key = item._key || (item.id + '|');
                    row.innerHTML = `
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm text-gray-900 dark:text-white truncate">${item.name}</div>
                            <div class="text-[10px] font-bold text-primary-600 dark:text-primary-400 uppercase tracking-wider">${formatCurrency(item.price)}</div>
                        </div>
                        <div class="flex items-center gap-1 bg-white dark:bg-gray-900 p-1 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
                            <button type="button" class="qty-btn w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-primary-600 transition-colors" data-action="decrease" data-key="${key}">&minus;</button>
                            <div class="w-8 text-center font-black text-sm text-gray-900 dark:text-white">${item.qty}</div>
                            <button type="button" class="qty-btn w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-primary-600 transition-colors" data-action="increase" data-key="${key}">+</button>
                        </div>
                        <div class="text-right ml-2">
                             <div class="font-black text-sm text-gray-900 dark:text-white">${formatCurrency(item.price * item.qty)}</div>
                             <button type="button" class="qty-btn text-[10px] font-bold text-red-500 hover:text-red-600 uppercase tracking-widest mt-0.5 opacity-0 group-hover:opacity-100 transition-opacity" data-action="remove" data-key="${key}">Hapus</button>
                        </div>
                    `;
                    cartList.appendChild(row);
                });

                // Add event listeners
                cartList.querySelectorAll('.qty-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const key = btn.getAttribute('data-key');
                        const action = btn.getAttribute('data-action');
                        const idx = cart.findIndex(c => c._key === key);
                        if (idx === -1) return;
                        const item = cart[idx];

                        if (action === 'increase') {
                            item.qty += 1;
                            updateStockDisplay(item.id, -1);
                        } else if (action === 'decrease') {
                            item.qty -= 1;
                            updateStockDisplay(item.id, 1);
                            if (item.qty <= 0) cart.splice(idx, 1);
                        } else if (action === 'remove') {
                            updateStockDisplay(item.id, item.qty);
                            cart.splice(idx, 1);
                        }

                        renderCart();
                        updateDisplay();
                    });
                });

                updateDisplay();
            }

            // Stock display helpers
            const stockCache = {}; // id -> current displayed stock

            function initStockCache() {
                document.querySelectorAll('.pos-card[data-product-id]').forEach(card => {
                    const pid = card.getAttribute('data-product-id');
                    // Baca dari data-stock (server value) sebagai sumber kebenaran
                    const serverStock = parseInt(card.getAttribute('data-stock')) || 0;
                    stockCache[pid] = serverStock;
                    // Sync tampilan dengan server value
                    const el = document.getElementById('stock-' + pid);
                    if (!el) return;
                    if (serverStock <= 0) {
                        el.innerHTML = '<span class="text-red-500">Habis</span>';
                        card.classList.add('opacity-60', 'grayscale');
                        const btn = card.querySelector('.add-to-cart');
                        if (btn) { btn.disabled = true; }
                    } else {
                        el.textContent = 'Stok: ' + serverStock;
                        card.classList.remove('opacity-60', 'grayscale');
                        const btn = card.querySelector('.add-to-cart');
                        if (btn) { btn.disabled = false; }
                    }
                });
            }

            // Init awal
            initStockCache();

            function updateStockDisplay(productId, delta) {
                const el = document.getElementById('stock-' + productId);
                const card = document.querySelector('[data-product-id="' + productId + '"]');
                if (!el || !card) return;
                const cur = (stockCache[productId] || 0) + delta;
                stockCache[productId] = Math.max(0, cur);
                if (stockCache[productId] <= 0) {
                    el.innerHTML = '<span class="text-red-500">Habis</span>';
                    card.classList.add('opacity-60', 'grayscale');
                    const btn = card.querySelector('.add-to-cart');
                    if (btn) { btn.disabled = true; }
                } else {
                    el.textContent = 'Stok: ' + stockCache[productId];
                    card.classList.remove('opacity-60', 'grayscale');
                    const btn = card.querySelector('.add-to-cart');
                    if (btn) { btn.disabled = false; }
                }
            }

            function addToCart(product, variantNote) {
                // Match by id + notes combo so same product with diff variant is separate line
                const key = product.id + '|' + (variantNote || '');
                const existing = cart.find(c => c._key === key);
                if (existing) {
                    existing.qty += 1;
                } else {
                    cart.push({
                        _key: key,
                        id: product.id,
                        name: product.name + (variantNote ? ' (' + variantNote + ')' : ''),
                        price: Number(product.price),
                        discount_pct: Number(product.discount_percentage || 0),
                        qty: 1,
                        notes: variantNote || null,
                    });
                }
                updateStockDisplay(product.id, -1);
                renderCart();
            }

            // Variant modal
            const variantModal = document.getElementById('variant-modal');
            let pendingVariantProduct = null;
            let selectedVariants = {};

            function openVariantModal(product) {
                pendingVariantProduct = product;
                selectedVariants = {};
                document.getElementById('variant-product-name').textContent = product.name;
                const body = document.getElementById('variant-body');
                body.innerHTML = '';
                const variants = product.variants || {};
                Object.entries(variants).forEach(([groupKey, options]) => {
                    const label = { size: 'Ukuran', temp: 'Suhu' }[groupKey] || groupKey;
                    const groupEl = document.createElement('div');
                    groupEl.innerHTML = `<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">${label}</div><div class="flex flex-wrap gap-2" data-group="${groupKey}"></div>`;
                    const optWrapper = groupEl.querySelector('div:last-child');
                    options.forEach(opt => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-600 dark:text-gray-400 hover:border-primary-500 transition-all [&.selected]:bg-primary-50 [&.selected]:text-primary-700 [&.selected]:border-primary-600 dark:[&.selected]:bg-primary-950/30 dark:[&.selected]:text-primary-400';
                        btn.textContent = opt;
                        btn.addEventListener('click', () => {
                            optWrapper.querySelectorAll('button').forEach(b => b.classList.remove('selected'));
                            btn.classList.add('selected');
                            selectedVariants[groupKey] = opt;
                        });
                        optWrapper.appendChild(btn);
                    });
                    // auto-select first
                    if (options.length > 0) {
                        optWrapper.firstChild.classList.add('selected');
                        selectedVariants[groupKey] = options[0];
                    }
                    body.appendChild(groupEl);
                });
                variantModal.classList.remove('hidden');
            }

            document.getElementById('variant-close').addEventListener('click', () => {
                variantModal.classList.add('hidden');
                pendingVariantProduct = null;
            });

            document.getElementById('variant-confirm').addEventListener('click', () => {
                if (!pendingVariantProduct) return;
                const note = Object.values(selectedVariants).filter(Boolean).join(', ');
                addToCart(pendingVariantProduct, note);
                variantModal.classList.add('hidden');
                pendingVariantProduct = null;
            });

            // Product buttons
            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', () => {
                    const product = JSON.parse(btn.getAttribute('data-product'));
                    if (product.has_variants && product.variants && Object.keys(product.variants).length > 0) {
                        openVariantModal(product);
                    } else {
                        addToCart(product, null);
                    }
                });
            });

            // Product card click for detail (excluding add button)
            document.querySelectorAll('.pos-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    if (e.target.closest('.add-to-cart')) return;
                    openProductDetail(card);
                });
            });

            // Payment method selection
            paymentMethodBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    paymentMethodBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    selectedPaymentMethod = btn.getAttribute('data-method');

                    // Debit / QRIS: no change expected — auto-fill exact total
                    if (selectedPaymentMethod === 'debit' || selectedPaymentMethod === 'qris') {
                        const { total } = calculateTotals();
                        if (total > 0) {
                            paidAmountInput.value = Math.ceil(total);
                            paidAmountInput.readOnly = true;
                            paidAmountInput.style.opacity = '0.6';

                            const qrisMode = '{{ $qrisType }}';
                            if (selectedPaymentMethod === 'qris') {
                                addLog(`Mode QRIS: ${qrisMode.toUpperCase()}`);
                            }
                        }
                    } else {
                        // Cash: let cashier type the amount
                        paidAmountInput.readOnly = false;
                        paidAmountInput.style.opacity = '';
                        paidAmountInput.value = '';
                    }
                    updateDisplay();
                });
            });

            // Only paidAmountInput triggers recalc now (tax/service/discount are server-side read-only)
            paidAmountInput.addEventListener('change', updateDisplay);
            paidAmountInput.addEventListener('input', updateDisplay);

            // Paid amount: cap max digits & enforce min = total on blur
            paidAmountInput.addEventListener('input', () => {
                if (paidAmountInput.value.length > 12) {
                    paidAmountInput.value = paidAmountInput.value.slice(0, 12);
                }
                updateDisplay();
            });
            paidAmountInput.addEventListener('blur', () => {
                const { total } = calculateTotals();
                const val = parseFloat(paidAmountInput.value) || 0;
                if (val < total && total > 0) {
                    paidAmountInput.value = Math.ceil(total);
                    updateDisplay();
                }
            });

            // Checkout
            checkoutBtn.addEventListener('click', async () => {
                if (cart.length === 0) {
                    showToast('Keranjang masih kosong.');
                    return;
                }

                const { discountAmt, subtotal, taxAmt, serviceAmt, total } = calculateTotals();
                const paidAmt = parseFloat(paidAmountInput.value) || 0;
                const change = Math.max(0, paidAmt - total);

                if (paidAmt < total) {
                    showToast('Jumlah pembayaran kurang dari total.');
                    return;
                }

                const loadingOverlay = document.getElementById('loading-overlay');
                loadingOverlay.classList.remove('hidden');

                checkoutBtn.disabled = true;
                checkoutBtn.textContent = 'Memproses...';
                addLog(`Memulai checkout (${selectedPaymentMethod.toUpperCase()})...`);

                try {
                    const res = await fetch('{{ route('pos.checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            cart: cart.map(item => ({ id: item.id, qty: item.qty, notes: item.notes || null })),
                            payment_method: selectedPaymentMethod,
                            discount_amount: discountAmt,
                            paid_amount: Math.round(paidAmt),
                            change_amount: Math.round(change),
                        }),
                    });

                    loadingOverlay.classList.add('hidden');
                    addLog(`Respons diterima (${res.status} ${res.statusText})`);

                    let data;
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                        addLog('DEBUG: Data JSON diterima dari server.');
                        if (data.qris_data) {
                            addLog('DEBUG: qris_data ditemukan. Snap Token: ' + (data.qris_data.snap_token || 'KOSONG'));
                        } else {
                            addLog('DEBUG: qris_data TIDAK ditemukan dalam respons.');
                        }
                    } else {
                        const text = await res.text();
                        console.error('Non-JSON response:', text);
                        addLog('ERROR: Server memberikan respons non-JSON.');
                        showToast('Server error. Cek console untuk detail.');
                        checkoutBtn.disabled = false;
                        checkoutBtn.textContent = 'Proses Pembayaran';
                        return;
                    }

                    if (!res.ok) {
                        addLog(`ERROR: Checkout gagal - ${data.message || 'Unknown'}`);
                        showToast('Checkout gagal: ' + (data.message || 'Terjadi kesalahan.'));
                        checkoutBtn.disabled = false;
                        checkoutBtn.textContent = 'Proses Pembayaran';
                        return;
                    }

                    addLog(`Sukses! No Transaksi: ${data.transaction_number}`);

                    let pollingInterval = null;

                    const renderReceipt = () => {
                        // Clear any previous polling
                        if (pollingInterval) clearInterval(pollingInterval);

                        // Show receipt
                        document.getElementById('receipt-trx-num').textContent = data.transaction_number;
                        document.getElementById('receipt-subtotal').textContent = formatCurrency(subtotal);
                        document.getElementById('receipt-tax-rate').textContent = TOKO_TAX_RATE;
                        document.getElementById('receipt-tax-amt').textContent = formatCurrency(taxAmt);
                        document.getElementById('receipt-service-rate').textContent = TOKO_SERVICE_RATE;
                        document.getElementById('receipt-service-amt').textContent = formatCurrency(serviceAmt);
                        document.getElementById('receipt-discount-amt').textContent = discountAmt > 0 ? '-' + formatCurrency(discountAmt) : formatCurrency(0);
                        document.getElementById('receipt-total').textContent = formatCurrency(total);

                        // Dynamic label: QRIS (Midtrans) or QRIS (Manual)
                        let methodLabel = selectedPaymentMethod.toUpperCase();
                        if (selectedPaymentMethod === 'qris') {
                            methodLabel += ' (' + ('{{ $qrisType }}' === 'midtrans' ? 'MIDTRANS' : 'MANUAL') + ')';
                        }
                        document.getElementById('receipt-payment-method').textContent = methodLabel;

                        document.getElementById('receipt-paid').textContent = formatCurrency(paidAmt);
                        document.getElementById('receipt-change').textContent = formatCurrency(change);
                        document.getElementById('receipt-time').textContent = new Date().toLocaleString('id-ID');

                        const receiptActions = document.querySelector('.receipt-actions');
                        const qrisSection = document.getElementById('receipt-qris-section');
                        const qrisImg = document.getElementById('receipt-qris-img');

                        // Default: Show actions
                        receiptActions.style.display = 'grid';

                        if (selectedPaymentMethod === 'qris' && data.qris_data && data.qris_data.qr_url) {
                            addLog('QRIS Midtrans aktif. Menyembunyikan tombol struk sementara.');

                            // Hide Print/New buttons while pending
                            receiptActions.style.display = 'none';

                            qrisImg.src = data.qris_data.qr_url;
                            qrisImg.style.display = 'block';
                            qrisImg.style.margin = '15px auto';
                            qrisSection.style.display = 'block';

                            const p = qrisSection.querySelector('p');
                            if (p) {
                                p.style.display = 'block';
                                p.style.textAlign = 'center';
                                p.style.fontWeight = 'bold';
                                p.style.marginTop = '10px';
                                p.innerHTML = `Menunggu Pembayaran...<br><span style="font-size:1.4rem; color:#ef4444;" id="countdown-timer">15:00</span>`;
                                p.style.color = '#f59e0b';
                                p.id = 'payment-status-text';
                            }

                            const cancelBtnArea = document.getElementById('qris-cancel-area');
                            cancelBtnArea.style.display = 'block';

                            window.currentTransactionNumber = data.transaction_number;
                            document.getElementById('qris-cancel-btn').onclick = () => {
                                const confirmModal = document.getElementById('cancel-confirm-modal');
                                if (confirmModal) confirmModal.classList.add('active');
                            };

                            // Countdown Logic
                            let timeLeft = 15 * 60;
                            if (window.countdownInterval) clearInterval(window.countdownInterval);
                            window.countdownInterval = setInterval(() => {
                                const minutes = Math.floor(timeLeft / 60);
                                const seconds = timeLeft % 60;
                                const timerEl = document.getElementById('countdown-timer');
                                if (timerEl) timerEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                                if (timeLeft <= 0) {
                                    clearInterval(window.countdownInterval);
                                    clearInterval(pollingInterval);
                                    cancelBtnArea.style.display = 'none';
                                    receiptActions.style.display = 'grid';
                                    document.querySelector('.btn-print').style.display = 'none';
                                }
                                timeLeft--;
                            }, 1000);

                            // Polling Logic
                            pollingInterval = setInterval(async () => {
                                try {
                                    const checkRes = await fetch(`/cashier/pos/check-status/${data.transaction_number}`);
                                    const checkData = await checkRes.json();
                                    if (checkData.status === 'success') {
                                        clearInterval(pollingInterval);
                                        if (window.countdownInterval) clearInterval(window.countdownInterval);
                                        cancelBtnArea.style.display = 'none';
                                        receiptActions.style.display = 'grid'; // Restore Print/New
                                        document.querySelector('.btn-print').style.display = 'block';

                                        const statusText = document.getElementById('payment-status-text');
                                        if (statusText) {
                                            statusText.innerHTML = '✅ PEMBAYARAN BERHASIL';
                                            statusText.style.color = '#16a34a';
                                        }
                                        showToast('Pembayaran Berhasil!', 'success');
                                    }
                                } catch (err) { console.error(err); }
                            }, 5000);

                        } else {
                            qrisSection.style.display = 'none';
                            if (document.getElementById('qris-cancel-area')) {
                                document.getElementById('qris-cancel-area').style.display = 'none';
                            }
                        }

                        const itemsList = document.getElementById('receipt-items-list');
                        itemsList.innerHTML = '';
                        cart.forEach(item => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'receipt-item flex justify-between text-sm text-gray-900 dark:text-white gap-3';
                            itemDiv.innerHTML = `<span>${item.name} x ${item.qty}</span><strong>${formatCurrency(item.price * item.qty)}</strong>`;
                            itemsList.appendChild(itemDiv);
                        });

                        receiptModal.classList.add('active');

                        const soldItems = cart.map(item => ({ id: item.id, qty: item.qty }));
                        cart.length = 0;

                        soldItems.forEach(({ id, qty }) => {
                            const card = document.querySelector(`[data-product-id="${id}"]`);
                            if (!card) return;
                            const prev = parseInt(card.getAttribute('data-stock')) || 0;
                            card.setAttribute('data-stock', Math.max(0, prev - qty));
                        });

                        initStockCache();
                        renderCart();
                        checkoutBtn.textContent = 'Proses Pembayaran';
                        checkoutBtn.disabled = false;
                    };

                    renderReceipt();
                } catch (e) {
                    console.error(e);
                    const loadingOverlay = document.getElementById('loading-overlay');
                    if (loadingOverlay) loadingOverlay.classList.add('hidden');

                    showToast('Gagal terhubung ke server. Coba lagi.');
                    checkoutBtn.disabled = false;
                    checkoutBtn.textContent = 'Proses Pembayaran';
                }
            });

            // Close receipt
            closeReceiptBtn.addEventListener('click', () => {
                receiptModal.classList.add('hidden');
            });

            // Print receipt
            printBtn.addEventListener('click', () => {
                window.print();
            });

            // Cancel
            cancelBtn.addEventListener('click', () => {
                // Restore stock for all items in cart before clearing it
                cart.forEach(item => {
                    updateStockDisplay(item.id, item.qty);
                });
                cart.length = 0;
                renderCart();
                paidAmountInput.value = '';
                showToast('Transaksi dibatalkan.', 'info');
            });

            // Category filter
            document.querySelectorAll('.pos-cat-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.pos-cat-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    activeCategory = btn.getAttribute('data-cat');
                    applyFilters();
                });
            });

            // Initial render
            renderCart();
            applyFilters();
        })();
    </script>
</x-filament-panels::page>
