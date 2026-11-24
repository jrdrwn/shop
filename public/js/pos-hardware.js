/**
 * POS Hardware Helper — Web Serial, Web Bluetooth (ESC/POS), Paper Width & HID Barcode Scanner.
 */
(function (window) {
    'use strict';

    const STORAGE_KEYS = {
        paperWidth: 'pos_paper_width',
        printerMode: 'pos_printer_mode',
        serialBaud: 'pos_serial_baud',
    };

    const BLE_SERVICE_UUIDS = [
        '000018f0-0000-1000-8000-00805f9b34fb',
        'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
        '49535343-fe7d-4ae5-8fa9-9fafd205e455',
        '0000ff00-0000-1000-8000-00805f9b34fb',
        '0000ffe0-0000-1000-8000-00805f9b34fb',
    ];

    let serialPort = null;
    let bluetoothDevice = null;
    let bluetoothCharacteristic = null;

    function getSettings() {
        return {
            paperWidth: localStorage.getItem(STORAGE_KEYS.paperWidth) || '80mm',
            printerMode: localStorage.getItem(STORAGE_KEYS.printerMode) || 'browser',
            serialBaud: parseInt(localStorage.getItem(STORAGE_KEYS.serialBaud) || '9600', 10) || 9600,
        };
    }

    function saveSettings(partial) {
        if (partial.paperWidth) {
            localStorage.setItem(STORAGE_KEYS.paperWidth, partial.paperWidth);
        }
        if (partial.printerMode) {
            localStorage.setItem(STORAGE_KEYS.printerMode, partial.printerMode);
        }
        if (partial.serialBaud) {
            localStorage.setItem(STORAGE_KEYS.serialBaud, String(partial.serialBaud));
        }
        applyReceiptPaperWidth();
        return getSettings();
    }

    function applyReceiptPaperWidth() {
        const { paperWidth } = getSettings();
        const width = paperWidth === '58mm' ? '58mm' : '80mm';
        const fontSize = paperWidth === '58mm' ? '8.5pt' : '10pt';

        let styleEl = document.getElementById('pos-paper-width-style');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'pos-paper-width-style';
            document.head.appendChild(styleEl);
        }

        styleEl.textContent = `
            @media print {
                @page { size: ${width} auto; margin: 0; }
                .receipt-container,
                #receipt-modal > div {
                    width: ${width} !important;
                    max-width: ${width} !important;
                    font-size: ${fontSize} !important;
                }
            }
        `;
    }

    function formatRupiah(amount) {
        const num = Math.round(Number(amount) || 0);
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function padLine(left, right, width) {
        const l = String(left || '');
        const r = String(right || '');
        const spaces = Math.max(1, width - l.length - r.length);
        if (l.length + r.length + 1 > width) {
            const maxLeft = Math.max(4, width - r.length - 1);
            return l.slice(0, maxLeft) + ' ' + r;
        }
        return l + ' '.repeat(spaces) + r;
    }

    function buildEscPosReceipt(data, paperWidth) {
        const cols = (paperWidth || getSettings().paperWidth) === '58mm' ? 32 : 42;
        const encoder = new TextEncoder();
        const chunks = [];
        const pushBytes = (...bytes) => chunks.push(new Uint8Array(bytes));
        const pushText = (text) => chunks.push(encoder.encode(String(text)));
        const divider = '-'.repeat(cols) + '\n';

        // Initialize printer
        pushBytes(0x1b, 0x40);
        // Center align
        pushBytes(0x1b, 0x61, 0x01);
        // Bold ON + double height
        pushBytes(0x1b, 0x45, 0x01);
        pushText((data.storeName || 'TOKO').toUpperCase() + '\n');
        pushBytes(0x1b, 0x45, 0x00);

        if (data.storeAddress) {
            pushText(data.storeAddress + '\n');
        }
        if (data.transactionNumber) {
            pushText(data.transactionNumber + '\n');
        }
        pushText(divider);

        // Left align
        pushBytes(0x1b, 0x61, 0x00);
        const items = Array.isArray(data.items) ? data.items : [];
        items.forEach((item) => {
            const name = `${item.name} x${item.qty}`;
            const subtotal = formatRupiah(item.subtotal);
            pushText(padLine(name, subtotal, cols) + '\n');
        });

        pushText(divider);
        pushText(padLine('Subtotal', formatRupiah(data.subtotal), cols) + '\n');

        if (Number(data.tax) > 0) {
            pushText(padLine('Pajak', formatRupiah(data.tax), cols) + '\n');
        }
        if (Number(data.service) > 0) {
            pushText(padLine('Service', formatRupiah(data.service), cols) + '\n');
        }
        if (Number(data.discount) > 0) {
            pushText(padLine('Diskon', '-' + formatRupiah(data.discount), cols) + '\n');
        }

        pushText(divider);
        pushBytes(0x1b, 0x45, 0x01);
        pushText(padLine('TOTAL', formatRupiah(data.total), cols) + '\n');
        pushBytes(0x1b, 0x45, 0x00);
        pushText(divider);

        pushText(padLine('Metode', String(data.paymentMethod || 'CASH').toUpperCase(), cols) + '\n');
        pushText(padLine('Bayar', formatRupiah(data.paid), cols) + '\n');
        pushText(padLine('Kembalian', formatRupiah(data.change), cols) + '\n');
        pushText(divider);

        // Center footer
        pushBytes(0x1b, 0x61, 0x01);
        pushText((data.time || new Date().toLocaleString('id-ID')) + '\n');
        pushText('Terima kasih atas kunjungan Anda\n\n\n');

        // Partial cut
        pushBytes(0x1d, 0x56, 0x42, 0x00);

        const totalLength = chunks.reduce((acc, arr) => acc + arr.length, 0);
        const merged = new Uint8Array(totalLength);
        let offset = 0;
        for (const chunk of chunks) {
            merged.set(chunk, offset);
            offset += chunk.length;
        }
        return merged;
    }

    async function connectSerial() {
        if (!('serial' in navigator)) {
            throw new Error('Browser ini tidak mendukung Web Serial API. Gunakan Google Chrome atau Microsoft Edge versi terbaru.');
        }

        const { serialBaud } = getSettings();
        serialPort = await navigator.serial.requestPort();
        if (!serialPort.readable && !serialPort.writable) {
            await serialPort.open({ baudRate: serialBaud });
        }
        return true;
    }

    async function disconnectSerial() {
        if (serialPort) {
            try {
                await serialPort.close();
            } catch (e) {
                // ignore
            }
            serialPort = null;
        }
    }

    async function writeSerial(bytes) {
        if (!serialPort || !serialPort.writable) {
            await connectSerial();
        }
        const writer = serialPort.writable.getWriter();
        try {
            await writer.write(bytes);
        } finally {
            writer.releaseLock();
        }
    }

    async function connectBluetooth() {
        if (!('bluetooth' in navigator)) {
            throw new Error('Browser ini tidak mendukung Web Bluetooth API. Gunakan Google Chrome (Desktop / Android) lewat HTTPS.');
        }

        bluetoothDevice = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: BLE_SERVICE_UUIDS,
        });

        const server = await bluetoothDevice.gatt.connect();
        let foundChar = null;

        for (const uuid of BLE_SERVICE_UUIDS) {
            try {
                const service = await server.getPrimaryService(uuid);
                const chars = await service.getCharacteristics();
                const writable = chars.find((c) => c.properties.write || c.properties.writeWithoutResponse);
                if (writable) {
                    foundChar = writable;
                    break;
                }
            } catch (e) {
                // try next UUID
            }
        }

        if (!foundChar) {
            const services = await server.getPrimaryServices();
            for (const service of services) {
                const chars = await service.getCharacteristics();
                const writable = chars.find((c) => c.properties.write || c.properties.writeWithoutResponse);
                if (writable) {
                    foundChar = writable;
                    break;
                }
            }
        }

        if (!foundChar) {
            throw new Error('Printer terhubung, tetapi layanan cetak ESC/POS tidak ditemukan. Gunakan mode Cetak Browser atau RawBT.');
        }

        bluetoothCharacteristic = foundChar;
        return bluetoothDevice.name || 'Printer Bluetooth';
    }

    function disconnectBluetooth() {
        if (bluetoothDevice && bluetoothDevice.gatt && bluetoothDevice.gatt.connected) {
            bluetoothDevice.gatt.disconnect();
        }
        bluetoothDevice = null;
        bluetoothCharacteristic = null;
    }

    async function writeBluetooth(bytes) {
        if (!bluetoothCharacteristic || !bluetoothDevice || !bluetoothDevice.gatt.connected) {
            await connectBluetooth();
        }
        const chunkSize = 100;
        for (let i = 0; i < bytes.length; i += chunkSize) {
            const slice = bytes.slice(i, i + chunkSize);
            if (bluetoothCharacteristic.properties.writeWithoutResponse) {
                await bluetoothCharacteristic.writeValueWithoutResponse(slice);
            } else {
                await bluetoothCharacteristic.writeValue(slice);
            }
            await new Promise((resolve) => setTimeout(resolve, 25));
        }
    }

    async function printReceipt(receiptData) {
        const settings = getSettings();
        applyReceiptPaperWidth();

        if (settings.printerMode === 'web_serial') {
            const bytes = buildEscPosReceipt(receiptData, settings.paperWidth);
            await writeSerial(bytes);
            return 'web_serial';
        }

        if (settings.printerMode === 'web_bluetooth') {
            const bytes = buildEscPosReceipt(receiptData, settings.paperWidth);
            await writeBluetooth(bytes);
            return 'web_bluetooth';
        }

        window.print();
        return 'browser';
    }

    function detectBarcodeFormat(code) {
        const clean = String(code || '').trim();
        if (/^\d{13}$/.test(clean)) {
            let sum = 0;
            for (let i = 0; i < 12; i++) {
                const digit = parseInt(clean[i], 10);
                sum += i % 2 === 0 ? digit : digit * 3;
            }
            const check = (10 - (sum % 10)) % 10;
            if (check === parseInt(clean[12], 10)) {
                return 'EAN-13 (Valid)';
            }
            return '13 Digit (Digit cek tidak cocok)';
        }
        if (/^\d{12}$/.test(clean)) {
            return 'EAN-13 (12 digit tanpa digit cek) / UPC-A';
        }
        if (/^\d{8}$/.test(clean)) {
            return 'EAN-8';
        }
        return 'Code 128 / Alfanumerik';
    }

    function attachGlobalBarcodeScanner(onScan, options = {}) {
        const minLength = options.minLength || 4;
        const maxIntervalMs = options.maxIntervalMs || 65;
        let buffer = '';
        let lastKeyTime = 0;
        let startTime = 0;

        const handler = (e) => {
            const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
            const isEditable = tag === 'input' || tag === 'textarea' || tag === 'select' || (e.target && e.target.isContentEditable);

            if (isEditable) {
                return;
            }

            const now = Date.now();
            if (now - lastKeyTime > maxIntervalMs) {
                buffer = '';
                startTime = now;
            }
            lastKeyTime = now;

            if (e.key === 'Enter') {
                if (buffer.length >= minLength) {
                    e.preventDefault();
                    const duration = Math.max(1, now - startTime);
                    const code = buffer.trim();
                    buffer = '';
                    onScan(code, { durationMs: duration, source: 'hid_scanner' });
                } else {
                    buffer = '';
                }
                return;
            }

            if (e.key && e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (!buffer) {
                    startTime = now;
                }
                buffer += e.key;
            }
        };

        document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }

    window.PosHardware = {
        getSettings,
        saveSettings,
        applyReceiptPaperWidth,
        buildEscPosReceipt,
        connectSerial,
        disconnectSerial,
        writeSerial,
        isSerialConnected: () => Boolean(serialPort && serialPort.writable),
        connectBluetooth,
        disconnectBluetooth,
        writeBluetooth,
        isBluetoothConnected: () => Boolean(bluetoothDevice && bluetoothDevice.gatt && bluetoothDevice.gatt.connected),
        printReceipt,
        detectBarcodeFormat,
        attachGlobalBarcodeScanner,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyReceiptPaperWidth);
    } else {
        applyReceiptPaperWidth();
    }
})(window);
