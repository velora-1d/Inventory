# DOKUMENTASI SISTEM: 1. DASHBOARD

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari **Halaman Dashboard** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');`
*   **Controller:** `app/Http/Controllers/DashboardController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Dashboard.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue` (Persistent Layout)

---

## 2. DESAIN ANTARMUKA & UI/UX (KPI OVERVIEW)
Dashboard dirancang untuk memberikan informasi persediaan barang secara cepat dan real-time:

### A. KPI Cards Grid (8 Cards)
*   **Total SKU Aktif:** Jumlah jenis item barang (SKU) yang aktif dalam sistem.
*   **Nilai Persediaan:** Total valuasi keuangan aset stok yang tersimpan di gudang menggunakan format Rupiah (`Rp XX.XXX.XXX`).
*   **Total Qty Barang:** Jumlah total kuantitas fisik unit seluruh barang.
*   **Stok Menipis:** Indikator barang yang berada di bawah limit minimum (berwarna merah sebagai sinyal kritis).
*   **Total Gudang:** Jumlah lokasi/gudang penyimpanan aktif.
*   **Mitra Supplier:** Jumlah partner pemasok barang yang terdaftar.
*   **Aktivitas Mutasi:** Jumlah pencatatan transaksi keluar-masuk stok.
*   **Transaksi Draft:** Jumlah transaksi yang masih berstatus draft (belum terkonfirmasi).

### B. Grafik Aktivitas Mutasi (Line Chart SVG)
*   Menampilkan visualisasi grafik pergerakan barang masuk (garis hijau keemasan) dan barang keluar (garis merah) secara periodik.
*   Grafik di-render menggunakan SVG koordinat dinamis agar performa rendering sangat ringan di browser.

### C. Daftar Barang Stok Menipis
*   Tabel bento-grid yang memuat daftar produk yang kuantitasnya saat ini berada di bawah batas minimum (`min_stock`) untuk segera ditindaklanjuti.

---

## 3. ALUR FUNGSIONALITAS & FILTER GUDANG

*   **Penyaringan Gudang (Filter Warehouse):**
    *   Pengguna dengan hak akses penuh (Super Admin/Manager) dapat memfilter seluruh KPI data statistik dan grafik untuk gudang tertentu menggunakan dropdown filter di bagian kanan atas.
    *   Sistem mengirimkan request filter lewat parameter query string `?warehouse_id=X` secara *asynchronous* melalui Inertia router (`preserveState: true`).
*   **Pembatasan Hak Akses (Role Limitation):**
    *   Jika pengguna masuk memiliki peran yang terkunci ke satu gudang (misal: Staff Gudang Cabang A), dropdown filter **disembunyikan** secara otomatis.
    *   Sebagai gantinya, sistem menampilkan label statis: `Akses Terbatas: [Nama Gudang]`.
