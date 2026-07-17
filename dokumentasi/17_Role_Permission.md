# DOKUMENTASI SISTEM: 17. ROLE & PERMISSION

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Role & Permission** pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::resource('roles', RoleController::class);`
*   **Controller:** `app/Http/Controllers/RoleController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Roles/Index.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & MATRIKS HAK AKSES
Menu ini mengelola pemetaan hak akses granular berdasarkan role pengguna:

### A. Fitur Utama & Aksi
*   **Kartu Role (Role Cards):** Menampilkan nama role, jumlah user terdaftar, detail list izin yang aktif per modul, dan tombol aksi.
*   **Grid Matriks Izin (Permission Matrix):**
    *   Tabel interaktif yang memetakan modul sistem (Baris) dengan jenis tindakan (Kolom): *Lihat (view)*, *Tambah (create)*, *Ubah (update)*, *Hapus (delete)*, dan *Input/Konfirm (input)*.
    *   Menggunakan check box interaktif untuk memberikan atau mencabut izin secara dinamis.

---

## 3. INTEGRASI DATABASE & PROTEKSI
*   **Spatie Integration:** Sinkronisasi hak akses menggunakan relational mapping database spatie (`role_has_permissions`).
*   **Proteksi Role Bawaan (Built-in Security):** Role dasar sistem (`super-admin`, `admin`, `staff`) dikunci secara ketat agar tidak dapat dihapus atau dimodifikasi strukturnya oleh user lain guna menghindari kerusakan hak akses sistem.
