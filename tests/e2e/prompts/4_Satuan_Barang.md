# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN SATUAN BARANG

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Satuan Barang** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD lengkap, validasi form modal, filter status, pencarian, dan dialog konfirmasi hapus.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Satuan Barang" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Satuan".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari nama atau simbol satuan...").
    *   Dropdown pilihan status: *Semua Status*, *Aktif*, dan *Nonaktif*.
    *   Tombol "Cari".
*   **Form Input Modal (Popup):**
    *   Field input: *Nama Satuan* (`#modal-name`), *Simbol* (`#modal-symbol`).
    *   Pilihan status keaktifan (Aktif/Nonaktif).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah Satuan Baru (Create):**
    *   Klik tombol "+ Tambah Satuan". Pastikan modal input muncul.
    *   Ketik nama satuan unik (misal: `Box Unit [Random]`).
    *   Ketik simbol satuan (misal: `box`).
    *   Klik tombol "Simpan". Pastikan modal tertutup.
    *   Verifikasi satuan baru tampil pada daftar tabel.
2.  **Skenario Filter & Pencarian:**
    *   Ketik simbol satuan yang baru dibuat di kolom pencarian.
    *   Klik tombol "Cari" atau tekan Enter. Pastikan tabel terfilter dan hanya menampilkan baris satuan tersebut.
    *   Ubah filter status ke "Nonaktif" dan pastikan baris satuan menghilang karena status defaultnya adalah "Aktif".
    *   Kembalikan filter status ke "Semua Status" untuk menampilkan kembali data.
3.  **Skenario Ubah Satuan (Update):**
    *   Cari satuan yang telah dibuat. Klik tombol aksi "Edit" (ikon pensil/ubah) dengan `title="Ubah"`.
    *   Ubah nama satuan dengan menambahkan kata `Updated`.
    *   Klik tombol "Simpan" dan pastikan modal tertutup.
    *   Verifikasi nama satuan baru terupdate di baris tabel.
4.  **Skenario Hapus Satuan (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"` pada baris satuan terkait.
    *   Pastikan dialog konfirmasi penghapusan muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan data satuan tersebut hilang dari daftar tabel.
