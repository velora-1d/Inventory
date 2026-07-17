# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - TRANSAKSI STOCK OPNAME (PENYESUAIAN STOK)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Stock Opname** pada sistem Inventory. Pengujian wajib mencakup verifikasi pembuatan draf opname, pemuatan otomatis produk ber-stok di gudang terpilih, penyesuaian jumlah fisik (*Physical Qty*), kalkulasi otomatis selisih (*Difference*), filtering tanggal/status, modal rincian detail, dan approval flow (menyelesaikan transaksi dari draft ke completed).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Utama:**
    *   Verifikasi judul halaman "Stock Opname" beserta deskripsinya.
    *   Tampilkan tombol "+ Tambah Stock Opname".
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian teks ("Cari No. Transaksi...").
    *   Filter dropdown: *Status* (Semua/Draft/Selesai) dan *Gudang*.
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
    *   Tombol "Cari".
*   **Form Input Modal (Warehouse Auto-population):**
    *   Header form: *Tanggal Opname* (`#modal-date`), *Gudang* (`#modal-warehouse_id`), *Catatan* (`#modal-notes`).
    *   Tabel List Items (Pemuatan Otomatis): Kolom *Nama Produk/SKU*, *Stok Sistem (Sistem Qty)*, *Stok Fisik (Fisik Qty)* (input teks), *Selisih (Difference)* (angka positif/negatif otomatis), dan *Keterangan* (input teks).
    *   Indikator total selisih/discrepancy (misal: "X item berbeda").

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Skenario Membuat Transaksi Opname (Simpan sebagai Draft):**
    *   Klik tombol "+ Tambah Stock Opname". Pastikan modal muncul.
    *   Isi Tanggal Opname, pilih Gudang (pilih yang sudah ada stok barang).
    *   Pastikan tabel list item secara otomatis memuat semua produk yang memiliki stok di gudang tersebut beserta jumlah *Sistem Qty*-nya.
    *   Pada salah satu item, ubah nilai *Fisik Qty* agar berbeda dengan *Sistem Qty* (misal: kurangi 1).
    *   Verifikasi kolom *Difference* otomatis terhitung negatif (`-1`).
    *   Ketik keterangan di kolom catatan item.
    *   Klik tombol "Simpan Draft" dan pastikan modal tertutup.
    *   Verifikasi transaksi baru tampil di tabel dengan status badge **Draft**.
2.  **Skenario Melengkapi Transaksi (Approve/Complete):**
    *   Pada baris transaksi draft opname tersebut, klik tombol aksi "Selesaikan" (ikon centang) dengan `title="Selesaikan Transaksi"`.
    *   Verifikasi dialog konfirmasi penyelesaian transaksi muncul.
    *   Klik "Selesaikan". Pastikan status transaksi berubah menjadi **Selesai** (Completed).
3.  **Skenario Hapus Transaksi Draft:**
    *   Buat transaksi draft opname baru lainnya.
    *   Klik tombol aksi "Hapus" (ikon sampah) dengan `title="Hapus"`.
    *   Klik konfirmasi "Hapus". Verifikasi transaksi terhapus dari tabel.
4.  **Skenario Melihat Detail Opname:**
    *   Klik tombol aksi "Detail" (ikon mata) dengan `title="Detail"`.
    *   Verifikasi modal detail muncul dan menampilkan selisih kuantitas dengan tepat.
