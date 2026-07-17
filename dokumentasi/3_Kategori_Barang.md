# DOKUMENTASI SISTEM: 3. KATEGORI BARANG

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Kategori Barang** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('categories', CategoryController::class);`
*   **Controller:** `app/Http/Controllers/CategoryController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Categories/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini mengelompokkan barang berdasarkan kategorisasi produk:

### A. Fitur Utama & Aksi
*   **Bilah Pencarian & Filter:**
    *   Kolom pencarian nama kategori.
    *   Dropdown status keaktifan (Semua/Aktif/Nonaktif).
*   **Tabel Data Kategori:** Menampilkan Nomor Urut, Nama Kategori, Deskripsi, Status Badge (hijau untuk Aktif, merah untuk Nonaktif), dan Tombol Aksi (Ubah & Hapus).

### B. Form Modal Input Kategori
*   **Nama Kategori (`#modal-name`):** Input wajib diisi.
*   **Deskripsi (Textarea):** Keterangan tambahan opsional.
*   **Status Keaktifan:** Opsi sakelar status (Aktif/Nonaktif).

---

## 3. INTEGRASI DATABASE & ALUR CRUD
*   **Create:** Kategori disimpan ke tabel `categories`.
*   **Delete:** Jika kategori dihapus, database akan menghapus baris terkait. Transaksi dilindungi pengaman relasi agar kategori yang masih digunakan oleh produk tidak dapat dihapus sembarangan.
