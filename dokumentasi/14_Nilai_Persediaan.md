# DOKUMENTASI SISTEM: 14. NILAI PERSEDIAAN

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Nilai Persediaan** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::get('/reports/valuation', [ValuationController::class, 'index'])->name('reports.valuation');`
*   **Controller:** `app/Http/Controllers/Reports/ValuationController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Reports/Valuation.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & KARTU KPI KEUANGAN
Menu ini memantau valuasi aset keuangan yang terikat dalam stok barang:

### A. Fitur Utama & Aksi
*   **Tiga Kartu Ringkasan Valuasi (KPI):**
    1.  *Total Kuantitas Barang:* Akumulasi fisik seluruh barang di gudang terpilih.
    2.  *Total Nilai Aset (Rupiah):* Total nominal aset stok berdasarkan harga beli rata-rata.
    3.  *Total SKU Terdaftar:* Jumlah SKU produk yang memiliki stok aktif.
*   **Tabel Valuasi Aset:** Menampilkan Kode SKU, Nama Produk, Kategori, Stok Fisik, Harga Rata-Rata Beli (Average Cost), dan Total Nilai Valuasi Aset per produk.

---

## 3. INTEGRASI DATABASE & FORMULA ASET
*   **Formula Valuasi Produk:**
    $$\text{Valuasi Produk} = \text{Stok Kuantitas} \times \text{Harga Rata-Rata Beli (avg\_price)}$$
*   **Query Database:** Menggabungkan data dari tabel `products` (untuk mendapatkan `avg_price` terbaru) dan tabel `stocks` (untuk total kuantitas per gudang).
