# DOKUMENTASI SISTEM: 5. SUPPLIER

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Supplier** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('suppliers', SupplierController::class);`
*   **Controller:** `app/Http/Controllers/SupplierController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Suppliers/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini mengelola data rekanan penyedia/pemasok barang masuk:

### A. Fitur Utama & Aksi
*   **Bilah Pencarian:** Kolom filter pencarian berdasarkan nama supplier, kode, email, atau kontak personal.
*   **Tabel Data Supplier:** Menampilkan Nomor Urut, Kode Supplier, Nama Mitra, Narahubung (Contact Person), Telepon & Email, Alamat, Status, dan Tombol Aksi.

### B. Form Modal Input Supplier
*   **Kode Supplier (`#modal-code`):** Auto-generate format `SPL-XXXX` yang aman dan terstandardisasi.
*   **Nama Supplier (`#modal-name`):** Nama perusahaan/badan usaha.
*   **Telepon & Email:** Kontak penanggung jawab supplier.
*   **Contact Person (`#modal-contact_person`):** Nama perwakilan narahubung.
*   **Alamat:** Lokasi kantor atau gudang supplier.

---

## 3. INTEGRASI DATABASE & ALUR CRUD
*   **Create/Update:** Menyimpan data kontak ke tabel `suppliers`.
*   **Delete:** Jika ada transaksi *Barang Masuk* yang tercatat berelasi dengan supplier tersebut, proses hapus dibatasi oleh database untuk menjaga validitas laporan mutasi historis.
