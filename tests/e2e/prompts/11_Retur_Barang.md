# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - TRANSAKSI RETUR BARANG (STOCK RETURN)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Retur Barang** pada sistem Inventory. Pengujian wajib mencakup verifikasi pembuatan draf retur (Retur Masuk / Retur Keluar), validasi stok tersedia di gudang tujuan/sumber, penyesuaian kuantitas & kondisi barang retur, modal rincian detail, dan approval flow (menyelesaikan transaksi dari draft ke completed).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Retur Barang" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Retur".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari No. Transaksi...").
    *   Filter dropdown: *Status* (Semua/Draft/Selesai), *Jenis Retur* (Semua/Retur Masuk/Retur Keluar), dan *Gudang*.
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
    *   Tombol "Cari".
*   **Form Input Modal (Dynamic Items Row):**
    *   Header form: *Tanggal Retur* (`#modal-date`), *Jenis Retur* (dropdown `#modal-return_type`), *Gudang* (dropdown `#modal-warehouse_id`), *Alasan Retur* (`#modal-reason`).
    *   Item rows: tombol "+ Tambah Item", dropdown *Produk*, input *Kondisi Barang* (dropdown Good/Broken), dropdown *Satuan*, input *Qty*, input *Harga*, dan subtotal otomatis.
    *   Teks Grand Total dinamis di bawah form list item.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Membuat Transaksi Retur Masuk (Simpan sebagai Draft):**
    *   Klik tombol "+ Tambah Retur". Pastikan modal muncul.
    *   Pilih Jenis Retur: **Retur Masuk** (Return In - dari customer).
    *   Pilih Gudang Penerima dan ketik alasan retur.
    *   Klik "+ Tambah Item". Pilih produk dari dropdown.
    *   Verifikasi harga default produk (biasanya *Average Price* untuk retur masuk) terisi otomatis.
    *   Masukkan Qty retur dan ubah kondisi barang menjadi *Broken* pada salah satu item (jika ada).
    *   Klik tombol "Simpan Draft" dan pastikan modal tertutup.
    *   Verifikasi transaksi baru tampil di tabel dengan status badge **Draft**.
2.  **Skenario Validasi Retur Keluar Melebihi Stok (Edge Case):**
    *   Buat transaksi retur baru dengan Jenis Retur: **Retur Keluar** (Return Out - ke supplier).
    *   Pilih Gudang Sumber.
    *   Tambahkan produk dan masukkan Qty melebihi "Stok Tersedia" di gudang tersebut.
    *   Pastikan muncul peringatan stok tidak mencukupi (Stock Warning) di bawah baris input.
3.  **Skenario Melengkapi Transaksi (Approve/Complete):**
    *   Pada baris transaksi draft retur yang valid, klik tombol aksi "Selesaikan" (ikon centang) dengan `title="Selesaikan Transaksi"`.
    *   Verifikasi dialog konfirmasi penyelesaian transaksi muncul.
    *   Klik "Selesaikan". Pastikan status transaksi berubah menjadi **Selesai** (Completed).
4.  **Skenario Hapus Transaksi Draft:**
    *   Buat transaksi draft retur baru lainnya.
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"`.
    *   Klik konfirmasi "Hapus". Verifikasi transaksi terhapus dari tabel.
