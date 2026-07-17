# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - TRANSAKSI TRANSFER STOK (STOCK TRANSFER)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Transfer Stok** pada sistem Inventory. Pengujian wajib mencakup verifikasi CRUD transaksi, validasi gudang asal dan tujuan tidak boleh sama, pengecekan stok tersedia (*Available Stock*) di gudang asal, validasi input kuantitas melebihi stok, modal rincian detail transfer, dan approval flow (menyelesaikan transaksi dari draft ke completed).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Transfer Stok" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Transfer".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari No. Transaksi/Referensi...").
    *   Filter dropdown: *Status* (Semua/Draft/Selesai), *Gudang Asal*, dan *Gudang Tujuan*.
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
    *   Tombol "Cari".
*   **Form Input Modal (Dynamic Items Row):**
    *   Header form: *Tanggal* (`#modal-date`), *Gudang Asal* (`#modal-from_warehouse_id`), *Gudang Tujuan* (`#modal-to_warehouse_id`), *No. Referensi* (`#modal-reference_no`), *Catatan* (`#modal-notes`).
    *   Item rows: tombol "+ Tambah Item", dropdown *Produk*, label *Stok Tersedia* (menampilkan angka stok di gudang asal terpilih), dropdown *Satuan*, input *Qty*.
    *   Pesan peringatan error (Same Warehouse Error & Stock Warning) yang muncul di bawah input.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Validasi Gudang Sama (Validation Edge Case):**
    *   Klik tombol "+ Tambah Transfer". Pastikan modal muncul.
    *   Pilih Gudang Asal yang sama dengan Gudang Tujuan.
    *   Pastikan muncul pesan peringatan/error: "Gudang asal dan tujuan tidak boleh sama."
2.  **Skenario Membuat Transaksi Baru (Simpan sebagai Draft):**
    *   Klik tombol "+ Tambah Transfer".
    *   Pilih Gudang Asal (yang memiliki stok barang) dan Gudang Tujuan yang berbeda.
    *   Klik "+ Tambah Item". Pilih produk dari dropdown.
    *   Verifikasi angka "Stok Tersedia" terisi otomatis.
    *   Masukkan Qty pengiriman (harus lebih kecil atau sama dengan Stok Tersedia).
    *   Klik tombol "Simpan Draft" dan pastikan modal tertutup.
    *   Verifikasi transaksi baru tampil di tabel dengan status badge **Draft**.
3.  **Skenario Validasi Melebihi Ketersediaan Stok (Stock Warning):**
    *   Ubah Qty item melebihi jumlah "Stok Tersedia".
    *   Pastikan muncul teks peringatan stok tidak mencukupi dan tombol simpan dinonaktifkan/menampilkan feedback error.
4.  **Skenario Melengkapi Transaksi (Approve/Complete):**
    *   Pada baris transaksi draft yang valid, klik tombol aksi "Selesaikan" (ikon centang) dengan `title="Selesaikan Transaksi"`.
    *   Verifikasi dialog konfirmasi penyelesaian transaksi muncul.
    *   Klik "Selesaikan". Pastikan status transaksi berubah menjadi **Selesai** (Completed).
5.  **Skenario Hapus Transaksi Draft:**
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"` pada baris transaksi draft yang ingin dibatalkan.
    *   Klik konfirmasi "Hapus". Verifikasi transaksi terhapus dari tabel.
