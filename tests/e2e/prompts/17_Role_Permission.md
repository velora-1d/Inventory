# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN ROLE & PERMISSION

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Role & Permission** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD Hak Akses (Role), manajemen matriks permission (checkbox matrix), detail list permission ter-assign, perlindungan role bawaan (*built-in roles*), dan konfirmasi hapus.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Role & Permission" beserta deskripsi.
    *   Tombol "+ Tambah Role".
*   **Role Cards List:**
    *   Daftar kartu Role menampilkan: *Nama Role*, *Jumlah Anggota (users count)*, *Summary Permission Group* (badge nama modul & list aksi yang diijinkan).
*   **Form Input Modal (Permission Matrix):**
    *   Field input: *Nama Role*.
    *   Grid Matrix Permission: Baris modul (Dashboard, Data Barang, dsb) dan kolom aksi (*Lihat*, *Tambah*, *Ubah*, *Hapus*, *Input/Konfirm*).
    *   Kotak centang (checkbox) interaktif di setiap perempatan matriks untuk memberikan/mencabut izin akses secara spesifik.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah Role Baru & Assign Matrix (Create):**
    *   Klik tombol "+ Tambah Role". Pastikan modal matrix muncul.
    *   Ketik nama role baru (misal: `Supervisor Gudang [Random]`).
    *   Pada baris modul "Data Barang", centang aksi "Lihat" dan "Tambah".
    *   Pada baris modul "Laporan", centang aksi "Lihat".
    *   Klik tombol "Simpan" dan pastikan modal tertutup.
    *   Verifikasi role baru tampil di daftar kartu dengan summary permission yang sesuai dengan yang telah dicentang.
2.  **Skenario Ubah Permission Matrix (Update):**
    *   Cari kartu role baru yang telah dibuat. Klik tombol aksi "Edit" (ikon pensil/ubah).
    *   Di dalam modal, centang tambahan izin (misal: centang "Ubah" pada modul "Data Barang").
    *   Klik tombol "Simpan" dan verifikasi summary badge di kartu role bertambah sesuai perubahan.
3.  **Skenario Proteksi Built-in Roles (Constraint Edge Case):**
    *   Verifikasi bahwa untuk role bawaan sistem (`super-admin`, `admin`, `staff`), tombol "Hapus" tidak muncul atau dinonaktifkan guna menghindari kerusakan data sistem bawaan.
4.  **Skenario Hapus Role (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) pada role kustom terkait.
    *   Pastikan dialog konfirmasi penghapusan muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan role kustom tersebut terhapus dari daftar kartu.
