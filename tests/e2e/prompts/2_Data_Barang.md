# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN DATA BARANG (PRODUCT SKU)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Data Barang** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD lengkap, manajemen modal input data SKU, filter pencarian multi-parameter, upload gambar, dan validasi data.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Data Barang / SKU" beserta deskripsi.
    *   Tampilkan tombol "+ Tambah Barang" untuk memicu popup input.
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari nama atau SKU...").
    *   Dropdown pilihan filter berdasarkan: *Kategori*, *Gudang*, dan *Status* (Semua/Aktif/Nonaktif).
    *   Tombol "Reset" filter.
*   **Form Input Modal (Popup):**
    *   Verifikasi field input utama: *SKU* (terisi otomatis/dapat diedit), *Nama Barang*, *Kategori*, *Satuan Utama*, *Harga Beli*, *Harga Jual*, *Stok Minimum*, dan *Gudang Default*.
    *   Upload foto barang beserta preview gambar.
    *   Status radio/switch (Aktif/Nonaktif).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Tambah Barang Baru (Create):**
    *   Klik tombol "+ Tambah Barang". Pastikan modal input muncul.
    *   Ketik nama barang unik (misal: `Kopi Robusta E2E [Random]`).
    *   Pilih opsi Kategori, Satuan Utama, dan Gudang Default dari dropdown pilihan yang tersedia.
    *   Ketik nominal *Harga Beli*, *Harga Jual*, dan *Stok Minimum*.
    *   Klik tombol "Simpan" dan pastikan modal tertutup.
    *   Verifikasi barang baru masuk ke baris tabel teratas.
2.  **Skenario Filter & Pencarian:**
    *   Ketik sebagian nama barang yang baru dibuat di kolom pencarian.
    *   Klik tombol "Cari" atau tekan Enter. Pastikan tabel terfilter dan hanya menampilkan produk tersebut.
    *   Pilih filter kategori yang berbeda dan pastikan produk tersebut menghilang dari hasil pencarian.
    *   Klik tombol "Reset" filter dan pastikan seluruh daftar barang termuat ulang.
3.  **Skenario Ubah Data Barang (Update):**
    *   Cari barang yang telah dibuat. Klik tombol aksi "Edit" (ikon pensil/ubah) pada baris tersebut.
    *   Ubah nilai *Harga Jual* dan tambahkan teks di *Deskripsi*.
    *   Klik tombol "Simpan" dan pastikan data terupdate di baris tabel.
4.  **Skenario Hapus Data Barang (Delete):**
    *   Klik tombol aksi "Hapus" (ikon sampah) pada baris barang tersebut.
    *   Verifikasi modal dialog konfirmasi konfirmasi hapus muncul.
    *   Klik tombol konfirmasi "Hapus" di dalam dialog.
    *   Pastikan produk tersebut hilang secara permanen dari daftar tabel.
