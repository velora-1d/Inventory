# DOKUMENTASI SISTEM: 10. STOCK OPNAME

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Stock Opname** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('stock-opnames', StockOpnameController::class);` beserta `Route::post('stock-opnames/{stock_opname}/confirm', [StockOpnameController::class, 'confirm'])->name('stock-opnames.confirm');`
*   **Controller:** `app/Http/Controllers/StockOpnameController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Transactions/StockOpname.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & AUTO-POPULATION
Menu ini menyelaraskan stok sistem database dengan stok fisik aktual di gudang:

### A. Fitur Utama & Aksi
*   **Pemuatan Otomatis (Auto-Load):**
    *   Saat Gudang dipilih di modal input, sistem otomatis mencari semua produk yang memiliki stok aktif di gudang tersebut dari database.
    *   Tabel form akan otomatis terisi dengan list produk lengkap beserta kuantitas teoritis saat ini (*Sistem Qty*).

### B. Form Penyesuaian Kuantitas
*   **Fisik Qty (Input):** User memasukkan kuantitas aktual hasil perhitungan manual.
*   **Selisih (Difference):** Sistem otomatis menghitung selisih (`Difference = Fisik Qty - Sistem Qty`) secara real-time.

---

## 3. INTEGRASI DATABASE & ALUR OPNAME
*   **Konfirmasi Opname (Adjustment):**
    *   Proses ini memperbarui stok gudang (`stocks.qty`) agar nilainya sama dengan *Fisik Qty* yang diinput.
    *   Perbedaan kuantitas dicatat di `stock_ledgers` dengan tipe `adjustment` (menambah stok jika positif, mengurangi stok jika negatif) untuk menyeimbangkan nilai buku.
