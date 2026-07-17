# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN GUDANG/LOKASI

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Gudang/Lokasi** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD lengkap, validasi form modal, filter status, pencarian, dan dialog konfirmasi hapus.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Gudang/Lokasi" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Gudang".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari nama atau kode gudang...").
    *   Dropdown pilihan status: *Semua Status*, *Aktif*, dan *Nonaktif*.
    *   Tombol "Cari".
*   **Form Input Modal (Popup):**
    *   Field input: *Kode Gudang* (`#modal-code` - terisi otomatis), *Nama Gudang* (`#modal-name`), *Penanggung Jawab / PIC* (`#modal-pic` - dropdown list user), *Alamat* (`#modal-address` - textarea).
    *   Pilihan status keaktifan (Aktif/Nonaktif).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah Gudang Baru (Create):**
    *   Klik tombol "+ Tambah Gudang". Pastikan modal input muncul.
    *   Ketik nama gudang unik (misal: `Gudang Cabang E2E [Random]`).
    *   Pilih user PIC dari dropdown, dan isi alamat gudang.
    *   Klik tombol "Simpan". Pastikan modal tertutup.
    *   Verifikasi gudang baru tampil pada daftar tabel.
2.  **Skenario Filter & Pencarian:**
    *   Ketik nama gudang yang baru dibuat di kolom pencarian.
    *   Klik tombol "Cari" atau tekan Enter. Pastikan tabel terfilter dan hanya menampilkan baris gudang tersebut.
    *   Ubah filter status ke "Nonaktif" dan pastikan baris gudang menghilang karena status defaultnya adalah "Aktif".
    *   Kembalikan filter status ke "Semua Status" untuk menampilkan kembali data.
3.  **Skenario Ubah Gudang (Update):**
    *   Cari gudang yang telah dibuat. Klik tombol aksi "Edit" (ikon pensil/ubah) dengan `title="Ubah"`.
    *   Ubah nama gudang dengan menambahkan kata `Updated`.
    *   Klik tombol "Simpan" dan pastikan modal tertutup.
    *   Verifikasi nama gudang baru terupdate di baris tabel.
4.  **Skenario Hapus Gudang (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"` pada baris gudang terkait.
    *   Pastikan dialog konfirmasi penghapusan muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan data gudang tersebut hilang dari daftar tabel.
