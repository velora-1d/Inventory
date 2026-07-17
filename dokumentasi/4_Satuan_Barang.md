# DOKUMENTASI SISTEM: 4. SATUAN BARANG

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Satuan Barang** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('units', UnitController::class);`
*   **Controller:** `app/Http/Controllers/UnitController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Units/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini mengelola standar satuan pengukuran barang:

### A. Fitur Utama & Aksi
*   **Bilah Pencarian:** Filter pencarian berdasarkan nama atau simbol satuan.
*   **Tabel Data Satuan:** Menampilkan Nama Satuan, Simbol Unit (misal: `pcs`, `dus`, `kg` - diformat tebal/mono), Status, dan Tombol Aksi.

### B. Form Modal Input Satuan
*   **Nama Satuan (`#modal-name`):** Nama lengkap unit (misal: `Pieces`).
*   **Simbol Satuan (`#modal-symbol`):** Singkatan unit (misal: `pcs`).
*   **Status Keaktifan:** Opsi sakelar status (Aktif/Nonaktif).

---

## 3. INTEGRASI DATABASE & ALUR CRUD
*   **Validasi Keunikan:** Kolom `symbol` memiliki index unik di database untuk mencegah terjadinya duplikasi satuan.
*   **Keamanan Relasi:** Jika satuan terikat sebagai `base_unit_id` pada suatu produk, penghapusan satuan akan diblokir oleh integritas database (*foreign key constraint violation*).
