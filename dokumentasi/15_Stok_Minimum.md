# DOKUMENTASI SISTEM: 15. STOK MINIMUM

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Stok Minimum** (Stok Rendah) pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::get('/reports/low-stock', [LowStockController::class, 'index'])->name('reports.low-stock');`
*   **Controller:** `app/Http/Controllers/Reports/LowStockController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Reports/LowStock.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & KARTU STATUS
Menu ini memonitoring produk-produk kritis yang stok fisiknya menipis atau kosong:

### A. Fitur Utama & Aksi
*   **Tiga Kartu Status Alert:**
    1.  *Kritis (Stok 0):* Produk yang benar-benar habis di gudang (warna merah).
    2.  *Hampir Habis:* Produk yang stoknya di bawah batas minimum (warna kuning).
    3.  *Total Item:* Jumlah total SKU yang bermasalah.
*   **Progress Bar Pemenuhan Stok:** Menampilkan persentase perbandingan stok fisik dengan batas minimum yang disarankan secara visual.

---

## 3. INTEGRASI DATABASE & LOGIKA STATUS
*   **Kriteria Status Stok Rendah:**
    *   *Kritis (Critical):* $\text{Stok} = 0$.
    *   *Peringatan (Warning):* $0 < \text{Stok} \le \text{Batas Stok Minimum}$.
*   **Ekspor Data:** Tombol cetak PDF, Excel, dan Word disediakan untuk mempermudah bagian pembelian (purchasing) membuat pengajuan pengadaan barang baru.
