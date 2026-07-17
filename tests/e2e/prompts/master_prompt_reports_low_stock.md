# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - LAPORAN STOK MINIMUM (STOK RENDAH)

Jalankan pengujian E2E (End-to-End) using TestSprite secara mendalam dan menyeluruh khusus untuk menu **Stok Minimum** (Stok Rendah) pada sistem Inventory. Pengujian wajib mencakup verifikasi parameter filter (Pencarian Produk, Gudang, Kategori, Tingkat Keparahan/Severity), pembacaan ringkasan status stok, indikator tingkat pemenuhan stok (progress bar/fill percentage), dan fungsionalitas ekspor dokumen (Excel, Word, PDF).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Ekspor:**
    *   Verifikasi judul halaman "Laporan Stok Rendah" beserta deskripsinya.
    *   Verifikasi 3 tombol ekspor: *Excel*, *Word*, dan *PDF* di sudut kanan atas.
*   **Summary Cards Grid:**
    *   Tiga kartu ringkasan status stok:
        1.  *Kritis (Habis)* - Dengan garis indikator merah di atas.
        2.  *Hampir Habis (Di Bawah Minimum)* - Dengan garis indikator kuning/amber.
        3.  *Total Item* - Dengan garis indikator biru.
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian produk/SKU.
    *   Dropdown filter: *Gudang* (`warehouse_id`), *Kategori* (`category_id`), dan *Tingkat Keparahan* (`severity` - Semua/Kritis/Peringatan).
*   **Tabel Data Stok Rendah:**
    *   Kolom: *Produk*, *SKU*, *Gudang*, *Kategori*, *Stok Saat Ini*, *Stok Minimum*, dan *Status*.
    *   Gaya visual baris: warna kemerahan untuk stok `0` (kritis) dan warna kekuningan untuk stok menipis (peringatan).
    *   Progress bar tingkat pemenuhan stok (visualisasi kuantitas saat ini terhadap batas minimum).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Filter Berdasarkan Tingkat Keparahan (Severity):**
    *   Pilih opsi "Kritis (Habis)" dari dropdown filter tingkat keparahan.
    *   Pastikan tabel terupdate dan hanya menampilkan produk yang kuantitasnya sama dengan `0`.
    *   Verifikasi status badge pada kolom Status adalah "Habis (Kritis)" dengan warna merah.
2.  **Filter Berdasarkan Kategori dan Gudang:**
    *   Pilih opsi Kategori dan Gudang tertentu dari dropdown filter.
    *   Verifikasi baris tabel menyaring data dengan tepat.
3.  **Pengujian Tombol Ekspor Data:**
    *   Klik tombol ekspor "Excel" dan "Word" secara bergantian, lalu pastikan sistem memicu pengunduhan berkas dokumen.
    *   Klik tombol ekspor "PDF", pastikan sistem print window terpicu.
