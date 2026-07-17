# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN NOTIFIKASI STOK

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Notifikasi Stok** (Pengaturan Notifikasi) pada sistem Inventory. Pengujian wajib mencakup verifikasi status toggle settings (on/off switch), input batas ambang stok minimum (*Low Stock Threshold*), input waktu kirim laporan harian (*Daily Report Time*), proses pengiriman form (Submit), dan pesan notifikasi sukses (Success Toast).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Toast Status:**
    *   Verifikasi judul halaman "Pengaturan Notifikasi" beserta deskripsinya.
    *   Success Toast ("Pengaturan berhasil disimpan!") yang muncul setelah proses simpan berhasil.
*   **Toggles List:**
    *   Daftar opsi sakelar (toggle):
        1.  *Alert Stok Rendah*
        2.  *Notifikasi Barang Masuk*
        3.  *Notifikasi Barang Keluar*
        4.  *Notifikasi Browser*
        5.  *Laporan Ringkasan Harian*
*   **Form Input Nilai:**
    *   Kolom input angka *Batas Stok Minimum* (low stock threshold).
    *   Kolom input waktu *Jam Kirim Laporan Harian* (daily report time).
    *   Tombol "Simpan Pengaturan".

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Mengubah Pilihan Sakelar (Toggle Settings):**
    *   Klik pada salah satu sakelar (misal: aktifkan atau nonaktifkan "Alert Stok Rendah").
    *   Pastikan status state form/toggle berganti.
2.  **Mengubah Nilai Parameter Threshold & Waktu:**
    *   Masukkan angka threshold baru di kolom batas stok minimum (misal: `10`).
    *   Masukkan jam baru pada pilihan waktu kirim laporan harian (misal: `08:00`).
3.  **Menyimpan Pengaturan (Submit & Success State):**
    *   Klik tombol "Simpan Pengaturan".
    *   Pastikan request terkirim dan Toast pesan sukses berwarna hijau ("Pengaturan berhasil disimpan!") muncul di atas form.
    *   Tunggu selama 3 detik, dan verifikasi Toast pesan sukses menghilang secara otomatis.
