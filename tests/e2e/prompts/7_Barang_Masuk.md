# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - TRANSAKSI BARANG MASUK (STOCK IN)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Barang Masuk** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD transaksi, pembuatan draft transaksi, penambahan item multi-row, penghitungan subtotal & grand total dinamis, filtering tanggal/status, modal detail transaksi, dan approval flow (menyelesaikan transaksi dari draft ke completed).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Barang Masuk" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Transaksi".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari No. Transaksi/Referensi...").
    *   Filter dropdown: *Status* (Semua/Draft/Selesai) dan *Gudang*.
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
    *   Tombol "Cari".
*   **Form Input Modal (Dynamic Items Row):**
    *   Header form: *Tanggal* (`#modal-date`), *Supplier* (`#modal-supplier`), *Gudang Penerima* (`#modal-warehouse`), *No. Referensi* (`#modal-reference_no`), *Catatan* (`#modal-notes`).
    *   Item rows: tombol "+ Tambah Item" untuk menambah baris produk baru, dropdown pilihan *Produk*, dropdown *Satuan*, input *Qty*, input *Harga Beli*, dan subtotal otomatis.
    *   Teks Grand Total dinamis di bawah form list item.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Membuat Transaksi Baru (Simpan sebagai Draft):**
    *   Klik tombol "+ Tambah Transaksi". Pastikan modal input muncul.
    *   Isi Tanggal, pilih Supplier, pilih Gudang Penerima, dan masukkan No. Referensi.
    *   Klik "+ Tambah Item". Pilih produk dari dropdown (pastikan unit dan harga terisi otomatis secara default).
    *   Ubah Qty menjadi `5` dan verifikasi subtotal serta Grand Total otomatis ter-recalculating.
    *   Klik tombol "Simpan Draft". Pastikan modal tertutup.
    *   Verifikasi transaksi baru tampil di tabel dengan status badge **Draft**.
2.  **Skenario Melihat Detail Transaksi:**
    *   Cari transaksi draft yang baru dibuat.
    *   Klik tombol aksi "Detail" (ikon mata) dengan `title="Detail"`.
    *   Verifikasi modal detail muncul dengan rincian data transaksi dan daftar item yang lengkap.
3.  **Skenario Melengkapi Transaksi (Approve/Complete):**
    *   Pada baris transaksi draft tersebut, klik tombol aksi "Selesaikan" (ikon centang) dengan `title="Selesaikan Transaksi"`.
    *   Verifikasi dialog konfirmasi penyelesaian transaksi muncul.
    *   Klik "Selesaikan". Pastikan status transaksi berubah menjadi **Selesai** (Completed).
    *   Pastikan tombol edit dan hapus otomatis menghilang untuk transaksi yang sudah selesai.
4.  **Skenario Hapus Transaksi Draft:**
    *   Buat transaksi draft baru lainnya.
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"`.
    *   Klik konfirmasi "Hapus". Verifikasi transaksi terhapus dari tabel.
5.  **Skenario Filter Tanggal dan Pencarian:**
    *   Masukkan No. Transaksi yang dicari di kolom pencarian.
    *   Klik "Cari" dan pastikan hanya transaksi tersebut yang tampil.
