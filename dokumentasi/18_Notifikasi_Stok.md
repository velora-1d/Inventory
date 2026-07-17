# DOKUMENTASI SISTEM: 18. NOTIFIKASI STOK

Dokumentasi ini menjelaskan secara mendalam arsitektur, antarmuka, fungsionalitas, keamanan, dan alur kerja teknis dari menu **Notifikasi Stok** (Pengaturan Notifikasi) pada Aplikasi Inventory.

---

## 1. STRUKTUR & METADATA FILE
*   **Rute Web (Laravel):** `Route::put('/settings/notifications', [NotificationSettingController::class, 'update'])->name('notification-settings.update');`
*   **Controller:** `app/Http/Controllers/NotificationSettingController.php`
*   **File UI (Vue 3 / Inertia):** `resources/js/Pages/Notifications/Settings.vue`
*   **Layout Utama:** `resources/js/Layouts/AuthenticatedLayout.vue`

---

## 2. DETAIL ANTARMUKA & FORM INPUT
Menu ini mengontrol jalur komunikasi pemberitahuan sistem:

### A. Fitur Utama & Aksi
*   **Daftar Sakelar Notifikasi (Toggle List):**
    1.  *Alert Stok Rendah:* Notifikasi otomatis saat stok produk menyentuh batas minimum.
    2.  *Notifikasi Barang Masuk:* Notifikasi saat transaksi masuk diselesaikan.
    3.  *Notifikasi Barang Keluar:* Notifikasi saat transaksi keluar diselesaikan.
    4.  *Notifikasi Browser:* Push notification di dalam browser.
    5.  *Laporan Ringkasan Harian:* Pengiriman rekap harian secara otomatis.
*   **Form Input:**
    *   *Batas Stok Minimum:* Nilai ambang batas global.
    *   *Jam Kirim Laporan Harian:* Input waktu pengiriman email otomatis.

---

## 3. INTEGRASI DATABASE & EMAIL BROADCAST
*   **Penyimpanan Pengaturan:** Pengaturan disimpan di tabel `notification_settings` yang terikat per user atau secara global.
*   **Proses Penyimpanan:** Ketika disimpan, UI menampilkan *Success Toast* hijau yang akan hilang perlahan selama 3 detik.
*   **Email Queueing:** Integrasi mailer Laravel (`Mail::queue`) digunakan untuk memproses broadcast notifikasi secara asynchronous agar tidak membebani performa request server utama.
