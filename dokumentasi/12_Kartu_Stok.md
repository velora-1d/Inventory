# DOKUMENTASI SISTEM: 12. KARTU STOK

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Kartu Stok** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::get('/reports/ledger', [LedgerController::class, 'index'])->name('reports.ledger');`
*   **Controller:** `app/Http/Controllers/Reports/LedgerController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Reports/Ledger.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & KARTU HISTORI
Menu ini menyajikan rekam jejak histori mutasi individual per produk:

### A. Fitur Utama & Aksi
*   **Filter Pencarian Utama:**
    *   Wajib memilih *Produk* dan *Gudang* untuk menampilkan kartu stok.
    *   Filter tambahan: Rentang tanggal awal & akhir.
*   **Grid Ringkasan Status Stok:**
    *   *Stok Awal (Opening Balance):* Jumlah kuantitas stok di awal periode filter.
    *   *Total Masuk (Total Qty In):* Akumulasi jumlah barang masuk selama periode.
    *   *Total Keluar (Total Qty Out):* Akumulasi jumlah barang keluar selama periode.
    *   *Stok Akhir (Closing Balance):* Jumlah kuantitas stok saat ini di akhir periode.

---

## 3. INTEGRASI DATABASE & ALUR KARTU STOK
*   **Query Data Histori:** Data dibaca secara berurutan kronologis dari tabel `stock_ledgers` berdasarkan tanggal transaksi dan ID produk/gudang terpilih.
*   **Ekspor Data:** Tombol cetak PDF dan ekspor Excel disediakan untuk pelaporan audit stok.
