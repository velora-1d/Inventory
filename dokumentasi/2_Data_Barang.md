# DOKUMENTASI SISTEM: 2. DATA BARANG

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Data Barang** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('products', ProductController::class);`
*   **Controller:** `app/Http/Controllers/ProductController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Products/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM UTAMA
Menu Data Barang mengelola Master SKU (Stock Keeping Unit) produk:

### A. Fitur Utama & Aksi
*   **Bilah Filter Multi-Parameter:**
    *   Pencarian berdasarkan *Nama* atau *SKU*.
    *   Dropdown penyaringan berdasarkan *Kategori*, *Gudang Default*, dan *Status Keaktifan* (Aktif/Nonaktif).
*   **Grid Tabel Utama:** Menampilkan info SKU, Nama Produk, Kategori, Harga Beli, Harga Jual, Stok Minimum, Gudang Default, dan Status.

### B. Form Modal Tambah & Ubah Barang
*   **SKU:** Auto-generate kode unik (`SKU-XXXXXX`) yang dapat dimodifikasi secara manual.
*   **Nama Barang & Kategori:** Pengelompokan barang.
*   **Satuan Utama:** Menghubungkan satuan dasar (seperti `Pcs` atau `Box`).
*   **Harga Beli & Harga Jual:** Parameter harga dasar.
*   **Stok Minimum:** Batas minimal alarm/peringatan stok menipis.
*   **Foto Barang:** Upload gambar fisik produk dengan preview instan sebelum diunggah.

---

## 3. INTEGRASI DATABASE & ALUR CRUD
*   **Create/Update:** Memanfaatkan library penyimpanan file Laravel (`Storage::disk('public')`) untuk mengelola unggahan foto produk. File foto disimpan di folder `/storage/products/` dan di-link ke folder `/public/storage/` agar dapat diakses oleh browser.
*   **Delete:** Dilengkapi dengan pop-up konfirmasi `showConfirm` dinamis. Sebelum produk dihapus, file gambar terkait di server akan dihapus secara otomatis untuk mencegah penumpukan berkas sampah.
