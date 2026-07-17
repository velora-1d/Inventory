# DOKUMENTASI SISTEM: 9. TRANSFER STOK

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Transfer Stok** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('stock-transfers', StockTransferController::class);` beserta `Route::post('stock-transfers/{stock_transfer}/confirm', [StockTransferController::class, 'confirm'])->name('stock-transfers.confirm');`
*   **Controller:** `app/Http/Controllers/StockTransferController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Transactions/StockTransfer.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini memproses pemindahan barang antargudang internal:

### A. Fitur Utama & Aksi
*   **Bilah Pencarian & Filter:** Filter pencarian No. Transaksi, status, gudang asal, gudang tujuan, dan rentang tanggal.
*   **Form Input Transfer:**
    *   *Gudang Asal* (`#modal-from_warehouse_id`) dan *Gudang Tujuan* (`#modal-to_warehouse_id`).
    *   *Items Grid:* Multi-row produk dengan validasi otomatis sisa stok di gudang asal.

---

## 3. ALUR VALIDASI & TRANSAKSI KONSISTEN
*   **Validasi Kesamaan Gudang:** Sistem memblokir pengiriman jika Gudang Asal sama dengan Gudang Tujuan dengan pesan error "Gudang asal dan tujuan tidak boleh sama."
*   **Konfirmasi Transfer (ACID Compliance):**
    *   Proses transfer dijalankan dalam transaksi `DB::transaction()`.
    *   Stok di Gudang Asal dikurangi dan stok di Gudang Tujuan ditambah secara simultan. Jika salah satu operasi gagal, seluruh transaksi di-rollback secara otomatis.
    *   Mutasi dicatat sebagai `transfer_out` untuk gudang asal dan `transfer_in` untuk gudang tujuan di tabel `stock_ledgers`.
