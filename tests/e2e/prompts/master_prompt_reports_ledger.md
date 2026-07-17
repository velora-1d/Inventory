# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - LAPORAN KARTU STOK (BUKU BESAR STOK)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Kartu Stok** pada sistem Inventory. Pengujian wajib mencakup verifikasi parameter filter (Produk, Gudang, Tipe Mutasi, Tanggal), pemuatan data historis transaksi secara kronologis, dan fungsionalitas ekspor dokumen (Excel, Word, PDF).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Ekspor:**
    *   Verifikasi judul halaman "Buku Besar Stok" beserta deskripsinya.
    *   Verifikasi kehadiran 3 tombol ekspor: *Excel* (hijau), *Word* (biru), dan *PDF* (merah) di sudut kanan atas.
*   **Bilah Filter Pencarian:**
    *   Filter dropdown: *Produk* (`product_id`), *Gudang* (`warehouse_id`), *Tipe Transaksi* (`type` - Barang Masuk/Barang Keluar/Transfer/Penyesuaian/Retur).
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
    *   Tombol "Reset Filter".

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Filter Pencarian Berdasarkan Produk:**
    *   Pilih salah satu produk spesifik dari dropdown filter produk.
    *   Pastikan tabel otomatis terfilter dan memuat riwayat mutasi hanya untuk produk tersebut.
    *   Verifikasi kolom *SKU - Nama Produk* cocok dengan kriteria filter.
2.  **Filter Berdasarkan Tipe Transaksi:**
    *   Ubah filter tipe transaksi ke "Barang Masuk".
    *   Verifikasi bahwa kolom tipe transaksi di tabel hanya menampilkan badge "Barang Masuk".
    *   Pastikan kolom *Qty Masuk* terisi angka dan *Qty Keluar* bernilai `0` atau `-`.
3.  **Fungsionalitas Reset Filter:**
    *   Klik tombol "Reset Filter".
    *   Pastikan parameter filter kembali kosong dan tabel memuat ulang semua riwayat mutasi dari seluruh produk dan gudang.
4.  **Pengujian Tombol Ekspor Data:**
    *   Klik tombol ekspor "Excel" dan "Word" secara bergantian, lalu pastikan sistem memicu pengunduhan berkas dokumen terkait.
    *   Klik tombol ekspor "PDF", pastikan modul print browser/sistem terpicu (atau layout CSS media print aktif memangkas elemen non-print).
