# DOKUMENTASI SISTEM: 13. MUTASI BARANG

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Mutasi Barang** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::get('/reports/mutation', [MutationController::class, 'index'])->name('reports.mutation');`
*   **Controller:** `app/Http/Controllers/Reports/MutationController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Reports/Mutation.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & TABEL REKAPITULASI
Menu ini menyajikan ringkasan perpindahan stok secara global untuk seluruh produk:

### A. Fitur Utama & Aksi
*   **Bilah Filter Pencarian:** Filter berdasarkan kata kunci produk, kategori, gudang spesifik, dan rentang tanggal.
*   **Tabel Mutasi Global:** Menampilkan kolom kode produk, nama barang, satuan, stok awal, total masuk (in), total keluar (out), penyesuaian (adj), dan saldo akhir produk.

---

## 3. INTEGRASI DATABASE & FORMULA SALDO
*   **Formula Perhitungan Saldo:**
    $$\text{Saldo Akhir} = \text{Stok Awal} + \text{Total In} - \text{Total Out} + \text{Adjustment}$$
*   **Query Rekapitulasi:** Menggunakan query grouping pada tabel `stock_ledgers` untuk merangkum total pergerakan stok per produk secara cepat dan efisien.
*   **Ekspor Laporan:** Tombol ekspor Excel, Word, dan print PDF untuk pelaporan kepada manajemen perusahaan.
