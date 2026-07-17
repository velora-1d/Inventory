# DOKUMENTASI SISTEM: 7. BARANG MASUK

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Barang Masuk** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('stock-ins', StockInController::class);` beserta `Route::post('stock-ins/{stock_in}/confirm', [StockInController::class, 'confirm'])->name('stock-ins.confirm');`
*   **Controller:** `app/Http/Controllers/StockInController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Transactions/StockIn.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & PENGELOLAAN DRAFT
Menu ini memproses penerimaan barang/stok baru ke gudang:

### A. Fitur Utama & Aksi
*   **Filter Pencarian:** No. Transaksi, Gudang Penerima, Status (Draft / Selesai), dan Rentang Tanggal.
*   **Tabel Transaksi:** Menampilkan detail No. Transaksi, Tanggal, Supplier, Gudang, Jumlah Item, Pembuat (Creator), Status Badge, dan Tombol Aksi (Detail, Edit, Hapus, Selesaikan).

### B. Form Modal Dynamic Items (Dynamic Row Grid)
*   **Header:** Tanggal, Supplier, Gudang Penerima, No. Referensi, Catatan.
*   **Item Grid:** Menghubungkan multi-row produk. Tombol "+ Tambah Item" menambahkan baris baru secara dinamis.
*   **Auto-Population:** Saat produk dipilih, sistem memuat satuan dan harga beli default.
*   **Kalkulasi Total:** Perubahan Qty atau Harga otomatis menghitung Subtotal item dan meng-update Grand Total di bagian bawah form secara real-time.

---

## 3. ALUR KERJA ACID & DATABASE TRANSACTION
*   **Penyimpanan Draft (Atomicity):** Seluruh input data tersimpan di tabel `stock_ins` dan `stock_in_items` dalam satu transaksi `DB::transaction()`. Status awal transaksi selalu **Draft**.
*   **Konfirmasi Transaksi (Isolation & Consistency):**
    *   Menggunakan **Row-Level Locking (`lockForUpdate()`)** untuk mengunci baris stok produk di database agar tidak terjadi *race condition* jika ada transaksi konkuren.
    *   Menghitung ulang harga rata-rata tertimbang (*Weighted Average Price*) produk:
        $$\text{New Average} = \frac{(\text{Qty Old} \times \text{Avg Price Old}) + (\text{Qty New} \times \text{Price New})}{\text{Qty Old} + \text{Qty New}}$$
    *   Memperbarui stok gudang (`stocks.qty`).
    *   Mencatat mutasi masuk ke `stock_ledgers` (tipe `in`).
    *   Mengubah status transaksi menjadi **Completed** (Selesai).
