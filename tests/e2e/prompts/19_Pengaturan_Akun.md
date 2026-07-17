# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN PENGATURAN AKUN (PROFIL)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Pengaturan Akun** (Profil) pada sistem Inventory. Pengujian wajib mencakup verifikasi pengubahan informasi profil, pembaruan kata sandi, pengaturan kode keamanan PIN 6-digit (untuk Admin), fungsionalitas tukar hak akses peran (*Role Switch*), dan validasi visual (teks hitam, background krem `#faf6eb`, rata kiri-kanan).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Struktur Layout:**
    *   Verifikasi judul halaman "Pengaturan Akun" beserta deskripsinya.
    *   Pastikan margin halaman seimbang dan **rata kiri-kanan (justified/centered container)**.
    *   Verifikasi teks berwarna hitam pekat di mode terang, dengan background kontras berwarna krem hangat (`#faf6eb`/`bg-bg-warm`).
*   **Form Sections:**
    *   *Informasi Profil:* Kolom input *Nama Lengkap* dan *Alamat Email*.
    *   *Ubah Kata Sandi:* Kolom input *Kata Sandi Saat Ini*, *Kata Sandi Baru*, dan *Konfirmasi Kata Sandi Baru* (wajib menggunakan komponen `PasswordInput` dengan fitur mata toggle show/hide).
    *   *PIN Keamanan (Khusus Admin):* Input PIN 6-digit terpisah (6 buah box input angka individual) dengan auto-focus ke kolom berikutnya.
    *   *Simulasi Tukar Peran (Role Switch - Khusus Admin):* Form dropdown pilihan Role dan input PIN 6-digit pengaman.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Pembaruan Profil:**
    *   Ubah nilai pada kolom *Nama Lengkap*.
    *   Klik tombol "Simpan" pada panel Informasi Profil.
    *   Pastikan pesan sukses pembaruan profil terkirim ke server dan UI menampilkan nama terbaru.
2.  **Skenario Ubah Kata Sandi (Dengan Validasi Mata Toggle):**
    *   Ketik password saat ini, password baru, dan konfirmasi password baru.
    *   Klik tombol mata pada kolom input untuk memverifikasi karakter password terlihat jelas (tipe berubah dari `password` ke `text`).
    *   Klik tombol "Perbarui Sandi".
3.  **Skenario Pengaturan PIN 6-Digit (Admin Only):**
    *   Ketik password admin, lalu masukkan 6-digit PIN (misal: `123456`) dan konfirmasi PIN.
    *   Pastikan fokus kursor berpindah otomatis ke box berikutnya setelah memasukkan 1 angka.
    *   Klik tombol "Simpan PIN".
5.  **Skenario Utilitas Tes Koneksi Sistem:**
    *   Klik tombol "Tes Koneksi Database TiDB".
    *   Pastikan tombol memunculkan indikator loading (spin).
    *   Verifikasi bahwa Toast Alert Banner berwarna hijau muncul dengan pesan sukses koneksi database.
    *   Klik tombol "Tes Koneksi Cloud Storage S3".
    *   Pastikan tombol memunculkan indikator loading (spin).
    *   Verifikasi bahwa Toast Alert Banner berwarna hijau muncul dengan pesan sukses koneksi S3/RustFS.
6.  **Skenario Tukar Peran (Role Switch):**
    *   Pilih salah satu Role (misal: `Staff Gudang`).
    *   Masukkan PIN 6-digit yang telah didaftarkan.
    *   Klik tombol "Ganti Tampilan Peran".
    *   Pastikan sistem berhasil dialihkan dan memunculkan banner status "Mode Tampilan Role Aktif" (berwarna amber) di bagian atas halaman.
    *   Uji tombol "Kembali ke Admin" dengan memasukkan PIN untuk memulihkan hak akses penuh.
