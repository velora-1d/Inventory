# DOKUMENTASI SISTEM: 19. PENGATURAN AKUN

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Pengaturan Akun** (Profil) pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):**
    *   Profile: `Route::patch('/settings/profile', [UserSettingsController::class, 'updateProfile'])->name('settings.user.profile');`
    *   Password: `Route::patch('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings.user.password');`
    *   PIN: `Route::patch('/settings/pin', [UserSettingsController::class, 'updatePin'])->name('settings.user.pin');`
*   **Controller:** `app/Http/Controllers/UserSettingsController.php` & `app/Http/Controllers/RoleSwitchController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Settings/UserSettings.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FITUR KEAMANAN
Menu ini mengontrol preferensi pengguna dan fitur keamanan tingkat tinggi:

### A. Informasi Profil & Kata Sandi
*   Pembaruan Nama dan Alamat Email.
*   Pembaruan Password menggunakan form input yang memiliki validasi visual eye toggle.

### B. PIN Keamanan 6-Digit (Admin Only)
*   Form pendaftaran PIN berisi **6 kolom input angka terpisah**.
*   Sistem dilengkapi Javascript auto-focus ke kolom berikutnya setelah angka dimasukkan, dan kembali ke kolom sebelumnya saat menekan tombol `Backspace`.

### C. Utilitas Tes Koneksi Sistem
*   Tombol "Tes Koneksi Database TiDB": Menghubungi remote TiDB Cloud Serverless menggunakan SSL certificate (`cacert.pem`) secara real-time.
*   Tombol "Tes Koneksi Cloud Storage S3": Menguji proses write dan delete file temporer ke RustFS/S3 storage.
*   Status koneksi dikomunikasikan secara visual melalui **Toast Alert Banner** (warna hijau sukses, merah gagal).

### D. Ganti Tampilan Peran (Role Switch)
*   Fitur simulasi bagi Admin untuk menguji sistem dengan peran lain (Staff Gudang, Kasir, Manager).
*   Proses pertukaran mewajibkan verifikasi PIN 6-digit.
*   Jika berhasil ditukar, banner peringatan berwarna amber ("Mode Tampilan Role Aktif") akan muncul di bagian atas halaman dengan tombol "Kembali ke Admin".

---

## 3. INTEGRASI DATABASE & ENKRIPSI
*   **Enkripsi PIN:** Kode PIN disimpan secara aman di database menggunakan metode enkripsi satu arah (Hash) yang tidak dapat dibaca kembali.
*   **Session Switching:** Laravel menyimpan peran tiruan di session server selama simulasi Role Switch aktif.
