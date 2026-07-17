# DOKUMENTASI SISTEM: 6. GUDANG/LOKASI

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Gudang/Lokasi** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('warehouses', WarehouseController::class);`
*   **Controller:** `app/Http/Controllers/WarehouseController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Warehouses/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini mengelola lokasi penyimpanan inventaris fisik barang:

### A. Fitur Utama & Aksi
*   **Bilah Pencarian:** Kolom filter pencarian berdasarkan nama gudang atau kode gudang.
*   **Tabel Data Gudang:** Menampilkan Nomor Urut, Kode Gudang, Nama Gudang, Penanggung Jawab (PIC), Alamat Gudang, Status, dan Tombol Aksi.

### B. Form Modal Input Gudang
*   **Kode Gudang (`#modal-code`):** Auto-generate format `GD-XXX`.
*   **Nama Gudang (`#modal-name`):** Identitas nama gudang (misal: Gudang Utama, Gudang Barat).
*   **Penanggung Jawab PIC (`#modal-pic`):** Pilihan dropdown list user aktif untuk ditugaskan memimpin gudang tersebut.
*   **Alamat:** Lokasi fisik gudang.

---

## 3. INTEGRASI DATABASE & ALUR CRUD
*   **Stok Multi-Gudang:** Setiap penambahan gudang baru secara otomatis mempersiapkan ruang pencatatan relasi `stocks` per produk.
*   **Keamanan Relasi:** Gudang yang masih menyimpan stok fisik barang tidak diijinkan untuk dihapus secara langsung.
