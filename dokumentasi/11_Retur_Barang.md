# DOKUMENTASI SISTEM: 11. RETUR BARANG

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Retur Barang** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('stock-returns', StockReturnController::class);` beserta `Route::post('stock-returns/{stock_return}/confirm', [StockReturnController::class, 'confirm'])->name('stock-returns.confirm');`
*   **Controller:** `app/Http/Controllers/StockReturnController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Transactions/StockReturn.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini memproses pengembalian barang rusak atau tidak sesuai:

### A. Fitur Utama & Aksi
*   **Jenis Retur:**
    *   *Retur Masuk (Return In):* Barang dikembalikan oleh pelanggan ke gudang kita (menambah stok).
    *   *Retur Keluar (Return Out):* Barang dikembalikan dari gudang kita ke supplier (mengurangi stok).
*   **Form Input Retur:**
    *   Dropdown Pilihan *Jenis Retur* (`#modal-return_type`), *Gudang*, *Alasan Retur*, dan list produk.
    *   *Kondisi Barang (Condition):* Pilihan kondisi produk (Good/Broken) untuk pelacakan kualitas barang retur.

---

## 3. INTEGRASI DATABASE & ALUR RETUR
*   **Kalkulasi Nilai Retur:**
    *   Untuk Retur Masuk, harga dihitung berdasarkan harga rata-rata (*Average Price*).
    *   Untuk Retur Keluar, harga dihitung berdasarkan harga beli awal (*Purchase Price*).
*   **Pembaruan Stok:** Setelah konfirmasi, stok gudang disesuaikan secara otomatis sesuai tipe retur dan dicatat ke `stock_ledgers` (tipe `return_in`/`return_out`).
