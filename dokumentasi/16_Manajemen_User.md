# DOKUMENTASI SISTEM: 16. MANAJEMEN USER

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Manajemen User** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('users', UserController::class);`
*   **Controller:** `app/Http/Controllers/UserController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Users/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM MODAL
Menu ini mengelola daftar pengguna sistem beserta tingkat otorisasi aksesnya:

### A. Fitur Utama & Aksi
*   **Bilah Pencarian & Filter:** Filter pencarian berdasarkan nama/email dan dropdown filter role.
*   **Grid Kartu User (User Cards):** Menampilkan avatar inisial nama, nama lengkap, email, role badge berwarna (misal: merah untuk super-admin, biru untuk admin, hijau untuk staff), nama gudang penugasan, dan tombol Ubah/Hapus.

### B. Form Modal Tambah/Ubah User
*   **Identitas Akun:** Nama Lengkap dan Alamat Email.
*   **Keamanan Akun:** Input Password & Konfirmasi Password (dilengkapi visual show/hide toggle).
*   **Pembatasan Gudang:** Opsi pilihan Gudang Penugasan untuk membatasi ruang gerak operasional staff.
*   **Penugasan Role:** Dropdown pilihan role otorisasi.

---

## 3. INTEGRASI DATABASE & ALUR CRUD
*   **Enkripsi Kata Sandi:** Laravel otomatis mengenkripsi password menggunakan `Hash::make()` sebelum disimpan ke database.
*   **Integritas Otorisasi:** Mengintegrasikan library `spatie/laravel-permission` untuk sinkronisasi role ke user baru secara instan di tabel `model_has_roles`.
