<x-filament-panels::page>
    <style>
        #hardware-guide-app {
            --hg-bg: #ffffff;
            --hg-bg-muted: #f9fafb;
            --hg-border: #e5e7eb;
            --hg-text: #111827;
            --hg-text-muted: #6b7280;
            --hg-input-bg: #f9fafb;
            --hg-input-border: #d1d5db;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            color: var(--hg-text);
        }

        .dark #hardware-guide-app {
            --hg-bg: #111827;
            --hg-bg-muted: #1f2937;
            --hg-border: #374151;
            --hg-text: #f9fafb;
            --hg-text-muted: #9ca3af;
            --hg-input-bg: #1f2937;
            --hg-input-border: #4b5563;
        }

        #hardware-guide-app .hg-banner {
            background: linear-gradient(135deg, #059669 0%, #0d9488 100%);
            color: #ffffff;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 10px 25px rgba(5, 150, 105, 0.2);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        #hardware-guide-app .hg-banner h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            color: #ffffff;
        }

        #hardware-guide-app .hg-banner p {
            margin: 0.35rem 0 0;
            font-size: 0.875rem;
            color: #d1fae5;
            max-width: 42rem;
        }

        #hardware-guide-app .hg-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        #hardware-guide-app .hg-tab {
            padding: 0.55rem 0.95rem;
            border-radius: 0.75rem;
            font-size: 0.78rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }

        #hardware-guide-app .hg-tab:hover {
            background: rgba(255, 255, 255, 0.28);
        }

        #hardware-guide-app .hg-tab.is-active {
            background: #ffffff;
            color: #047857;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        #hardware-guide-app .hg-grid-2 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.5rem;
        }

        #hardware-guide-app .hg-grid-3 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (min-width: 1024px) {
            #hardware-guide-app .hg-grid-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            #hardware-guide-app .hg-grid-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        #hardware-guide-app .hg-card {
            background: var(--hg-bg);
            border: 1px solid var(--hg-border);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        #hardware-guide-app .hg-card-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        #hardware-guide-app .hg-card h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--hg-text);
        }

        #hardware-guide-app .hg-sub {
            margin: 0.25rem 0 0;
            font-size: 0.8rem;
            color: var(--hg-text-muted);
            line-height: 1.5;
        }

        #hardware-guide-app .hg-badge {
            padding: 0.25rem 0.7rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        #hardware-guide-app .hg-badge-green {
            background: rgba(16, 185, 129, 0.15);
            color: #059669;
        }

        #hardware-guide-app .hg-badge-blue {
            background: rgba(14, 165, 233, 0.15);
            color: #0284c7;
        }

        #hardware-guide-app .hg-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--hg-text-muted);
            margin-bottom: 0.4rem;
        }

        #hardware-guide-app .hg-input,
        #hardware-guide-app .hg-select {
            width: 100%;
            border-radius: 0.75rem;
            border: 1px solid var(--hg-input-border);
            background: var(--hg-input-bg);
            color: var(--hg-text);
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            box-sizing: border-box;
        }

        #hardware-guide-app .hg-row-2 {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 640px) {
            #hardware-guide-app .hg-row-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        #hardware-guide-app .hg-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        #hardware-guide-app .hg-btn {
            padding: 0.6rem 0.95rem;
            border-radius: 0.75rem;
            border: none;
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.15s ease;
            white-space: nowrap;
        }

        #hardware-guide-app .hg-btn:hover {
            opacity: 0.9;
        }

        #hardware-guide-app .hg-btn-emerald { background: #059669; }
        #hardware-guide-app .hg-btn-sky { background: #0284c7; }
        #hardware-guide-app .hg-btn-indigo { background: #4f46e5; }
        #hardware-guide-app .hg-btn-amber { background: #d97706; }

        #hardware-guide-app .hg-box {
            border-radius: 0.75rem;
            background: var(--hg-bg-muted);
            border: 1px solid var(--hg-border);
            padding: 0.95rem 1rem;
            font-size: 0.8rem;
            line-height: 1.55;
            color: var(--hg-text-muted);
        }

        #hardware-guide-app .hg-box-dashed {
            border-style: dashed;
        }

        #hardware-guide-app .hg-box-emerald {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.3);
        }

        #hardware-guide-app .hg-feedback {
            display: none;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        #hardware-guide-app .hg-feedback.is-success {
            display: block;
            background: rgba(16, 185, 129, 0.14);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.35);
        }

        #hardware-guide-app .hg-feedback.is-error {
            display: block;
            background: rgba(239, 68, 68, 0.14);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }

        .dark #hardware-guide-app .hg-feedback.is-success { color: #6ee7b7; }
        .dark #hardware-guide-app .hg-feedback.is-error { color: #fca5a5; }

        #hardware-guide-app .hg-caps {
            display: flex;
            flex-wrap: wrap;
            gap: 0.85rem;
            margin-top: 0.4rem;
        }

        #hardware-guide-app .hg-list {
            margin: 0;
            padding-left: 1.2rem;
            font-size: 0.82rem;
            line-height: 1.65;
            color: var(--hg-text-muted);
        }

        #hardware-guide-app .hg-list li + li {
            margin-top: 0.35rem;
        }

        #hardware-guide-app .hg-list strong,
        #hardware-guide-app .hg-box strong {
            color: var(--hg-text);
        }

        #hardware-guide-app .hg-scan-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--hg-text);
            margin: 0.35rem 0;
        }

        #hardware-guide-app .hg-title-accent {
            font-size: 0.9rem;
            font-weight: 800;
            color: #059669;
        }

        #hardware-guide-app .hg-title-amber { color: #d97706; }
        #hardware-guide-app .hg-title-indigo { color: #4f46e5; }
        #hardware-guide-app .hg-title-sky { color: #0284c7; }

        #test-receipt-print-area {
            display: none;
        }

        @media print {
            body * { visibility: hidden !important; }
            #test-receipt-print-area,
            #test-receipt-print-area * { visibility: visible !important; }
            #test-receipt-print-area {
                display: block !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
            }
        }
    </style>

    <div id="hardware-guide-app" x-data="{ tab: 'koneksi' }">
        <!-- Header Banner -->
        <div class="hg-banner">
            <div>
                <h2>Pusat Panduan &amp; Koneksi Perangkat Kasir</h2>
                <p>
                    Sambungkan alat <strong>Barcode Scanner</strong> &amp; <strong>Printer Struk Thermal (58mm / 80mm)</strong> langsung dari browser, lalu lakukan tes scan &amp; tes cetak di sini.
                </p>
            </div>
            <div class="hg-tabs">
                <button type="button" @click="tab = 'koneksi'" :class="tab === 'koneksi' ? 'hg-tab is-active' : 'hg-tab'">
                    ⚡ Koneksi &amp; Tes Alat
                </button>
                <button type="button" @click="tab = 'scanner'" :class="tab === 'scanner' ? 'hg-tab is-active' : 'hg-tab'">
                    📷 Panduan Scanner &amp; EAN-13
                </button>
                <button type="button" @click="tab = 'printer'" :class="tab === 'printer' ? 'hg-tab is-active' : 'hg-tab'">
                    🖨️ Panduan Printer Struk
                </button>
                <button type="button" @click="tab = 'faq'" :class="tab === 'faq' ? 'hg-tab is-active' : 'hg-tab'">
                    🛠️ Troubleshooting
                </button>
            </div>
        </div>

        <!-- TAB 1: KONEKSI & TES ALAT LANGSUNG DI WEB -->
        <div x-show="tab === 'koneksi'" class="hg-grid-2">
            <!-- Panel Koneksi Printer Struk -->
            <div class="hg-card">
                <div class="hg-card-head">
                    <div>
                        <h3>1. Penyambungan Printer Struk Thermal</h3>
                        <p class="hg-sub">
                            Pengaturan ini otomatis tersimpan di browser dan langsung dipakai saat cetak struk di halaman Kasir.
                        </p>
                    </div>
                    <span id="printer-status-badge" class="hg-badge hg-badge-green">
                        Mode Browser
                    </span>
                </div>

                <div>
                    <label for="hw-printer-mode" class="hg-label">Metode Cetak Struk</label>
                    <select id="hw-printer-mode" class="hg-select">
                        <option value="browser">Cetak Browser / Driver OS (USB / WiFi — Paling Stabil)</option>
                        <option value="web_serial">USB Langsung via Web Serial (ESC/POS — Chrome/Edge Desktop)</option>
                        <option value="web_bluetooth">Bluetooth Langsung via Web Bluetooth (ESC/POS — Chrome Android/Laptop)</option>
                    </select>
                </div>

                <div class="hg-row-2">
                    <div>
                        <label for="hw-paper-width" class="hg-label">Ukuran Kertas Thermal</label>
                        <select id="hw-paper-width" class="hg-select">
                            <option value="58mm">58mm (Thermal Mini / Portable)</option>
                            <option value="80mm">80mm (Thermal Kasir Standar)</option>
                        </select>
                    </div>

                    <div>
                        <label for="hw-serial-baud" class="hg-label">Baud Rate (Web Serial)</label>
                        <select id="hw-serial-baud" class="hg-select">
                            <option value="9600">9600 (Umum / Default)</option>
                            <option value="19200">19200</option>
                            <option value="38400">38400</option>
                            <option value="115200">115200</option>
                        </select>
                    </div>
                </div>

                <div class="hg-actions">
                    <button type="button" id="btn-save-printer" class="hg-btn hg-btn-emerald">💾 Simpan Pengaturan</button>
                    <button type="button" id="btn-connect-serial" class="hg-btn hg-btn-sky">🔌 Hubungkan USB (Web Serial)</button>
                    <button type="button" id="btn-connect-bt" class="hg-btn hg-btn-indigo">📶 Hubungkan Bluetooth</button>
                    <button type="button" id="btn-test-print" class="hg-btn hg-btn-amber">🖨️ Tes Cetak Struk</button>
                </div>

                <div id="printer-feedback" class="hg-feedback"></div>

                <div class="hg-box">
                    <strong>Status Dukungan Browser Anda:</strong>
                    <div class="hg-caps">
                        <span id="cap-https">HTTPS: mengecek...</span>
                        <span id="cap-serial">Web Serial: mengecek...</span>
                        <span id="cap-bluetooth">Web Bluetooth: mengecek...</span>
                        <span id="cap-camera">Kamera: mengecek...</span>
                    </div>
                </div>
            </div>

            <!-- Panel Tes Barcode Scanner -->
            <div class="hg-card">
                <div class="hg-card-head">
                    <div>
                        <h3>2. Tes &amp; Kalibrasi Barcode Scanner</h3>
                        <p class="hg-sub">
                            Colokkan scanner USB / Wireless / Bluetooth Anda, lalu langsung scan barcode produk apa saja (EAN-13 atau Code 128).
                        </p>
                    </div>
                    <span class="hg-badge hg-badge-blue">
                        Auto-Capture Aktif
                    </span>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="hw-scanner-input" class="hg-input" style="font-family: monospace;"
                        placeholder="Arahkan scanner &amp; tembak barcode, atau ketik kode lalu Enter...">
                    <button type="button" id="btn-test-camera-scan" class="hg-btn hg-btn-emerald">
                        📷 Tes Kamera
                    </button>
                </div>

                <div id="guide-camera-viewport" style="display: none; border-radius: 0.75rem; overflow: hidden; border: 1px solid var(--hg-border); max-width: 360px; margin: 0 auto;"></div>

                <div id="scan-result-box" class="hg-box hg-box-dashed">
                    <div class="hg-label" style="margin-bottom: 0;">Hasil Scan Terakhir</div>
                    <div id="scan-result-code" class="hg-scan-code">Belum ada barcode di-scan</div>
                    <div class="hg-row-2" style="margin: 0.4rem 0;">
                        <div>Format: <strong id="scan-result-format">-</strong></div>
                        <div>Sumber: <strong id="scan-result-source">-</strong></div>
                    </div>
                    <div id="scan-result-product" style="font-weight: 600;">
                        Coba scan salah satu produk di toko Anda untuk memastikan kecocokan SKU.
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: PANDUAN BARCODE SCANNER & EAN-13 -->
        <div x-show="tab === 'scanner'" x-cloak style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="hg-grid-3">
                <div class="hg-card">
                    <div class="hg-title-accent">A. Scanner USB Kabel / Wireless 2.4GHz</div>
                    <ol class="hg-list">
                        <li>Colokkan kabel USB atau dongle receiver 2.4GHz ke komputer/laptop.</li>
                        <li>Tunggu 5 detik hingga terdengar bunyi <em>beep</em> (Plug &amp; Play, tidak perlu driver).</li>
                        <li>Pastikan scanner berada di mode <strong>HID Keyboard</strong> dengan akhiran <strong>Enter / CR</strong> aktif (biasanya sudah default pabrik).</li>
                        <li>Buka halaman <strong>Kasir (POS)</strong> lalu langsung scan produk — tidak perlu klik kolom pencarian terlebih dahulu karena sistem sudah mendukung <em>Auto-Capture</em>.</li>
                    </ol>
                </div>

                <div class="hg-card">
                    <div class="hg-title-accent">B. Scanner Bluetooth (HP / Tablet / Laptop)</div>
                    <ol class="hg-list">
                        <li>Nyalakan scanner Bluetooth dan masuk ke mode pairing (scan barcode <em>Bluetooth HID</em> di buku manual scanner).</li>
                        <li>Buka pengaturan Bluetooth di HP/Tablet/Laptop lalu hubungkan ke nama scanner.</li>
                        <li>Buka menu <strong>Panduan Alat → Koneksi &amp; Tes Alat</strong> untuk memastikan hasil scan terbaca otomatis.</li>
                        <li>Siap digunakan di halaman <strong>Kasir (POS)</strong>.</li>
                    </ol>
                </div>

                <div class="hg-card">
                    <div class="hg-title-accent">C. Scan Menggunakan Kamera HP / Webcam</div>
                    <ol class="hg-list">
                        <li>Pastikan alamat website dibuka menggunakan <strong>HTTPS</strong> (ikon gembok di browser).</li>
                        <li>Di halaman <strong>Kasir (POS)</strong>, klik tombol <strong>ikon Kamera</strong> di sebelah kolom pencarian.</li>
                        <li>Klik <strong>Izinkan (Allow)</strong> saat browser meminta akses kamera.</li>
                        <li>Arahkan barcode (EAN-13 atau Code 128) ke area kamera hingga produk otomatis masuk keranjang.</li>
                    </ol>
                </div>
            </div>

            <div class="hg-card">
                <h3>Memilih Format Barcode: Code 128 (Versi Lama) vs EAN-13</h3>
                <div class="hg-row-2">
                    <div class="hg-box">
                        <div style="font-weight: 700; margin-bottom: 0.3rem; color: var(--hg-text);">Code 128 (Versi Lama)</div>
                        Mendukung kombinasi huruf, angka, dan tanda hubung (contoh: <code>SKU-001</code>, <code>KOPI-AREN-L</code>). Cocok untuk produk buatan sendiri atau kode internal toko.
                    </div>
                    <div class="hg-box hg-box-emerald">
                        <div style="font-weight: 700; margin-bottom: 0.3rem; color: #059669;">EAN-13 (Standar Retail 13 Digit)</div>
                        Standar barcode retail internasional/Indonesia (prefix <code>899...</code>, 13 digit angka). Cocok untuk produk kemasan pabrik atau label standar minimarket. Di form Produk, pilih <strong>EAN-13</strong> lalu klik tombol <strong>Generate EAN-13</strong> jika belum punya kode.
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: PANDUAN PRINTER STRUK -->
        <div x-show="tab === 'printer'" x-cloak class="hg-grid-3">
            <div class="hg-card">
                <div class="hg-title-accent hg-title-amber">1. Printer Thermal USB (Windows / Mac)</div>
                <ol class="hg-list">
                    <li>Hubungkan kabel USB printer thermal (58mm atau 80mm) dan install driver bawaan printer.</li>
                    <li>Jadikan printer thermal sebagai <strong>Default Printer</strong> di pengaturan OS.</li>
                    <li>Di tab <strong>Koneksi &amp; Tes Alat</strong>, pilih ukuran kertas (<strong>58mm</strong> / <strong>80mm</strong>) lalu klik <strong>Simpan Pengaturan</strong>.</li>
                    <li>Saat dialog print browser muncul pertama kali: pilih printer thermal Anda, atur <strong>Margins: None</strong>, matikan <strong>Headers and footers</strong>, dan aktifkan <strong>Background graphics</strong>.</li>
                    <li><strong>Tips Cetak Otomatis Tanpa Pop-up:</strong> Jalankan shortcut Google Chrome dengan flag <code>--kiosk-printing</code>.</li>
                </ol>
            </div>

            <div class="hg-card">
                <div class="hg-title-accent hg-title-indigo">2. Printer Thermal Bluetooth (Android / Laptop)</div>
                <ol class="hg-list">
                    <li>Nyalakan printer Bluetooth dan lakukan pairing di pengaturan Bluetooth perangkat (PIN standar biasanya <code>0000</code> atau <code>1234</code>).</li>
                    <li><strong>Cara 1 (Langsung di Web — Tanpa Aplikasi Tambahan):</strong> Buka tab <strong>Koneksi &amp; Tes Alat</strong> → pilih <strong>Web Bluetooth</strong> → klik <strong>Hubungkan Bluetooth</strong> → klik <strong>Tes Cetak Struk</strong>.</li>
                    <li><strong>Cara 2 (Android via RawBT):</strong> Jika printer Bluetooth model klasik (non-BLE), install aplikasi <strong>RawBT Print Service</strong> di Play Store, hubungkan printer di RawBT, lalu gunakan mode <strong>Cetak Browser</strong>.</li>
                </ol>
            </div>

            <div class="hg-card">
                <div class="hg-title-accent hg-title-sky">3. Printer USB Langsung (Web Serial ESC/POS)</div>
                <ol class="hg-list">
                    <li>Cocok untuk printer thermal USB/Serial yang terbaca sebagai port COM di Windows/Mac/Linux.</li>
                    <li>Gunakan browser <strong>Google Chrome</strong> atau <strong>Microsoft Edge</strong>.</li>
                    <li>Di tab <strong>Koneksi &amp; Tes Alat</strong>, pilih metode <strong>USB Langsung via Web Serial</strong>.</li>
                    <li>Klik <strong>Hubungkan USB (Web Serial)</strong>, pilih port printer Anda, lalu klik <strong>Tes Cetak Struk</strong>.</li>
                    <li>Cetak struk di Kasir akan langsung mengirim perintah ESC/POS tanpa membuka dialog print browser.</li>
                </ol>
            </div>
        </div>

        <!-- TAB 4: TROUBLESHOOTING -->
        <div x-show="tab === 'faq'" x-cloak class="hg-card">
            <h3>Troubleshooting Cepat</h3>
            <div class="hg-box">
                <div style="font-weight: 700; color: var(--hg-text); margin-bottom: 0.25rem;">Scanner bunyi "beep", tapi produk tidak masuk keranjang?</div>
                1) Cek di menu Produk apakah SKU produk sama persis dengan kode yang discan (tes di tab <em>Koneksi &amp; Tes Alat</em>). 2) Pastikan produk berstatus <strong>Aktif</strong> dan stok &gt; 0. 3) Pastikan scanner mengirim tombol <strong>Enter (CR)</strong> di akhir scan.
            </div>
            <div class="hg-box">
                <div style="font-weight: 700; color: var(--hg-text); margin-bottom: 0.25rem;">Struk tercetak terpotong atau kertas keluar terlalu panjang?</div>
                Pastikan ukuran kertas di tab <strong>Koneksi &amp; Tes Alat</strong> sudah sesuai fisik printer Anda (<strong>58mm</strong> atau <strong>80mm</strong>), dan pada dialog cetak browser atur <strong>Margins: None</strong> serta matikan <strong>Headers and footers</strong>.
            </div>
            <div class="hg-box">
                <div style="font-weight: 700; color: var(--hg-text); margin-bottom: 0.25rem;">Kamera scanner atau Web Bluetooth tidak berfungsi?</div>
                Browser mewajibkan koneksi <strong>HTTPS</strong> untuk akses Kamera, Web Bluetooth, dan Web Serial. Pastikan URL website diawali <code>https://</code> dan izin kamera/Bluetooth tidak diblokir.
            </div>
        </div>

        <!-- Hidden Printable Sample Receipt for Browser Test Print -->
        <div id="test-receipt-print-area">
            <div class="receipt-container" style="margin: 0 auto; padding: 8px; font-family: monospace; color: #000; background: #fff;">
                <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px;">
                    <div style="font-weight: 700; font-size: 13px;">{{ strtoupper($tokoName) }}</div>
                    <div style="font-size: 11px;">TES CETAK STRUK THERMAL</div>
                    <div style="font-size: 10px;">TEST-PRINTER-001</div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px;">
                    <span>Produk Contoh x1</span>
                    <span>Rp 15.000</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px;">
                    <span>Kopi Susu x2</span>
                    <span>Rp 36.000</span>
                </div>
                <div style="border-top: 1px dashed #000; margin: 6px 0; padding-top: 6px; display: flex; justify-content: space-between; font-weight: 700; font-size: 12px;">
                    <span>TOTAL</span>
                    <span>Rp 51.000</span>
                </div>
                <div style="text-align: center; font-size: 10px; margin-top: 8px;">
                    Printer berhasil terhubung!<br>Terima kasih.
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/pos-hardware.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
    <script>
        (function () {
            const productSkuMap = @json($productSkuMap);
            const tokoName = @json($tokoName);

            const modeSelect = document.getElementById('hw-printer-mode');
            const paperSelect = document.getElementById('hw-paper-width');
            const baudSelect = document.getElementById('hw-serial-baud');
            const statusBadge = document.getElementById('printer-status-badge');
            const feedbackEl = document.getElementById('printer-feedback');

            const scannerInput = document.getElementById('hw-scanner-input');
            const resultCode = document.getElementById('scan-result-code');
            const resultFormat = document.getElementById('scan-result-format');
            const resultSource = document.getElementById('scan-result-source');
            const resultProduct = document.getElementById('scan-result-product');
            const cameraBtn = document.getElementById('btn-test-camera-scan');
            const cameraViewport = document.getElementById('guide-camera-viewport');

            function showFeedback(message, type = 'success') {
                feedbackEl.className = type === 'error'
                    ? 'hg-feedback is-error'
                    : 'hg-feedback is-success';
                feedbackEl.textContent = message;
            }

            function refreshStatusBadge() {
                if (!window.PosHardware) return;
                const s = window.PosHardware.getSettings();
                if (s.printerMode === 'web_serial') {
                    statusBadge.textContent = window.PosHardware.isSerialConnected()
                        ? `USB Terhubung (${s.paperWidth})`
                        : `Web Serial (${s.paperWidth})`;
                } else if (s.printerMode === 'web_bluetooth') {
                    statusBadge.textContent = window.PosHardware.isBluetoothConnected()
                        ? `Bluetooth Terhubung (${s.paperWidth})`
                        : `Web Bluetooth (${s.paperWidth})`;
                } else {
                    statusBadge.textContent = `Mode Browser (${s.paperWidth})`;
                }
            }

            const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
            document.getElementById('cap-https').textContent = isSecure ? '✅ HTTPS / Aman' : '⚠️ Belum HTTPS';
            document.getElementById('cap-serial').textContent = ('serial' in navigator) ? '✅ Web Serial Siap' : '❌ Web Serial Tidak Didukung';
            document.getElementById('cap-bluetooth').textContent = ('bluetooth' in navigator) ? '✅ Web Bluetooth Siap' : '❌ Web Bluetooth Tidak Didukung';
            document.getElementById('cap-camera').textContent = (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) ? '✅ Kamera Siap' : '❌ Kamera Tidak Tersedia';

            if (window.PosHardware) {
                const s = window.PosHardware.getSettings();
                modeSelect.value = s.printerMode;
                paperSelect.value = s.paperWidth;
                baudSelect.value = String(s.serialBaud);
                refreshStatusBadge();
            }

            document.getElementById('btn-save-printer').addEventListener('click', () => {
                if (!window.PosHardware) return;
                window.PosHardware.saveSettings({
                    printerMode: modeSelect.value,
                    paperWidth: paperSelect.value,
                    serialBaud: parseInt(baudSelect.value, 10) || 9600,
                });
                refreshStatusBadge();
                showFeedback('Pengaturan printer berhasil disimpan dan aktif untuk halaman Kasir.');
            });

            document.getElementById('btn-connect-serial').addEventListener('click', async () => {
                try {
                    window.PosHardware.saveSettings({
                        printerMode: 'web_serial',
                        paperWidth: paperSelect.value,
                        serialBaud: parseInt(baudSelect.value, 10) || 9600,
                    });
                    modeSelect.value = 'web_serial';
                    await window.PosHardware.connectSerial();
                    refreshStatusBadge();
                    showFeedback('Printer USB (Web Serial) berhasil terhubung! Klik "Tes Cetak Struk" untuk mencoba.');
                } catch (err) {
                    showFeedback(err.message || 'Gagal menghubungkan printer USB.', 'error');
                }
            });

            document.getElementById('btn-connect-bt').addEventListener('click', async () => {
                try {
                    window.PosHardware.saveSettings({
                        printerMode: 'web_bluetooth',
                        paperWidth: paperSelect.value,
                    });
                    modeSelect.value = 'web_bluetooth';
                    const name = await window.PosHardware.connectBluetooth();
                    refreshStatusBadge();
                    showFeedback(`Printer Bluetooth "${name}" berhasil terhubung! Klik "Tes Cetak Struk" untuk mencoba.`);
                } catch (err) {
                    showFeedback(err.message || 'Gagal menghubungkan printer Bluetooth.', 'error');
                }
            });

            document.getElementById('btn-test-print').addEventListener('click', async () => {
                try {
                    window.PosHardware.saveSettings({
                        printerMode: modeSelect.value,
                        paperWidth: paperSelect.value,
                        serialBaud: parseInt(baudSelect.value, 10) || 9600,
                    });

                    const sampleReceipt = {
                        storeName: tokoName,
                        storeAddress: 'Tes Koneksi Printer Thermal',
                        transactionNumber: 'TEST-PRINTER-001',
                        items: [
                            { name: 'Produk Contoh', qty: 1, subtotal: 15000 },
                            { name: 'Kopi Susu', qty: 2, subtotal: 36000 },
                        ],
                        subtotal: 51000,
                        tax: 0,
                        service: 0,
                        discount: 0,
                        total: 51000,
                        paymentMethod: 'TUNAI',
                        paid: 100000,
                        change: 49000,
                        time: new Date().toLocaleString('id-ID'),
                    };

                    const usedMode = await window.PosHardware.printReceipt(sampleReceipt);
                    refreshStatusBadge();
                    showFeedback(`Tes cetak struk berhasil dikirim melalui mode: ${usedMode}.`);
                } catch (err) {
                    showFeedback(err.message || 'Gagal melakukan tes cetak struk.', 'error');
                }
            });

            function handleScannedCode(rawCode, meta = {}) {
                const code = String(rawCode || '').trim();
                if (!code) return;

                resultCode.textContent = code;
                resultFormat.textContent = window.PosHardware
                    ? window.PosHardware.detectBarcodeFormat(code)
                    : 'Terdeteksi';
                resultSource.textContent = meta.source === 'camera'
                    ? 'Kamera Browser'
                    : (meta.durationMs ? `Scanner HID (${meta.durationMs} ms)` : 'Input Scanner / Keyboard');

                const lower = code.toLowerCase();
                const ean12 = /^\d{13}$/.test(code) ? code.slice(0, 12) : null;
                let matchedName = null;

                for (const [sku, name] of Object.entries(productSkuMap)) {
                    const cleanSku = String(sku).trim();
                    if (cleanSku.toLowerCase() === lower || (ean12 && cleanSku === ean12)) {
                        matchedName = name;
                        break;
                    }
                }

                if (matchedName) {
                    resultProduct.style.color = '#059669';
                    resultProduct.textContent = `✅ Cocok dengan produk di toko Anda: "${matchedName}" — Siap dipakai di Kasir!`;
                } else {
                    resultProduct.style.color = '#d97706';
                    resultProduct.textContent = '⚠️ Scanner terbaca normal, namun SKU ini belum terdaftar pada daftar produk toko Anda.';
                }
            }

            if (scannerInput) {
                scannerInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleScannedCode(scannerInput.value, { source: 'input' });
                        scannerInput.value = '';
                    }
                });
            }

            if (window.PosHardware) {
                window.PosHardware.attachGlobalBarcodeScanner((code, meta) => {
                    handleScannedCode(code, meta);
                });
            }

            let cameraRunning = false;
            if (cameraBtn) {
                cameraBtn.addEventListener('click', () => {
                    if (cameraRunning) {
                        Quagga.stop();
                        cameraViewport.style.display = 'none';
                        cameraBtn.textContent = '📷 Tes Kamera';
                        cameraRunning = false;
                        return;
                    }

                    cameraViewport.style.display = 'block';
                    cameraBtn.textContent = '⏹️ Stop Kamera';
                    Quagga.init({
                        inputStream: {
                            name: 'Live',
                            type: 'LiveStream',
                            target: cameraViewport,
                            constraints: { facingMode: 'environment' },
                        },
                        decoder: {
                            readers: ['ean_reader', 'code_128_reader', 'ean_8_reader', 'code_39_reader'],
                        },
                    }, (err) => {
                        if (err) {
                            showFeedback('Gagal membuka kamera: ' + err.message, 'error');
                            cameraViewport.style.display = 'none';
                            cameraBtn.textContent = '📷 Tes Kamera';
                            return;
                        }
                        Quagga.start();
                        cameraRunning = true;
                    });
                });

                Quagga.onDetected((data) => {
                    if (data && data.codeResult && data.codeResult.code) {
                        handleScannedCode(data.codeResult.code, { source: 'camera' });
                    }
                });
            }
        })();
    </script>
</x-filament-panels::page>
