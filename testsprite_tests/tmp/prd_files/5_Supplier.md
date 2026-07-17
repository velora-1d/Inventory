# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN MITRA SUPPLIER

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Supplier** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD lengkap, validasi form modal, filter status, pencarian, dan dialog konfirmasi hapus.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Mitra Supplier" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Supplier".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari nama, kode, atau kontak...").
    *   Dropdown pilihan status: *Semua Status*, *Aktif*, dan *Nonaktif*.
    *   Tombol "Cari".
*   **Form Input Modal (Popup):**
    *   Field input: *Kode* (`#modal-code` - terisi otomatis), *Nama Supplier* (`#modal-name`), *Telepon* (`#modal-phone`), *Email* (`#modal-email`), *Contact Person* (`#modal-contact_person`), *Alamat* (`#modal-address` - textarea).
    *   Pilihan status keaktifan (Aktif/Nonaktif).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah Supplier Baru (Create):**
    *   Klik tombol "+ Tambah Supplier". Pastikan modal input muncul.
    *   Ketik nama supplier unik (misal: `PT Supplier Maju [Random]`).
    *   Isi kolom nomor telepon, email, nama narahubung (contact person), dan alamat.
    *   Klik tombol "Simpan". Pastikan modal tertutup.
    *   Verifikasi supplier baru tampil pada daftar tabel.
2.  **Skenario Filter & Pencarian:**
    *   Ketik nama supplier yang baru dibuat di kolom pencarian.
    *   Klik tombol "Cari" atau tekan Enter. Pastikan tabel terfilter dan hanya menampilkan baris supplier tersebut.
    *   Ubah filter status ke "Nonaktif" dan pastikan baris supplier menghilang karena status defaultnya adalah "Aktif".
    *   Kembalikan filter status ke "Semua Status" untuk menampilkan kembali data.
3.  **Skenario Ubah Supplier (Update):**
    *   Cari supplier yang telah dibuat. Klik tombol aksi "Edit" (ikon pensil/ubah) dengan `title="Ubah"`.
    *   Ubah nama supplier dengan menambahkan kata `Updated`.
    *   Klik tombol "Simpan" dan pastikan modal tertutup.
    *   Verifikasi nama supplier baru terupdate di baris tabel.
4.  **Skenario Hapus Supplier (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"` pada baris supplier terkait.
    *   Pastikan dialog konfirmasi penghapusan muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan data supplier tersebut hilang dari daftar tabel.
