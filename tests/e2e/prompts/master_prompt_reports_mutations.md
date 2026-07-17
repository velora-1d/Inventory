# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - LAPORAN MUTASI BARANG (MUTATION REPORT)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Mutasi Barang** pada sistem Inventory. Pengujian wajib mencakup verifikasi parameter filter (Produk, Gudang, Rentang Tanggal), pembacaan ringkasan mutasi (Total Masuk, Total Keluar, Saldo Akhir, Perubahan Bersih), dan fungsionalitas ekspor dokumen (Excel, Word, PDF).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Ekspor:**
    *   Verifikasi judul halaman "Laporan Mutasi Stok" beserta deskripsinya.
    *   Verifikasi 3 tombol ekspor: *Excel*, *Word*, dan *PDF* di sudut kanan atas.
*   **Summary Cards Grid:**
    *   Tiga kartu ringkasan mutasi di bagian atas: *Total Masuk* (hijau), *Total Keluar* (merah), dan *Total Transaksi* (biru).
*   **Bilah Filter & Pencarian:**
    *   Filter dropdown: *Produk* (`product_id`) dan *Gudang* (`warehouse_id`).
    *   Filter tanggal: *Dari Tanggal* (`date_from`) dan *Sampai Tanggal* (`date_to`).
*   **Tabel Data Mutasi:**
    *   Verifikasi kolom: *Produk*, *SKU*, *Gudang*, *Total Masuk*, *Total Keluar*, *Perubahan Bersih*, *Saldo Akhir*, dan *Jumlah Transaksi*.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Filter Pencarian Berdasarkan Gudang:**
    *   Pilih salah satu gudang spesifik dari dropdown filter gudang.
    *   Pastikan tabel otomatis terfilter dan memuat data mutasi hanya untuk gudang tersebut.
    *   Verifikasi kolom *Gudang* di baris tabel cocok dengan kriteria filter.
2.  **Filter Berdasarkan Produk:**
    *   Pilih salah satu produk spesifik dari dropdown filter produk.
    *   Verifikasi bahwa tabel hanya menampilkan baris mutasi untuk produk tersebut.
3.  **Verifikasi Warna Net Change (Perubahan Bersih):**
    *   Pastikan angka perubahan bersih yang bernilai positif (>0) berwarna hijau (`text-emerald-600`), dan yang bernilai negatif (<0) berwarna merah (`text-rose-600`).
4.  **Pengujian Tombol Ekspor Data:**
    *   Klik tombol ekspor "Excel" dan "Word" secara bergantian, lalu pastikan sistem memicu pengunduhan berkas dokumen.
    *   Klik tombol ekspor "PDF", pastikan sistem print window terpicu.
