# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - LAPORAN VALUASI STOK (NILAI PERSEDIAAN)

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk menu **Nilai Persediaan** (Valuasi Stok) pada sistem Inventory. Pengujian wajib mencakup verifikasi parameter filter (Pencarian Produk, Gudang, Kategori), pembacaan total valuasi persediaan, indikator progress bar kontribusi persediaan, dan fungsionalitas ekspor dokumen (Excel, Word, PDF).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
*   **Header & Aksi Ekspor:**
    *   Verifikasi judul halaman "Valuasi Stok" beserta deskripsinya.
    *   Verifikasi 3 tombol ekspor: *Excel*, *Word*, dan *PDF* di sudut kanan atas.
*   **Total Value Hero Card:**
    *   Kartu besar gradien oranye-amber di bagian atas: menampilkan *Total Nilai Persediaan* (format rupiah `Rp XX.XXX.XXX`) dan *Total Item Produk-Gudang*.
*   **Bilah Filter & Pencarian:**
    *   Kolom pencarian produk/SKU.
    *   Dropdown filter: *Gudang* (`warehouse_id`) dan *Kategori* (`category_id`).
*   **Tabel Data Valuasi:**
    *   Kolom: *Produk*, *SKU*, *Gudang*, *Kategori*, *Stok*, *Harga Rata-rata*, dan *Nilai Total*.
    *   Progress bar kecil di bawah angka Nilai Total (menunjukkan persentase kontribusi item terhadap total persediaan).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Filter Pencarian Berdasarkan Kategori:**
    *   Pilih salah satu kategori produk dari dropdown filter kategori.
    *   Verifikasi bahwa tabel hanya menampilkan baris produk yang sesuai dengan kategori yang dipilih.
2.  **Filter Berdasarkan Gudang:**
    *   Pilih salah satu gudang spesifik dari dropdown filter gudang.
    *   Pastikan total nilai persediaan di Hero Card (jika di-recalculate di sisi server) atau data di baris tabel menyesuaikan hanya menampilkan data untuk gudang tersebut.
3.  **Pengujian Pencarian Input:**
    *   Ketik SKU produk di kolom pencarian lalu tekan Enter.
    *   Verifikasi baris produk yang dicari muncul dengan tepat.
4.  **Pengujian Tombol Ekspor Data:**
    *   Klik tombol ekspor "Excel" dan "Word" secara bergantian, lalu pastikan sistem memicu pengunduhan berkas dokumen.
    *   Klik tombol ekspor "PDF", pastikan sistem print window terpicu.
