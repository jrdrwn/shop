<x-filament-panels::page>
    <div id="pos-app" class="pos-app">
        <!-- Toast -->
        <div id="pos-toast" class="pos-toast pos-toast-hidden"></div>

        <!-- Receipt Modal -->
        <div id="receipt-modal" class="receipt-modal hidden" role="dialog" aria-modal="true">
            <div class="receipt-container">
                <div class="receipt-header">
                    <div class="receipt-logo">
                        @if($cafeLogo)
                            <img src="{{ asset('storage/' . $cafeLogo) }}" alt="Logo Cafe" style="max-height: 48px; max-width: 100%;">
@else
                            <img src="{{ asset('/default-logo/light-mode.png') }}" class="logo-light" alt="Logo Default" style="max-height: 48px; max-width: 100%;">
                            <img src="{{ asset('/default-logo/dark-mode.png') }}" class="logo-dark" alt="Logo Default" style="max-height: 48px; max-width: 100%;">
                        @endif
                    </div>
                    <h2>{{ strtoupper($cafeName) }}</h2>
                    <p id="receipt-trx-num" class="receipt-trx-num">TRX...</p>
                </div>

                <div class="receipt-body">
                    <div class="receipt-section-label">Detail Pesanan</div>
                    <div id="receipt-items-list" class="receipt-items-list"></div>

                    <div class="receipt-divider"></div>

                    <div class="receipt-row">
                        <span>Subtotal</span>
                        <strong id="receipt-subtotal">Rp 0</strong>
                    </div>
                    <div class="receipt-row" id="receipt-tax-row" style="display:none">
                        <span>Pajak (<span id="receipt-tax-rate">0</span>%)</span>
                        <strong id="receipt-tax-amt">Rp 0</strong>
                    </div>
                    <div class="receipt-row" id="receipt-service-row" style="display:none">
                        <span>Service (<span id="receipt-service-rate">0</span>%)</span>
                        <strong id="receipt-service-amt">Rp 0</strong>
                    </div>
                    <div class="receipt-row" id="receipt-discount-row" style="display:none">
                        <span>Diskon</span>
                        <strong id="receipt-discount-amt" class="text-success">-Rp 0</strong>
                    </div>

                    <div class="receipt-total-row">
                        <span>TOTAL</span>
                        <strong id="receipt-total">Rp 0</strong>
                    </div>

                    <div class="receipt-divider"></div>

                    <div class="receipt-section-label">Pembayaran</div>
                    <div class="receipt-row">
                        <span>Metode</span>
                        <strong id="receipt-payment-method">CASH</strong>
                    </div>
                    <div class="receipt-row">
                        <span>Jumlah Bayar</span>
                        <strong id="receipt-paid">Rp 0</strong>
                    </div>
                    <div class="receipt-row">
                        <span>Kembalian</span>
                        <strong id="receipt-change" class="text-success">Rp 0</strong>
                    </div>

                    <div class="receipt-footer">
                        <p id="receipt-time" class="receipt-time"></p>
                        <p class="receipt-thanks">Terima kasih atas kunjungan Anda</p>
                    </div>
                </div>

                <div class="receipt-actions">
                    <button id="print-btn" class="receipt-btn receipt-btn-outline">Cetak Struk</button>
                    <button id="close-receipt-btn" class="receipt-btn receipt-btn-primary">Transaksi Baru</button>
                </div>
            </div>
        </div>


        <!-- Main POS Layout -->
        <div class="pos-layout">
            <!-- Products Section -->
            <section class="pos-products">
                <div class="pos-products-header">
                    <div class="pos-products-title">
                        <span class="pos-products-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                                <line x1="6" y1="1" x2="6" y2="4" />
                                <line x1="10" y1="1" x2="10" y2="4" />
                                <line x1="14" y1="1" x2="14" y2="4" />
                            </svg>
                        </span>
                        <div>
                            <h3>Menu Produk</h3>
                            <p id="pos-product-count">{{ count($products) }} item tersedia</p>
                        </div>
                    </div>
                </div>

                @if(count($categories) > 0)
                    <div class="pos-category-tabs">
                        <button class="pos-cat-btn active" data-cat="all">Semua</button>
                        @foreach($categories as $cat)
                            <button class="pos-cat-btn" data-cat="{{ $cat['id'] }}">{{ $cat['name'] }}</button>
                        @endforeach
                    </div>
                @endif

                <div class="pos-grid">
                    @forelse($products as $product)
                        @php $isOutOfStock = (int) ($product['stock'] ?? 0) <= 0; @endphp
                        <article class="pos-card {{ $isOutOfStock ? 'is-out' : '' }}" data-product-id="{{ $product['id'] }}"
                            data-category-id="{{ $product['category_id'] ?? '' }}"
                            data-stock="{{ (int) ($product['stock'] ?? 0) }}">
                            <div class="pos-image-wrapper">
                                @if(!empty($product['image_url']))
                                    <img src="{{ Storage::disk('public')->url($product['image_url']) }}"
                                        alt="{{ $product['name'] }}" class="pos-image">
                                @else
                                    <div class="pos-image-fallback">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1" opacity="0.3">
                                            <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                                            <line x1="6" y1="1" x2="6" y2="4" />
                                            <line x1="10" y1="1" x2="10" y2="4" />
                                            <line x1="14" y1="1" x2="14" y2="4" />
                                        </svg>
                                    </div>
                                @endif
                                @if($isOutOfStock)
                                    <div class="pos-out-badge">Habis</div>
                                @endif
                            </div>
                            <div class="pos-card-body">
                                <h4>{{ $product['name'] }}</h4>
                                <p class="pos-price">Rp {{ number_format((int) $product['price'], 0, ',', '.') }}</p>
                                <p class="pos-stock" id="stock-{{ $product['id'] }}">
                                    @if($isOutOfStock)
                                        <span class="pos-stock-empty">Habis</span>
                                    @else
                                        Stok: {{ (int) ($product['stock'] ?? 0) }}
                                    @endif
                                </p>
                            </div>
                            <button type="button" class="pos-add-btn add-to-cart" data-product='@json($product)'
                                @disabled($isOutOfStock)>{{ ($product['has_variants'] ?? false) ? 'Pilih Opsi' : ($isOutOfStock ? 'Stok Habis' : '+ Tambah') }}</button>
                        </article>
                    @empty
                        <div class="pos-empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1" opacity="0.3" style="margin-bottom:.5rem">
                                <rect x="2" y="7" width="20" height="14" rx="2" />
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                            </svg>
                            <p>Belum ada produk aktif</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- Sidebar -->
            <aside class="pos-sidebar">
                <!-- Cart -->
                <div class="pos-panel">
                    <div class="pos-panel-header">
                        <div class="pos-panel-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1" />
                                <circle cx="20" cy="21" r="1" />
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                            </svg>
                            Keranjang
                        </div>
                        <span class="pos-badge" id="cart-count">0</span>
                    </div>
                    <div id="cart-list" class="pos-cart-list">
                        <div class="pos-cart-empty">Belum ada item</div>
                    </div>
                </div>

                <!-- Summary + Payment -->
                <div class="pos-panel" id="pricing-panel" style="display:none">
                    <div class="pos-panel-header">
                        <div class="pos-panel-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6" />
                                <line x1="8" y1="12" x2="21" y2="12" />
                                <line x1="8" y1="18" x2="21" y2="18" />
                                <line x1="3" y1="6" x2="3.01" y2="6" />
                                <line x1="3" y1="12" x2="3.01" y2="12" />
                                <line x1="3" y1="18" x2="3.01" y2="18" />
                            </svg>
                            Ringkasan
                        </div>
                    </div>
                    <div class="pos-pricing-body">
                        <div class="pos-field-row">
                            <label>Pajak</label>
                            <div class="pos-field-inline">
                                <span class="pos-rate-badge">{{ $taxPercentage }}%</span>
                                <span id="tax-amt-display" class="pos-field-result">Rp 0</span>
                            </div>
                        </div>
                        <div class="pos-field-row">
                            <label>Service Charge</label>
                            <div class="pos-field-inline">
                                <span class="pos-rate-badge">{{ $serviceChargePercentage }}%</span>
                                <span id="service-amt-display" class="pos-field-result">Rp 0</span>
                            </div>
                        </div>
                        <div class="pos-field-row">
                            <label>Diskon Produk</label>
                            <div class="pos-field-inline">
                                <span class="pos-rate-badge pos-rate-badge--discount">per item</span>
                                <span id="discount-display" class="pos-field-result text-success">Rp 0</span>
                            </div>
                        </div>

                        <div class="pos-summary-block">
                            <div class="pos-summary-row" id="summary-discount-row" style="display:none">
                                <span>Diskon</span><span id="display-discount" class="text-success">-Rp 0</span>
                            </div>
                            <div class="pos-summary-row" id="summary-tax-row" style="display:none">
                                <span>Pajak ({{ $taxPercentage }}%)</span><span id="display-tax">Rp 0</span>
                            </div>
                            <div class="pos-summary-row" id="summary-service-row" style="display:none">
                                <span>Service ({{ $serviceChargePercentage }}%)</span><span id="display-service">Rp
                                    0</span>
                            </div>
                            <div class="pos-summary-row">
                                <span>Subtotal</span><span id="display-subtotal">Rp 0</span>
                            </div>
                            <div class="pos-total-row">
                                <span>TOTAL</span><strong id="display-total">Rp 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pos-panel" id="payment-panel" style="display:none">
                    <div class="pos-panel-header">
                        <div class="pos-panel-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                                <line x1="1" y1="10" x2="23" y2="10" />
                            </svg>
                            Pembayaran
                        </div>
                    </div>
                    <div class="pos-payment-body">
                        <div class="pos-method-group">
                            <button type="button" class="pos-method-btn active" data-method="cash">Cash</button>
                            <button type="button" class="pos-method-btn" data-method="debit">Debit</button>
                            <button type="button" class="pos-method-btn" data-method="qris">QRIS</button>
                        </div>

                        <div class="pos-field-row">
                            <label for="paid-amount">Jumlah Bayar (Rp)</label>
                            <input type="number" id="paid-amount" min="0" placeholder="0"
                                class="pos-input pos-input-lg">
                        </div>

                        <div class="pos-change-box">
                            <span>Kembalian</span>
                            <strong id="display-change">Rp 0</strong>
                        </div>

                        <button id="checkout-btn" class="pos-btn-checkout" disabled>Proses Pembayaran</button>
                        <button id="cancel-btn" class="pos-btn-cancel">Batal Transaksi</button>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Variant Modal -->
    <div id="variant-modal" class="pos-modal hidden" role="dialog" aria-modal="true">
        <div class="variant-container">
            <div class="variant-header">
                <h3 id="variant-product-name">Pilih Opsi</h3>
                <button type="button" id="variant-close" class="variant-close-btn">&times;</button>
            </div>
            <div id="variant-body" class="variant-body"></div>
            <div class="variant-footer">
                <button type="button" id="variant-confirm" class="pos-btn-checkout" style="margin:0;width:100%">Tambah
                    ke Keranjang</button>
            </div>
        </div>
    </div>

    <style>
        * {
            box-sizing: border-box;
        }

        /* ── Variant Modal ── */
        .variant-container {
            background: rgb(255 255 255);
            color: rgb(var(--color-gray-900, 17 24 39));
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .35);
            width: min(400px, 94vw);
            overflow: hidden;
        }

        .dark .variant-container {
            background: rgb(var(--color-gray-800, 31 41 55));
            color: rgb(var(--color-gray-100, 243 244 246));
        }

        .variant-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgb(var(--color-gray-200, 229 231 235));
        }

        .dark .variant-header {
            border-color: rgb(var(--color-gray-700, 55 65 81));
        }

        .variant-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
        }

        .variant-close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: rgb(var(--color-gray-500, 107 114 128));
            line-height: 1;
            padding: .25rem;
        }

        .variant-body {
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .variant-group-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: rgb(var(--color-gray-500, 107 114 128));
            margin-bottom: .5rem;
        }

        .variant-options {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .variant-opt {
            padding: .45rem 1rem;
            border-radius: 9999px;
            border: 1.5px solid rgb(var(--color-gray-300, 209 213 219));
            background: transparent;
            color: rgb(var(--color-gray-700, 55 65 81));
            font-size: .85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all .15s;
        }

        .dark .variant-opt {
            border-color: rgb(var(--color-gray-600, 75 85 99));
            color: rgb(var(--color-gray-300, 209 213 219));
        }

        .variant-opt:hover {
            border-color: rgb(var(--color-primary-400, 251 191 36));
        }

        .variant-opt.selected {
            border-color: rgb(var(--color-primary-600, 217 119 6));
            background: rgb(var(--color-primary-50, 255 251 235));
            color: rgb(var(--color-primary-700, 180 83 9));
            font-weight: 600;
        }

        .dark .variant-opt.selected {
            background: rgb(var(--color-primary-950, 28 25 23));
            color: rgb(var(--color-primary-300, 252 211 77));
        }

        .variant-footer {
            padding: 1rem 1.5rem 1.5rem;
        }

        /*  Toast  */
        .pos-toast {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            z-index: 9999;
            padding: .75rem 1.25rem;
            border-radius: 8px;
            font-size: .875rem;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .2);
        }

        .pos-toast-hidden {
            display: none;
        }

        .pos-toast-error {
            background: rgb(var(--color-danger-600, 220 38 38));
            color: rgb(255 255 255);
        }

        .pos-toast-info {
            background: rgb(var(--color-gray-700, 55 65 81));
            color: rgb(255 255 255);
        }

        .pos-toast-success {
            background: rgb(var(--color-success-600, 22 163 74));
            color: rgb(255 255 255);
        }

        /*  Receipt Modal  */
        .receipt-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .65);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            backdrop-filter: blur(4px);
        }

        .receipt-modal.hidden {
            display: none;
        }

        /* Variant Modal — same backdrop style but separate class so print CSS won't confuse them */
        .pos-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .65);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            backdrop-filter: blur(4px);
        }

        .pos-modal.hidden {
            display: none;
        }

        /* Force receipt to always look like paper (light mode) */
        .receipt-container {
            background: #fff !important;
            color: #111827 !important;
            border-radius: 16px !important;
            box-shadow: 0 24px 64px rgba(0, 0, 0, .1) !important;
            width: min(460px, 94vw) !important;
            max-height: 90vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        .receipt-header {
            text-align: center !important;
            padding: 1.75rem 2rem 1.25rem !important;
            border-bottom: 1px solid #e5e7eb !important;
            background: #f9fafb !important;
        }

        .receipt-logo {
            font-size: 2.5rem !important;
            margin-bottom: .5rem !important;
            display: flex !important;
            justify-content: center !important;
        }

        .receipt-header h2 {
            margin: 0 !important;
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            letter-spacing: .15em !important;
            color: #374151 !important;
        }

        .receipt-trx-num {
            margin: .35rem 0 0 !important;
            font-size: .8rem !important;
            color: #9ca3af !important;
            font-family: monospace !important;
        }

        .receipt-body {
            flex: 1 !important;
            overflow-y: auto !important;
            padding: 1.25rem 1.75rem !important;
            display: flex !important;
            flex-direction: column !important;
            gap: .5rem !important;
            background: #fff !important;
            color: #111827 !important;
        }

        .receipt-section-label {
            font-size: .7rem !important;
            font-weight: 700 !important;
            letter-spacing: .1em !important;
            color: #6b7280 !important;
            text-transform: uppercase !important;
            margin: .5rem 0 .25rem !important;
        }

        .receipt-items-list {
            display: flex !important;
            flex-direction: column !important;
            gap: .4rem !important;
        }

        .receipt-item {
            display: flex !important;
            justify-content: space-between !important;
            font-size: .875rem !important;
            color: #111827 !important;
        }

        .receipt-divider {
            border: none !important;
            border-top: 1px dashed #d1d5db !important;
            margin: .5rem 0 !important;
        }

        .receipt-row {
            display: flex !important;
            justify-content: space-between !important;
            font-size: .875rem !important;
            color: #111827 !important;
        }

        .receipt-container .text-success {
            color: #16a34a !important;
        }

        .receipt-total-row {
            display: flex !important;
            justify-content: space-between !important;
            font-size: 1.1rem !important;
            font-weight: 700 !important;
            padding: .75rem 0 !important;
            border-top: 2px solid #e5e7eb !important;
            border-bottom: 2px solid #e5e7eb !important;
            margin: .25rem 0 !important;
            color: #111827 !important;
        }

        .receipt-footer {
            text-align: center !important;
            margin-top: .75rem !important;
        }

        .receipt-time {
            font-size: .75rem !important;
            color: #9ca3af !important;
            margin: 0 0 .25rem !important;
        }

        .receipt-thanks {
            font-size: .875rem !important;
            color: #16a34a !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }

        /* Dark mode overrides for receipt */
        .dark .receipt-container {
            background: rgb(var(--color-gray-800, 31 41 55)) !important;
            color: rgb(var(--color-gray-100, 243 244 246)) !important;
        }

        .dark .receipt-header {
            background: rgb(var(--color-gray-900, 17 24 39)) !important;
            border-color: rgb(var(--color-gray-700, 55 65 81)) !important;
        }

        .dark .receipt-header h2 {
            color: rgb(var(--color-gray-200, 229 231 235)) !important;
        }

        .dark .receipt-trx-num {
            color: rgb(var(--color-gray-400, 156 163 175)) !important;
        }

        .dark .receipt-body {
            background: rgb(var(--color-gray-800, 31 41 55)) !important;
            color: rgb(var(--color-gray-100, 243 244 246)) !important;
        }

        .dark .receipt-section-label {
            color: rgb(var(--color-gray-400, 156 163 175)) !important;
        }

        .dark .receipt-item {
            color: rgb(var(--color-gray-100, 243 244 246)) !important;
        }

        .dark .receipt-divider {
            border-color: rgb(var(--color-gray-600, 75 85 99)) !important;
        }

        .dark .receipt-row {
            color: rgb(var(--color-gray-100, 243 244 246)) !important;
        }

        .dark .receipt-container .text-success {
            color: rgb(var(--color-success-400, 74 222 128)) !important;
        }

        .dark .receipt-total-row {
            border-color: rgb(var(--color-gray-700, 55 65 81)) !important;
            color: rgb(var(--color-gray-100, 243 244 246)) !important;
        }

        .dark .receipt-time {
            color: rgb(var(--color-gray-400, 156 163 175)) !important;
        }

        .dark .receipt-thanks {
            color: rgb(var(--color-success-400, 74 222 128)) !important;
        }

        .receipt-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
            padding: 1rem 1.75rem 1.5rem;
        }

        .receipt-btn {
            padding: .7rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: .9rem;
            cursor: pointer;
            transition: all .15s;
        }

        .receipt-btn-outline {
            background: transparent;
            border: 1.5px solid rgb(var(--color-gray-300, 209 213 219));
            color: rgb(var(--color-gray-700, 55 65 81));
        }

        .dark .receipt-btn-outline {
            border-color: rgb(var(--color-gray-600, 75 85 99));
            color: rgb(var(--color-gray-300, 209 213 219));
        }

        .receipt-btn-outline:hover {
            background: rgb(var(--color-gray-100, 243 244 246));
        }

        .dark .receipt-btn-outline:hover {
            background: rgb(var(--color-gray-700, 55 65 81));
        }

        .receipt-btn-primary {
            background: rgb(var(--color-primary-600, 217 119 6));
            border: none;
            color: rgb(var(--color-white, 255 255 255));
        }

        .receipt-btn-primary:hover {
            background: rgb(var(--color-primary-700, 180 83 9));
        }

        /*  Layout  */
        .pos-app {
            width: 100%;
        }

        .pos-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) 340px;
            gap: 1.25rem;
            align-items: start;
        }

        @media (max-width:1100px) {
            .pos-layout {
                grid-template-columns: 1fr;
            }
        }

        /*  Products Section  */
        .pos-products {
            background: rgb(255 255 255);
            border: 1px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 12px;
            overflow: hidden;
        }

        .dark .pos-products {
            background: rgb(var(--color-gray-900, 17 24 39));
            border-color: rgb(var(--color-gray-700, 55 65 81));
        }

        .pos-products-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgb(var(--color-gray-200, 229 231 235));
            background: rgb(var(--color-gray-50, 249 250 251));
        }

        .dark .pos-products-header {
            border-color: rgb(var(--color-gray-700, 55 65 81));
            background: rgb(var(--color-gray-800, 31 41 55));
        }

        .pos-products-title {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .pos-products-icon {
            font-size: 1.5rem;
        }

        .pos-products-title h3 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
        }

        .pos-products-title p {
            margin: 0;
            font-size: .75rem;
            color: rgb(var(--color-gray-500, 107 114 128));
        }

        /*  Category Tabs  */
        .pos-category-tabs {
            display: flex;
            gap: .5rem;
            padding: .75rem 1rem;
            overflow-x: auto;
            border-bottom: 1px solid rgb(var(--color-gray-200, 229 231 235));
            scrollbar-width: none;
        }

        .dark .pos-category-tabs {
            border-color: rgb(var(--color-gray-700, 55 65 81));
        }

        .pos-category-tabs::-webkit-scrollbar {
            display: none;
        }

        .pos-cat-btn {
            padding: .35rem .875rem;
            border-radius: 9999px;
            border: 1.5px solid rgb(var(--color-gray-200, 229 231 235));
            background: transparent;
            color: rgb(var(--color-gray-600, 75 85 99));
            font-size: .8rem;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s;
        }

        .dark .pos-cat-btn {
            border-color: rgb(var(--color-gray-700, 55 65 81));
            color: rgb(var(--color-gray-400, 156 163 175));
        }

        .pos-cat-btn:hover {
            border-color: rgb(var(--color-primary-400, 251 191 36));
            color: rgb(var(--color-primary-700, 180 83 9));
        }

        .pos-cat-btn.active {
            background: rgb(var(--color-primary-600, 217 119 6));
            border-color: rgb(var(--color-primary-600, 217 119 6));
            color: rgb(255 255 255);
        }

        /*  Product Grid  */
        .pos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
            gap: .875rem;
            padding: 1rem;
            max-height: calc(100vh - 280px);
            overflow-y: auto;
        }

        .pos-card {
            border: 1px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: rgb(255 255 255);
            transition: all .15s ease;
        }

        .dark .pos-card {
            border-color: rgb(var(--color-gray-700, 55 65 81));
            background: rgb(var(--color-gray-800, 31 41 55));
        }

        .pos-card:hover:not(.is-out) {
            border-color: rgb(var(--color-primary-400, 251 191 36));
            box-shadow: 0 4px 16px rgb(var(--color-primary-400, 251 191 36) / 0.15);
            transform: translateY(-2px);
        }

        .pos-card.is-out {
            opacity: .5;
        }

        .pos-image-wrapper {
            position: relative;
            width: 100%;
            height: 100px;
            background: rgb(var(--color-gray-100, 243 244 246));
            overflow: hidden;
        }

        .dark .pos-image-wrapper {
            background: rgb(var(--color-gray-700, 55 65 81));
        }

        .pos-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pos-image-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .pos-out-badge {
            position: absolute;
            top: .4rem;
            right: .4rem;
            background: rgb(var(--color-danger-600, 220 38 38));
            color: rgb(255 255 255);
            font-size: .65rem;
            font-weight: 700;
            padding: .15rem .45rem;
            border-radius: 4px;
        }

        .pos-card-body {
            padding: .65rem .75rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: .2rem;
        }

        .pos-card-body h4 {
            margin: 0;
            font-size: .85rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .pos-price {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            color: rgb(var(--color-primary-600, 217 119 6));
        }

        .pos-stock {
            margin: 0;
            font-size: .7rem;
            color: rgb(var(--color-gray-500, 107 114 128));
        }

        .pos-stock-empty {
            color: rgb(var(--color-danger-600, 220 38 38));
            font-weight: 600;
        }

        .pos-add-btn {
            border: 0;
            border-top: 1px solid rgb(var(--color-gray-100, 243 244 246));
            padding: .55rem;
            font-size: .8rem;
            font-weight: 600;
            background: rgb(var(--color-primary-50, 255 251 235));
            color: rgb(var(--color-primary-700, 180 83 9));
            cursor: pointer;
            transition: background .15s;
        }

        .dark .pos-add-btn {
            border-color: rgb(var(--color-gray-700, 55 65 81));
            background: rgb(var(--color-primary-950, 28 25 23));
            color: rgb(var(--color-primary-300, 252 211 77));
        }

        .pos-add-btn:hover:not(:disabled) {
            background: rgb(var(--color-primary-100, 254 243 199));
        }

        .pos-add-btn:disabled {
            cursor: not-allowed;
            opacity: .5;
        }

        .pos-empty-state {
            grid-column: 1/-1;
            text-align: center;
            padding: 3rem 1rem;
            color: rgb(var(--color-gray-400, 156 163 175));
        }

        .pos-empty-icon {
            font-size: 2.5rem;
            margin-bottom: .5rem;
        }

        /*  Sidebar  */
        .pos-sidebar {
            display: flex;
            flex-direction: column;
            gap: .875rem;
            position: sticky;
            top: 1rem;
        }

        /*  Panels  */
        .pos-panel {
            background: rgb(255 255 255);
            border: 1px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 12px;
            overflow: hidden;
        }

        .dark .pos-panel {
            background: rgb(var(--color-gray-900, 17 24 39));
            border-color: rgb(var(--color-gray-700, 55 65 81));
        }

        .pos-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 1rem;
            background: rgb(var(--color-gray-50, 249 250 251));
            border-bottom: 1px solid rgb(var(--color-gray-200, 229 231 235));
        }

        .dark .pos-panel-header {
            background: rgb(var(--color-gray-800, 31 41 55));
            border-color: rgb(var(--color-gray-700, 55 65 81));
        }

        .pos-panel-title {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .875rem;
            font-weight: 700;
        }

        .pos-badge {
            background: rgb(var(--color-primary-100, 254 243 199));
            color: rgb(var(--color-primary-700, 180 83 9));
            padding: .15rem .55rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
        }

        .dark .pos-badge {
            background: rgb(var(--color-primary-950, 28 25 23));
            color: rgb(var(--color-primary-300, 252 211 77));
        }

        /*  Cart  */
        .pos-cart-list {
            max-height: 220px;
            overflow-y: auto;
            padding: .5rem .75rem;
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        .pos-cart-empty {
            text-align: center;
            padding: 1.5rem;
            color: rgb(var(--color-gray-400, 156 163 175));
            font-size: .85rem;
        }

        .pos-cart-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            background: rgb(var(--color-gray-50, 249 250 251));
            border: 1px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 8px;
            padding: .55rem .7rem;
        }

        .dark .pos-cart-row {
            background: rgb(var(--color-gray-800, 31 41 55));
            border-color: rgb(var(--color-gray-700, 55 65 81));
        }

        .pos-cart-row-info {
            flex: 1;
            min-width: 0;
        }

        .pos-cart-row-name {
            font-size: .82rem;
            font-weight: 600;
        }

        .pos-cart-row-price {
            font-size: .72rem;
            color: rgb(var(--color-gray-500, 107 114 128));
        }

        .pos-cart-row-controls {
            display: flex;
            gap: .2rem;
            align-items: center;
        }

        .qty-btn {
            width: 22px;
            height: 22px;
            border: 1px solid rgb(var(--color-gray-300, 209 213 219));
            border-radius: 4px;
            background: rgb(255 255 255);
            color: rgb(var(--color-gray-700, 55 65 81));
            cursor: pointer;
            font-weight: 700;
            font-size: .8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all .1s;
        }

        .dark .qty-btn {
            background: rgb(var(--color-gray-700, 55 65 81));
            border-color: rgb(var(--color-gray-600, 75 85 99));
            color: rgb(var(--color-gray-200, 229 231 235));
        }

        .qty-btn:hover {
            background: rgb(var(--color-gray-100, 243 244 246));
        }

        .qty-btn-remove {
            border-color: rgb(var(--color-danger-200, 254 202 202));
            color: rgb(var(--color-danger-600, 220 38 38));
        }

        .qty-btn-remove:hover {
            background: rgb(var(--color-danger-50, 254 242 242));
        }

        .qty-display {
            width: 24px;
            text-align: center;
            font-size: .82rem;
            font-weight: 700;
        }

        .pos-cart-row-value {
            font-size: .82rem;
            font-weight: 700;
            color: rgb(var(--color-primary-600, 217 119 6));
            min-width: 65px;
            text-align: right;
        }

        /*  Summary / Pricing  */
        .pos-pricing-body {
            padding: .875rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }

        .pos-field-row {
            display: flex;
            flex-direction: column;
            gap: .25rem;
        }

        .pos-field-row label {
            font-size: .72rem;
            font-weight: 600;
            color: rgb(var(--color-gray-500, 107 114 128));
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .pos-field-inline {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        /* Read-only rate badge replaces editable input */
        .pos-rate-badge {
            display: inline-flex;
            align-items: center;
            padding: .3rem .7rem;
            background: rgb(var(--color-gray-100, 243 244 246));
            border: 1px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 6px;
            font-size: .85rem;
            font-weight: 600;
            color: rgb(var(--color-gray-700, 55 65 81));
            min-width: 60px;
            justify-content: center;
        }

        .dark .pos-rate-badge {
            background: rgb(var(--color-gray-700, 55 65 81));
            border-color: rgb(var(--color-gray-600, 75 85 99));
            color: rgb(var(--color-gray-300, 209 213 219));
        }

        .pos-rate-badge--discount {
            background: rgb(var(--color-success-50, 240 253 244));
            border-color: rgb(var(--color-success-200, 187 247 208));
            color: rgb(var(--color-success-700, 21 128 61));
        }

        .dark .pos-rate-badge--discount {
            background: rgb(var(--color-success-950, 5 46 22));
            border-color: rgb(var(--color-success-800, 22 101 52));
            color: rgb(var(--color-success-400, 74 222 128));
        }

        .pos-input {
            flex: 1;
            padding: .45rem .65rem;
            border: 1px solid rgb(var(--color-gray-300, 209 213 219));
            border-radius: 6px;
            background: rgb(255 255 255);
            color: rgb(var(--color-gray-900, 17 24 39));
            font-size: .875rem;
        }

        .dark .pos-input {
            background: rgb(var(--color-gray-800, 31 41 55));
            border-color: rgb(var(--color-gray-600, 75 85 99));
            color: rgb(var(--color-gray-100, 243 244 246));
        }

        .pos-input:focus {
            outline: none;
            border-color: rgb(var(--color-primary-500, 245 158 11));
            box-shadow: 0 0 0 2px rgb(var(--color-primary-200, 254 243 199) / 0.5);
        }

        .pos-input-lg {
            padding: .65rem .75rem;
            font-size: 1rem;
            font-weight: 600;
        }

        .pos-field-result {
            font-size: .78rem;
            color: rgb(var(--color-gray-500, 107 114 128));
            min-width: 70px;
            text-align: right;
        }

        .pos-summary-block {
            background: rgb(var(--color-gray-50, 249 250 251));
            border-radius: 8px;
            padding: .65rem .875rem;
            display: flex;
            flex-direction: column;
            gap: .35rem;
            margin-top: .25rem;
        }

        .dark .pos-summary-block {
            background: rgb(var(--color-gray-800, 31 41 55));
        }

        .pos-summary-row {
            display: flex;
            justify-content: space-between;
            font-size: .82rem;
            color: rgb(var(--color-gray-600, 75 85 99));
        }

        .dark .pos-summary-row {
            color: rgb(var(--color-gray-400, 156 163 175));
        }

        .pos-total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1rem;
            font-weight: 700;
            padding-top: .5rem;
            border-top: 1.5px solid rgb(var(--color-gray-200, 229 231 235));
            margin-top: .25rem;
        }

        .dark .pos-total-row {
            border-color: rgb(var(--color-gray-700, 55 65 81));
            color: rgb(var(--color-primary-300, 252 211 77));
        }

        /*  Payment  */
        .pos-payment-body {
            padding: .875rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .875rem;
        }

        .pos-method-group {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: .4rem;
        }

        .pos-method-btn {
            padding: .55rem .35rem;
            border: 1.5px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 8px;
            background: rgb(255 255 255);
            color: rgb(var(--color-gray-600, 75 85 99));
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }

        .dark .pos-method-btn {
            background: rgb(var(--color-gray-800, 31 41 55));
            border-color: rgb(var(--color-gray-700, 55 65 81));
            color: rgb(var(--color-gray-300, 209 213 219));
        }

        .pos-method-btn.active {
            border-color: rgb(var(--color-primary-500, 245 158 11));
            background: rgb(var(--color-primary-50, 255 251 235));
            color: rgb(var(--color-primary-700, 180 83 9));
        }

        .dark .pos-method-btn.active {
            background: rgb(var(--color-primary-950, 28 25 23));
            color: rgb(var(--color-primary-300, 252 211 77));
        }

        .pos-change-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgb(var(--color-success-50, 240 253 244));
            border: 1px solid rgb(var(--color-success-200, 187 247 208));
            border-radius: 8px;
            padding: .75rem 1rem;
        }

        .dark .pos-change-box {
            background: rgb(var(--color-success-950, 5 46 22));
            border-color: rgb(var(--color-success-800, 22 101 52));
        }

        .pos-change-box span {
            font-size: .82rem;
            color: rgb(var(--color-success-700, 21 128 61));
        }

        .dark .pos-change-box span {
            color: rgb(var(--color-success-400, 74 222 128));
        }

        .pos-change-box strong {
            font-size: 1rem;
            font-weight: 700;
            color: rgb(var(--color-success-700, 21 128 61));
        }

        .dark .pos-change-box strong {
            color: rgb(var(--color-success-400, 74 222 128));
        }

        .pos-btn-checkout {
            width: 100%;
            padding: .8rem;
            border: none;
            border-radius: 8px;
            background: rgb(var(--color-success-600, 22 163 74));
            color: rgb(255 255 255);
            font-size: .9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all .15s;
        }

        .pos-btn-checkout:hover:not(:disabled) {
            background: rgb(var(--color-success-700, 21 128 61));
        }

        .pos-btn-checkout:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .pos-btn-cancel {
            width: 100%;
            padding: .65rem;
            border: 1px solid rgb(var(--color-gray-200, 229 231 235));
            border-radius: 8px;
            background: transparent;
            color: rgb(var(--color-gray-500, 107 114 128));
            font-size: .85rem;
            cursor: pointer;
            transition: all .15s;
        }

        .dark .pos-btn-cancel {
            border-color: rgb(var(--color-gray-700, 55 65 81));
            color: rgb(var(--color-gray-400, 156 163 175));
        }

        .pos-btn-cancel:hover {
            background: rgb(var(--color-gray-100, 243 244 246));
        }

        .dark .pos-btn-cancel:hover {
            background: rgb(var(--color-gray-800, 31 41 55));
        }


        .logo-light { display: inline-block; }
        .logo-dark { display: none; }

        .dark .logo-light { display: none; }
        .dark .logo-dark { display: inline-block; }

        @media print {
            body {
                background: #fff !important;
                padding: 0 !important;
            }
            .logo-light { display: inline-block !important; }
            .logo-dark { display: none !important; }

            /* Sembunyikan SEMUA, tampilkan HANYA struk berdasarkan ID */
            body * {
                visibility: hidden !important;
            }

            #receipt-modal,
            #receipt-modal * {
                visibility: visible !important;
            }

            /* Pastikan variant modal TIDAK tercetak */
            #variant-modal {
                display: none !important;
                visibility: hidden !important;
            }

            /* Posisi struk */
            #receipt-modal {
                position: fixed !important;
                inset: 0 !important;
                display: flex !important;
                align-items: flex-start !important;
                justify-content: center !important;
                background: rgb(255 255 255) !important;
                backdrop-filter: none !important;
            }

            #receipt-modal.hidden {
                display: flex !important;
                visibility: visible !important;
            }

            /* Ukuran struk ~80mm thermal printer */
            .receipt-container,
            .dark .receipt-container {
                position: relative !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 80mm !important;
                max-width: 100% !important;
                max-height: none !important;
                overflow: visible !important;
                background: rgb(255 255 255) !important;
                color: rgb(0 0 0) !important;
                font-size: 10pt !important;
                page-break-inside: avoid;
            }

            .receipt-header {
                background: rgb(255 255 255) !important;
                border-bottom: 2px solid rgb(0 0 0) !important;
                padding: .4rem .75rem !important;
            }

            .receipt-header h2 {
                color: rgb(0 0 0) !important;
                font-size: 12pt !important;
                letter-spacing: .08em !important;
            }

            .receipt-logo {
                font-size: 1.5rem !important;
            }

            .receipt-trx-num {
                color: rgb(80 80 80) !important;
                font-size: 8pt !important;
            }

            .receipt-body {
                padding: .4rem .75rem !important;
                gap: .3rem !important;
            }

            .receipt-section-label {
                color: rgb(60 60 60) !important;
                font-size: 7pt !important;
                margin: .25rem 0 .1rem !important;
            }

            .receipt-item,
            .receipt-row {
                font-size: 9pt !important;
            }

            .receipt-divider {
                border-color: rgb(0 0 0) !important;
                margin: .25rem 0 !important;
            }

            .receipt-total-row {
                border-color: rgb(0 0 0) !important;
                color: rgb(0 0 0) !important;
                font-size: 11pt !important;
                padding: .4rem 0 !important;
            }

            .text-success {
                color: rgb(0 128 60) !important;
            }

            .receipt-footer {
                padding: .25rem 0 !important;
            }

            .receipt-time {
                color: rgb(100 100 100) !important;
                font-size: 8pt !important;
            }

            .receipt-thanks {
                color: rgb(0 128 60) !important;
                font-size: 9pt !important;
            }

            .receipt-actions {
                display: none !important;
                visibility: hidden !important;
            }
        }
    </style>
    <script>
        (() => {
            // ── Cafe settings (read-only, from server) ───────────────────────────
            const CAFE_TAX_RATE = {{ $taxPercentage }};
            const CAFE_SERVICE_RATE = {{ $serviceChargePercentage }};

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
            const toastEl = document.getElementById('pos-toast');

            let selectedPaymentMethod = 'cash';
            let toastTimer = null;

            function showToast(msg, type = 'error') {
                toastEl.textContent = msg;
                toastEl.className = 'pos-toast pos-toast-' + type;
                if (toastTimer) clearTimeout(toastTimer);
                toastTimer = setTimeout(() => { toastEl.className = 'pos-toast pos-toast-hidden'; }, 3500);
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
                const taxAmt = Math.round(subtotal * CAFE_TAX_RATE / 100);
                const serviceAmt = Math.round(subtotal * CAFE_SERVICE_RATE / 100);
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
                    row.className = 'pos-cart-row';
                    const key = item._key || (item.id + '|');
                    row.innerHTML = `
                    <div class="pos-cart-row-info">
                        <div class="pos-cart-row-name">${item.name}</div>
                        <div class="pos-cart-row-price">${formatCurrency(item.price)}</div>
                    </div>
                    <div class="pos-cart-row-controls">
                        <button type="button" class="qty-btn" data-action="decrease" data-key="${key}">&minus;</button>
                        <div class="qty-display">${item.qty}</div>
                        <button type="button" class="qty-btn" data-action="increase" data-key="${key}">+</button>
                        <button type="button" class="qty-btn qty-btn-remove" data-action="remove" data-key="${key}">&times;</button>
                    </div>
                    <div class="pos-cart-row-value">${formatCurrency(item.price * item.qty)}</div>
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
                        el.innerHTML = '<span class="pos-stock-empty">Habis</span>';
                        card.classList.add('is-out');
                        const btn = card.querySelector('.pos-add-btn');
                        if (btn) { btn.disabled = true; btn.textContent = 'Stok Habis'; }
                    } else {
                        el.textContent = 'Stok: ' + serverStock;
                        card.classList.remove('is-out');
                        const btn = card.querySelector('.pos-add-btn');
                        const product = JSON.parse(btn?.getAttribute('data-product') || '{}');
                        if (btn) {
                            btn.disabled = false;
                            btn.textContent = (product.has_variants && product.variants && Object.keys(product.variants).length > 0)
                                ? 'Pilih Opsi' : '+ Tambah';
                        }
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
                    el.innerHTML = '<span class="pos-stock-empty">Habis</span>';
                    card.classList.add('is-out');
                    const btn = card.querySelector('.pos-add-btn');
                    if (btn) { btn.disabled = true; btn.textContent = 'Stok Habis'; }
                } else {
                    el.textContent = 'Stok: ' + stockCache[productId];
                    card.classList.remove('is-out');
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
                    groupEl.innerHTML = `<div class="variant-group-label">${label}</div><div class="variant-options" data-group="${groupKey}"></div>`;
                    const optWrapper = groupEl.querySelector('.variant-options');
                    options.forEach(opt => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'variant-opt';
                        btn.textContent = opt;
                        btn.addEventListener('click', () => {
                            optWrapper.querySelectorAll('.variant-opt').forEach(b => b.classList.remove('selected'));
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

                checkoutBtn.disabled = true;
                checkoutBtn.textContent = 'Memproses...';

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

                    let data;
                    const contentType = res.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    } else {
                        const text = await res.text();
                        console.error('Non-JSON response:', text);
                        showToast('Server error. Cek console untuk detail.');
                        checkoutBtn.disabled = false;
                        checkoutBtn.textContent = 'Proses Pembayaran';
                        return;
                    }

                    if (!res.ok) {
                        showToast('Checkout gagal: ' + (data.message || 'Terjadi kesalahan.'));
                        checkoutBtn.disabled = false;
                        checkoutBtn.textContent = 'Proses Pembayaran';
                        return;
                    }

                    // Show receipt
                    document.getElementById('receipt-trx-num').textContent = data.transaction_number;
                    document.getElementById('receipt-subtotal').textContent = formatCurrency(subtotal);
                    document.getElementById('receipt-tax-rate').textContent = CAFE_TAX_RATE;
                    document.getElementById('receipt-tax-amt').textContent = formatCurrency(taxAmt);
                    document.getElementById('receipt-service-rate').textContent = CAFE_SERVICE_RATE;
                    document.getElementById('receipt-service-amt').textContent = formatCurrency(serviceAmt);
                    document.getElementById('receipt-discount-amt').textContent = discountAmt > 0 ? '-' + formatCurrency(discountAmt) : formatCurrency(0);
                    document.getElementById('receipt-total').textContent = formatCurrency(total);
                    document.getElementById('receipt-payment-method').textContent = selectedPaymentMethod.toUpperCase();
                    document.getElementById('receipt-paid').textContent = formatCurrency(paidAmt);
                    document.getElementById('receipt-change').textContent = formatCurrency(change);
                    document.getElementById('receipt-time').textContent = new Date().toLocaleString('id-ID');

                    const itemsList = document.getElementById('receipt-items-list');
                    itemsList.innerHTML = '';
                    cart.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'receipt-item';
                        itemDiv.innerHTML = `<span>${item.name} x ${item.qty}</span><strong>${formatCurrency(item.price * item.qty)}</strong>`;
                        itemsList.appendChild(itemDiv);
                    });

                    receiptModal.classList.remove('hidden');

                    // Patch data-stock attributes before resetting cache,
                    // so initStockCache() reads the correct post-sale values.
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
                } catch (e) {
                    console.error(e);
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
                    const cat = btn.getAttribute('data-cat');
                    document.querySelectorAll('.pos-card').forEach(card => {
                        card.style.display = (cat === 'all' || card.getAttribute('data-category-id') == cat) ? '' : 'none';
                    });
                });
            });

            // Initial render
            renderCart();
        })();
    </script>
</x-filament-panels::page>