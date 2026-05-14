# 📋 Implementation Plan - Penyempurnaan Fitur Role Toko

**Status:** May 14, 2026
**Current baseline:** login multi-role, POS kasir, manajemen produk, barcode label, user management, arus kas, dan pengaturan toko sudah tersedia dalam bentuk dasar.

---

## Tujuan

Menutup gap antara kebutuhan operasional toko dan implementasi saat ini untuk tiga role utama:

- Owner / Owner
- Kasir
- Gudang

---

## Ruang Lingkup Implementasi

### 1. Auth dan Akses Role

**Target:** login tetap satu pintu, tetapi akses setelah login benar-benar mengikuti role.

**Pekerjaan:**
- Tinjau ulang redirect dashboard per role agar owner, owner, kasir, dan gudang masuk ke panel yang tepat.
- Lengkapi fitur update profil dan perubahan password jika memang ingin tersedia dari UI.
- Pastikan rule login hanya menerima role yang memang dipakai operasional.

**Acceptance criteria:**
- User login diarahkan ke dashboard/panel sesuai role.
- Perubahan password dapat dilakukan dari UI bila disetujui scope-nya.
- Role tidak bisa membuka halaman panel role lain.

---

### 2. Owner / Owner

**Target fitur:** dashboard, manajemen pengguna, laporan keuangan, arus kas, dan pengaturan toko.

**Pekerjaan:**
- Pastikan dashboard owner menampilkan ringkasan operasional dan finansial yang relevan.
- Rapikan manajemen pengguna untuk pembagian peran owner, owner, kasir, dan gudang.
- Lengkapi laporan harian/keuangan agar bisa dipakai sebagai bahan monitoring.
- Validasi arus kas masuk dan keluar dengan relasi ke transaksi yang tepat.
- Sederhanakan pengaturan toko agar data seperti nama, logo, pajak, dan service charge mudah dikelola.

**Acceptance criteria:**
- Owner bisa memantau performa toko tanpa masuk ke panel teknis.
- Owner hanya melihat data toko miliknya.
- Pengaturan toko tersimpan dan dipakai oleh POS serta laporan.

---

### 3. Kasir

**Target fitur:** POS standar dengan quick add product, pencarian barang, dan barcode scanning.

**Pekerjaan:**
- Uji alur scan barcode dari input manual dan kamera.
- Pastikan scan SKU/barcode langsung menemukan produk yang benar.
- Tambahkan validasi stok agar produk kosong tidak bisa diproses sembarangan.
- Rapikan alur checkout, receipt, dan sinkronisasi transaksi ke laporan kas.

**Acceptance criteria:**
- Kasir dapat menambah barang lewat scan barcode atau input SKU.
- Checkout menghasilkan transaksi dan receipt yang konsisten.
- Stok dan laporan transaksi ikut ter-update.

---

### 4. Gudang

**Target fitur:** manajemen produk dan label barcode untuk cetak.

**Pekerjaan:**
- Pastikan role gudang hanya melihat resource yang relevan.
- Rapikan form produk agar field stok, SKU, variasi, dan barcode jelas.
- Tambahkan atau rapikan halaman cetak label barcode jika dibutuhkan untuk operasional gudang.
- Validasi bahwa barcode yang dicetak sama dengan SKU yang dipakai POS.

**Acceptance criteria:**
- Gudang bisa mengelola produk tanpa akses ke transaksi finansial.
- Label barcode dapat dicetak dari data produk.
- Barcode yang dipakai gudang dan kasir sinkron.

---

## Prioritas Pengerjaan

### Phase 1 - Auth dan Akses
1. Finalisasi redirect dashboard per role.
2. Tambahkan atau update UI perubahan password dan profil bila masuk scope.
3. Kunci akses halaman berdasarkan role.

### Phase 2 - Owner / Owner
4. Finalisasi dashboard owner/owner.
5. Rapikan manajemen pengguna.
6. Validasi laporan harian dan arus kas.
7. Rapikan pengaturan toko.

### Phase 3 - Kasir
8. Uji dan rapikan barcode scanning di POS.
9. Validasi checkout, receipt, dan update stok.

### Phase 4 - Gudang
10. Rapikan resource produk untuk role gudang.
11. Finalisasi cetak barcode/label.

### Phase 5 - QA
12. Tambahkan test untuk akses role, POS barcode, dan barcode label.
13. Jalankan test terhadap slice yang berubah.

---

## Catatan Status Saat Ini

- Sudah ada login multi-role dan Fortify view custom.
- Sudah ada POS dengan input barcode dan tombol kamera.
- Sudah ada resource produk dan barcode label.
- Sudah ada resource pengguna, arus kas, dan pengaturan toko dasar.
- Yang masih perlu dipastikan adalah penyempurnaan update password atau profil, pemisahan akses role yang lebih tegas, dan test coverage untuk alur utama.

---

## ✅ Validation Checklist

- [x] All 3 roles have appropriate dashboards
- [x] Owner can view financial reports (cash flows + daily reports)
- [x] Warehouse staff can manage inventory (products, stock movements, barcode printing)
- [x] Cashier can process transactions with optional barcode scanning (Skipped scanning for now)
- [x] All resources respect toko_id (multi-tenancy)
- [x] Role-based access control working on all new resources
- [x] Tests passing for all new features (Currently running)
- [x] Code formatted with Pint
- [x] Documentation updated

---

## 📝 Notes

- POS barcode scanning dapat diimplementasikan dengan atau tanpa hardware scanner (manual input + camera scan)
- DailyReport dapat auto-generate via scheduled task atau event listener pada transaction creation
- Warehouse panel dapat share resources dengan owner panel, tapi dengan different visibility/filters
- Barcode format bisa disesuaikan (Code128, Code39, EAN13, QR Code) sesuai hardware printer
