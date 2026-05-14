<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $transaction->transaction_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 0; padding: 20px; display: flex; justify-content: center; background: #f3f4f6; }
        
        .receipt-container { background: #fff; color: #111827; border-radius: 16px; box-shadow: 0 24px 64px rgba(0,0,0,.1); width: min(460px, 94vw); max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; }
        .receipt-header { text-align: center; padding: 1.75rem 2rem 1.25rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .receipt-logo { font-size: 2.5rem; margin-bottom: .5rem; }
        .logo-light { display: inline-block; }
        .logo-dark { display: none; }
        .receipt-header h2 { margin: 0; font-size: 1.1rem; font-weight: 700; letter-spacing: .15em; color: #374151; }
        .receipt-trx-num { margin: .35rem 0 0; font-size: .8rem; color: #9ca3af; font-family: monospace; }
        
        .receipt-body { flex: 1; overflow-y: auto; padding: 1.25rem 1.75rem; display: flex; flex-direction: column; gap: .5rem; }
        .receipt-section-label { font-size: .7rem; font-weight: 700; letter-spacing: .1em; color: #6b7280; text-transform: uppercase; margin: .5rem 0 .25rem; }
        .receipt-items-list { display: flex; flex-direction: column; gap: .4rem; }
        .receipt-item { display: flex; justify-content: space-between; font-size: .875rem; }
        .receipt-divider { border: none; border-top: 1px dashed #d1d5db; margin: .5rem 0; }
        .receipt-row { display: flex; justify-content: space-between; font-size: .875rem; }
        .text-success { color: #16a34a; }
        .receipt-total-row { display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 700; padding: .75rem 0; border-top: 2px solid #e5e7eb; border-bottom: 2px solid #e5e7eb; margin: .25rem 0; }
        
        .receipt-footer { text-align: center; margin-top: .75rem; }
        .receipt-time { font-size: .75rem; color: #9ca3af; margin: 0 0 .25rem; }
        .receipt-thanks { font-size: .875rem; color: #16a34a; font-weight: 600; margin: 0; }
        
        .receipt-actions { display: grid; grid-template-columns: 1fr; gap: .75rem; padding: 1rem 1.75rem 1.5rem; }
        .receipt-btn { padding: .7rem 1rem; border-radius: 8px; font-weight: 600; font-size: .9rem; cursor: pointer; transition: all .15s; }
        .receipt-btn-primary { background: #d97706; border: none; color: #fff; }
        .receipt-btn-primary:hover { background: #b45309; }

        @media (prefers-color-scheme: dark) {
            body { background: #111827; }
            .logo-light { display: none; }
            .logo-dark { display: inline-block; }
            
            .receipt-container { 
                background: #1f2937; 
                color: #f3f4f6; 
                box-shadow: 0 24px 64px rgba(0,0,0,.5); 
            }
            
            .receipt-header { 
                background: #111827; 
                border-bottom-color: #374151; 
            }
            
            .receipt-header h2 { color: #e5e7eb; }
            .receipt-trx-num { color: #6b7280; }
            
            .receipt-body { background: #1f2937; color: #f3f4f6; }
            .receipt-section-label { color: #9ca3af; }
            .receipt-item { color: #f3f4f6; }
            .receipt-divider { border-top-color: #4b5563; }
            .receipt-row { color: #f3f4f6; }
            .text-success { color: #4ade80; }
            .receipt-total-row { 
                border-top-color: #374151; 
                border-bottom-color: #374151; 
                color: #f3f4f6; 
            }
            
            .receipt-time { color: #9ca3af; }
            .receipt-thanks { color: #4ade80; }
        }

        @media print {
            body { background: #fff; padding: 0; }
            .logo-light { display: inline-block !important; }
            .logo-dark { display: none !important; }
            .receipt-container {
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 80mm !important;
                max-width: 100% !important;
                max-height: none !important;
                overflow: visible !important;
                background: #fff !important;
                color: #000 !important;
                font-size: 10pt !important;
                page-break-inside: avoid;
            }
            .receipt-header { background: #fff !important; border-bottom: 2px solid #000 !important; padding: .4rem .75rem !important; }
            .receipt-header h2 { color: #000 !important; font-size: 12pt !important; letter-spacing: .08em !important; }
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
            .receipt-actions { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="receipt-logo">
                @if($transaction->toko && $transaction->toko->logo_url)
                    <img src="{{ asset('storage/' . $transaction->toko->logo_url) }}" alt="Logo Toko" style="max-height: 48px; max-width: 100%;">
                @else
                    <img src="{{ asset('/default-logo/light-mode.png') }}" class="logo-light" alt="Logo Default" style="max-height: 48px; max-width: 100%;">
                    <img src="{{ asset('/default-logo/dark-mode.png') }}" class="logo-dark" alt="Logo Default" style="max-height: 48px; max-width: 100%;">
                @endif
            </div>
            <h2>{{ strtoupper($transaction->toko->name ?? 'TOKO') }}</h2>
            <p class="receipt-trx-num">{{ $transaction->transaction_number }}</p>
        </div>

        <div class="receipt-body">
            <div class="receipt-section-label">Detail Pesanan</div>
            <div class="receipt-items-list">
                @foreach($transaction->items as $item)
                    <div class="receipt-item">
                        <span>{{ $item->product->name }} x {{ $item->quantity }}</span>
                        <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span>Subtotal</span>
                <strong>Rp {{ number_format($transaction->total_amount - $transaction->tax_amount + $transaction->discount_amount, 0, ',', '.') }}</strong>
            </div>
            @if($transaction->tax_amount > 0)
                <div class="receipt-row">
                    <span>Pajak</span>
                    <strong>Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</strong>
                </div>
            @endif
            @if($transaction->discount_amount > 0)
                <div class="receipt-row">
                    <span>Diskon</span>
                    <strong class="text-success">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</strong>
                </div>
            @endif

            <div class="receipt-total-row">
                <span>TOTAL</span>
                <strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-section-label">Pembayaran</div>
            <div class="receipt-row">
                <span>Metode</span>
                <strong>{{ strtoupper($transaction->payments->first()->paymentMethod->name ?? 'CASH') }}</strong>
            </div>
            <div class="receipt-row">
                <span>Jumlah Bayar</span>
                <strong>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</strong>
            </div>
            <div class="receipt-row">
                <span>Kembalian</span>
                <strong class="text-success">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</strong>
            </div>

            <div class="receipt-footer">
                <p class="receipt-time">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                <p class="receipt-thanks">Terima kasih atas kunjungan Anda</p>
            </div>
        </div>

        <div class="receipt-actions">
            <button class="receipt-btn receipt-btn-primary" onclick="window.print()">Cetak Struk</button>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
