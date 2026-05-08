<x-filament-panels::page>
    <div class="pos-layout">
        <section class="pos-products">
            <div class="pos-section-head">
                <h3>Daftar Produk</h3>
                <span>{{ count($products) }} item</span>
            </div>

            <div class="pos-grid">
                @forelse($products as $product)
                    @php
                        $isOutOfStock = (int) ($product['stock'] ?? 0) <= 0;
                    @endphp

                    <article class="pos-card {{ $isOutOfStock ? 'is-out' : '' }}">
                        @if(!empty($product['image_url']))
                            <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="pos-image">
                        @else
                            <div class="pos-image-fallback">No Image</div>
                        @endif

                        <div class="pos-card-content">
                            <h4>{{ $product['name'] }}</h4>
                            <p>SKU: {{ $product['sku'] ?? '-' }}</p>
                            <p>Stok: {{ (int) ($product['stock'] ?? 0) }}</p>
                            <strong>Rp {{ number_format((int) $product['price'], 0, ',', '.') }}</strong>
                        </div>

                        <button
                            type="button"
                            class="pos-add-btn add-to-cart"
                            data-product='@json($product)'
                            @disabled($isOutOfStock)
                        >
                            {{ $isOutOfStock ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                        </button>
                    </article>
                @empty
                    <div class="pos-empty">Belum ada produk aktif untuk cafe ini.</div>
                @endforelse
            </div>
        </section>

        <aside class="pos-cart">
            <div class="pos-section-head">
                <h3>Keranjang</h3>
            </div>

            <div id="cart-list" class="pos-cart-list">
                <div class="pos-empty">Keranjang masih kosong.</div>
            </div>

            <div class="pos-cart-summary">
                <div>
                    <span>Subtotal</span>
                    <strong id="cart-subtotal">Rp 0</strong>
                </div>
                <button id="checkout-btn" class="pos-checkout-btn">Checkout</button>
            </div>
            <p class="pos-note">Checkout memakai endpoint POS yang tersimpan ke transaksi dan pembayaran.</p>
        </aside>
    </div>

    <style>
        .pos-layout {
            display: grid;
            gap: 1rem;
            grid-template-columns: minmax(0, 2fr) minmax(300px, 1fr);
        }

        .pos-products,
        .pos-cart {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.02);
            overflow: hidden;
        }

        .pos-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .pos-section-head h3 {
            margin: 0;
            font-size: 1rem;
        }

        .pos-section-head span {
            opacity: 0.75;
            font-size: 0.85rem;
        }

        .pos-grid {
            padding: 1rem;
            display: grid;
            gap: 0.8rem;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        }

        .pos-card {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 0.9rem;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: rgba(15, 23, 42, 0.2);
        }

        .pos-card.is-out {
            opacity: 0.7;
        }

        .pos-image,
        .pos-image-fallback {
            width: 100%;
            height: 108px;
            object-fit: cover;
            display: grid;
            place-items: center;
            background: rgba(51, 65, 85, 0.45);
        }

        .pos-card-content {
            padding: 0.8rem;
            display: grid;
            gap: 0.2rem;
            flex: 1;
        }

        .pos-card-content h4 {
            margin: 0;
            font-size: 0.95rem;
        }

        .pos-card-content p {
            margin: 0;
            font-size: 0.78rem;
            opacity: 0.76;
        }

        .pos-card-content strong {
            font-size: 0.9rem;
        }

        .pos-add-btn {
            border: 0;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            padding: 0.65rem 0.8rem;
            font-weight: 600;
            background: rgba(14, 116, 144, 0.22);
            color: inherit;
            cursor: pointer;
        }

        .pos-add-btn:disabled {
            cursor: not-allowed;
            opacity: 0.5;
        }

        .pos-cart {
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        .pos-cart-list {
            padding: 1rem;
            display: grid;
            gap: 0.65rem;
            align-content: start;
        }

        .pos-cart-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 0.7rem;
            padding: 0.55rem 0.6rem;
        }

        .pos-cart-row small {
            display: block;
            opacity: 0.72;
        }

        .pos-cart-summary {
            padding: 1rem;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            display: grid;
            gap: 0.7rem;
        }

        .pos-cart-summary > div {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pos-checkout-btn {
            border: 0;
            border-radius: 0.75rem;
            padding: 0.68rem 0.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.8), rgba(16, 185, 129, 0.9));
            color: #032229;
            cursor: pointer;
        }

        .pos-empty,
        .pos-note {
            opacity: 0.72;
            font-size: 0.85rem;
        }

        .pos-note {
            padding: 0 1rem 1rem;
            margin: 0;
        }

        @media (max-width: 1024px) {
            .pos-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-filament-panels::page>

<script>
    (() => {
        const cart = [];
        const cartList = document.getElementById('cart-list');
        const subtotalEl = document.getElementById('cart-subtotal');

        function renderCart() {
            cartList.innerHTML = '';
            if (cart.length === 0) {
                cartList.innerHTML = '<div class="pos-empty">Keranjang masih kosong.</div>';
                subtotalEl.textContent = 'Rp 0';
                return;
            }

            let subtotal = 0;
            cart.forEach((item) => {
                subtotal += item.price * item.qty;
                const row = document.createElement('div');
                row.className = 'pos-cart-row';
                row.innerHTML = `<div><strong>${item.name}</strong><small>${item.qty} x Rp ${item.price.toLocaleString('id-ID')}</small></div><div><strong>Rp ${(item.price * item.qty).toLocaleString('id-ID')}</strong></div>`;
                cartList.appendChild(row);
            });

            subtotalEl.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        }

        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const product = JSON.parse(btn.getAttribute('data-product'));
                const existing = cart.find(c => c.id === product.id);
                if (existing) existing.qty += 1; else cart.push({id: product.id, name: product.name, price: Number(product.price), qty: 1});
                renderCart();
            });
        });

        document.getElementById('checkout-btn').addEventListener('click', async () => {
            if (cart.length === 0) {
                alert('Keranjang masih kosong.');
                return;
            }

            const total = cart.reduce((s,i)=>s+i.price*i.qty,0);
            const proceed = confirm('Lanjutkan pembayaran Rp ' + total.toLocaleString('id-ID') + '?');
            if (!proceed) return;

            try {
                const res = await fetch('{{ route('pos.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ cart, payment_method: 'cash' }),
                });

                if (!res.ok) {
                    const err = await res.json();
                    alert('Checkout gagal: ' + (err.message || 'Unknown'));
                    return;
                }

                const data = await res.json();
                alert('Checkout berhasil. Nomor transaksi: ' + data.transaction_number);
                cart.length = 0;
                renderCart();
            } catch (e) {
                console.error(e);
                alert('Terjadi error saat checkout.');
            }
        });

        renderCart();
    })();
</script>
