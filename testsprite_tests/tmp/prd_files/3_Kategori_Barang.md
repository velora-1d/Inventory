# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN KATEGORI BARANG

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Kategori Barang** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD lengkap, validasi form modal, filter status, pencarian, dan dialog konfirmasi hapus.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Kategori Barang" beserta deskripsi klasifikasi barang.
    *   Tampilkan tombol "+ Tambah Kategori".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari kategori...").
    *   Dropdown pilihan status: *Semua Status*, *Aktif*, dan *Nonaktif*.
    *   Tombol "Cari".
*   **Form Input Modal (Popup):**
    *   Field input: *Nama Kategori* (`#modal-name`), *Deskripsi* (textarea).
    *   Pilihan status keaktifan (Aktif/Nonaktif).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah Kategori Baru (Create):**
    *   Klik tombol "+ Tambah Kategori". Pastikan modal input muncul.
    *   Ketik nama kategori unik (misal: `Kategori Test [Random]`).
    *   Isi kolom deskripsi dengan keterangan singkat.
    *   Klik tombol "Simpan". Pastikan modal tertutup.
    *   Verifikasi kategori baru tampil pada daftar tabel.
2.  **Skenario Filter & Pencarian:**
    *   Ketik nama kategori yang baru dibuat di kolom pencarian.
    *   Klik tombol "Cari" atau tekan Enter. Pastikan tabel terfilter dan hanya menampilkan baris kategori tersebut.
    *   Ubah filter status ke "Nonaktif" dan pastikan baris kategori menghilang karena status defaultnya adalah "Aktif".
    *   Kembalikan filter status ke "Semua Status" untuk menampilkan kembali data.
3.  **Skenario Ubah Kategori (Update):**
    *   Cari kategori yang telah dibuat. Klik tombol aksi "Edit" (ikon pensil/ubah) dengan `title="Ubah"`.
    *   Ubah nama kategori dengan menambahkan kata `Updated`.
    *   Klik tombol "Simpan" dan pastikan modal tertutup.
    *   Verifikasi nama kategori baru terupdate di baris tabel.
4.  **Skenario Hapus Kategori (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"` pada baris kategori terkait.
    *   Pastikan dialog konfirmasi penghapusan muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan data kategori tersebut hilang dari daftar tabel.
