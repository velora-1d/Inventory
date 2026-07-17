# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN MANAJEMEN USER

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Manajemen User** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD lengkap akun user, validasi form modal (termasuk validasi pencocokan password), dropdown multi-pilihan role, dropdown penugasan gudang (*Warehouse ID*), filtering, pencarian, dan dialog konfirmasi hapus.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Manajemen User" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah User".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari nama / email...").
    *   Dropdown pilihan filter berdasarkan: *Role* (Semua Role/Admin/Staff Gudang/dsb).
    *   Tombol "Cari".
*   **User Grid/Cards Layout:**
    *   Verifikasi grid list user menampilkan: *Avatar inisial nama*, *Nama*, *Email*, *Role badge* (berwarna sesuai tingkat hak akses), dan *Nama Gudang penugasan* (jika ada).
*   **Form Input Modal (Popup):**
    *   Field input: *Nama Lengkap*, *Email*, *Kata Sandi* (wajib ada ikon mata toggle visibility), *Konfirmasi Sandi* (wajib ada ikon mata), dropdown *Hak Akses (Role)*, dropdown *Penempatan Gudang*.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah User Baru (Create):**
    *   Klik tombol "+ Tambah User". Pastikan modal input muncul.
    *   Ketik nama user unik (misal: `Staff E2E Test [Random]`).
    *   Ketik email baru unik (misal: `staff-e2e-[random]@inventory.com`).
    *   Ketik sandi valid (misal: `password`) dan pastikan password diketik cocok di kolom konfirmasi sandi.
    *   Pilih Role (misal: `Staff Gudang`) dan tempatkan di salah satu Gudang.
    *   Klik tombol "Simpan". Pastikan modal tertutup.
    *   Verifikasi user baru tampil di daftar grid dengan inisial avatar yang sesuai.
2.  **Skenario Validasi Sandi Tidak Cocok (Edge Case):**
    *   Buka modal Tambah User.
    *   Masukkan sandi yang berbeda di kolom sandi dan konfirmasi sandi.
    *   Klik simpan dan verifikasi error alert validasi penolakan muncul.
3.  **Skenario Filter & Pencarian:**
    *   Ketik email user baru di kolom pencarian.
    *   Klik "Cari" atau tekan Enter. Pastikan hanya user tersebut yang tampil.
4.  **Skenario Ubah User (Update):**
    *   Cari user yang baru dibuat, klik tombol aksi "Edit" (ikon pensil/ubah).
    *   Ubah *Nama Lengkap* user.
    *   Klik "Simpan" dan pastikan data nama terupdate di grid.
5.  **Skenario Hapus User (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) pada user terkait.
    *   Pastikan dialog konfirmasi penghapusan muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan data user tersebut terhapus dari daftar.
