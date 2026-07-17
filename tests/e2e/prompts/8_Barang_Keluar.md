# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - TRANSAKSI BARANG KELUAR (STOCK OUT)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Barang Keluar** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD transaksi, pembuatan draft transaksi, penambahan item multi-row, pengecekan stok tersedia (*Available Stock*) real-time per gudang, penghitungan subtotal & grand total dinamis, filtering tanggal/status, modal detail transaksi, dan approval flow (menyelesaikan transaksi dari draft ke completed).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Barang Keluar" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Transaksi".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari No. Transaksi/Referensi/Penerima...").
    *   Filter dropdown: *Status* (Semua/Draft/Selesai) dan *Gudang*.
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
    *   Tombol "Cari".
*   **Form Input Modal (Dynamic Items Row):**
    *   Header form: *Tanggal* (`#modal-date`), *Gudang Sumber* (`#modal-warehouse`), *Penerima* (`#modal-recipient`), *No. Referensi* (`#modal-reference_no`), *Catatan* (`#modal-notes`).
    *   Item rows: tombol "+ Tambah Item", dropdown *Produk*, label *Stok Tersedia* (menampilkan angka stok di gudang terpilih), dropdown *Satuan*, input *Qty*, input *Harga Jual*, dan subtotal otomatis.
    *   Teks Grand Total dinamis di bawah form list item.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Membuat Transaksi Baru (Simpan sebagai Draft):**
    *   Klik tombol "+ Tambah Transaksi". Pastikan modal input muncul.
    *   Isi Tanggal, pilih Gudang Sumber (misal: gudang yang sudah di-seed memiliki stok), dan masukkan Penerima.
    *   Klik "+ Tambah Item". Pilih produk dari dropdown.
    *   Verifikasi angka "Stok Tersedia" muncul (tidak bernilai `0` untuk produk yang memiliki stok).
    *   Ubah Qty menjadi `2` (harus di bawah atau sama dengan Stok Tersedia) dan verifikasi subtotal serta Grand Total otomatis terhitung.
    *   Klik tombol "Simpan Draft". Pastikan modal tertutup.
    *   Verifikasi transaksi baru tampil di tabel dengan status badge **Draft**.
2.  **Skenario Melengkapi Transaksi (Approve/Complete):**
    *   Pada baris transaksi draft tersebut, klik tombol aksi "Selesaikan" (ikon centang) dengan `title="Selesaikan Transaksi"`.
    *   Verifikasi dialog konfirmasi penyelesaian transaksi muncul.
    *   Klik "Selesaikan". Pastikan status transaksi berubah menjadi **Selesai** (Completed).
3.  **Skenario Validasi Melebihi Stok Tersedia (Edge Case):**
    *   Buat draft transaksi baru. Pilih produk dan masukkan Qty melebihi "Stok Tersedia".
    *   Coba simpan draft atau klik selesaikan, pastikan sistem memunculkan pesan validasi kesalahan stok tidak mencukupi.
4.  **Skenario Hapus Transaksi Draft:**
    *   Buat transaksi draft baru lainnya.
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"`.
    *   Klik konfirmasi "Hapus". Verifikasi transaksi terhapus dari tabel.
