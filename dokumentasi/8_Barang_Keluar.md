# DOKUMENTASI SISTEM: 8. BARANG KELUAR

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Barang Keluar** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('stock-outs', StockOutController::class);` beserta `Route::post('stock-outs/{stock_out}/confirm', [StockOutController::class, 'confirm'])->name('stock-outs.confirm');`
*   **Controller:** `app/Http/Controllers/StockOutController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Transactions/StockOut.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & PENGELOLAAN DRAFT
Menu ini memproses pengeluaran stok dari gudang (penjualan, konsumsi internal, dsb):

### A. Fitur Utama & Aksi
*   **Filter Pencarian:** No. Transaksi, Gudang Sumber, Status (Draft / Selesai), dan Rentang Tanggal.
*   **Tabel Transaksi:** Menampilkan detail No. Transaksi, Tanggal, Penerima, Gudang, Jumlah Item, Pembuat (Creator), Status, dan Tombol Aksi.

### B. Form Modal Dynamic Items & Validasi Stok
*   **Header:** Tanggal, Gudang Sumber, Penerima, No. Referensi, Catatan.
*   **Pengecekan Stok Real-time (Available Stock):**
    *   Saat produk dipilih, sistem mendeteksi sisa stok fisik di gudang sumber yang bersangkutan secara real-time.
    *   Label "Stok Tersedia: X" ditampilkan sebagai panduan pengisian Qty agar user tidak menginput jumlah melebihi kapasitas gudang.

---

## 3. ALUR KERJA ACID & DATABASE TRANSACTION
*   **Konfirmasi Transaksi (Isolation & Consistency):**
    *   Menggunakan **Row-Level Locking (`lockForUpdate()`)** untuk mengunci baris stok produk di gudang sumber selama transaksi berjalan untuk menghindari pembacaan konkuren yang tidak konsisten.
    *   Mengurangi kuantitas stok gudang (`stocks.qty`).
    *   Mencatat mutasi keluar ke `stock_ledgers` (tipe `out`).
    *   Mengubah status transaksi menjadi **Completed** (Selesai).
*   **Validasi Keamanan:** Transaksi yang sudah selesai (Completed) dikunci secara permanen sehingga tidak dapat diedit atau dihapus secara langsung.
