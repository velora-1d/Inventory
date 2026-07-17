# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN DASHBOARD INVENTORY

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk **Halaman Dashboard** pada sistem Inventory. Pengujian wajib mencakup verifikasi data statistik (KPI cards), grafik aktivitas barang, filter gudang, dan daftar barang stok menipis.

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
Verifikasi bahwa seluruh antarmuka dashboard dimuat secara presisi dengan detail berikut:
*   **Header:**
    *   Teks judul utama "Dashboard" dan penjelasan deskripsinya.
    *   Ikon tema di navbar atas/sidebar.
*   **KPI Cards Overview (8 Cards):**
    *   Verifikasi 8 kartu statistik utama tampil lengkap dengan labelnya:
        1.  *Total SKU Aktif*
        2.  *Nilai Persediaan* (Dengan format mata uang IDR: `Rp XX.XXX.XXX`)
        3.  *Total Qty Barang*
        4.  *Stok Menipis* (Berwarna merah jika ada item di bawah limit)
        5.  *Total Gudang*
        6.  *Mitra Supplier*
        7.  *Aktivitas Mutasi*
        8.  *Transaksi Draft*
*   **Chart Grafik Aktivitas:**
    *   Grafik SVG aktivitas barang masuk/keluar (line chart area).
    *   Legend/keterangan warna grafik: *Barang Masuk* (hijau) dan *Barang Keluar* (merah).
*   **Daftar Stok Menipis (Bento Grid):**
    *   Panel list barang yang stoknya di bawah batas minimum (menampilkan SKU, nama, gudang, stok saat ini/minimum).

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Penggunaan Filter Gudang (Bagi Super Admin):**
    *   Pilih salah satu gudang spesifik pada dropdown "Filter Gudang".
    *   Pastikan sistem mengirimkan request filter dan memuat ulang halaman dengan parameter `warehouse_id`.
    *   Verifikasi angka pada KPI cards berubah sesuai dengan nilai spesifik milik gudang yang dipilih.
    *   Kembalikan pilihan filter ke "Semua Gudang" dan pastikan angka statistik kembali ke nilai agregat semula.
2.  **Pembatasan Akses Filter (Bagi Staff Gudang / User Terkunci):**
    *   Jika login menggunakan akun staff yang ditugaskan ke gudang tertentu, pastikan dropdown "Filter Gudang" **tidak muncul**.
    *   Pastikan teks peringatan "Akses Terbatas: [Nama Gudang]" muncul menggantikan dropdown filter.
3.  **Verifikasi Navigasi Shortcut Sidebar:**
    *   Klik menu "Dashboard" di sidebar, pastikan halaman direfresh atau tetap berada di rute `/dashboard`.
    *   Klik toggle collapse sidebar (tombol `<<` / `>>`) dan pastikan lebar sidebar mengecil/membesar dengan animasi transisi yang mulus.

---

## 3. EDGE CASE TESTING
*   **Tanpa Data Statistik (Zero State):**
    *   Simulasikan jika nilai data statistik `stats` bernilai `0`. Pastikan antarmuka tidak rusak dan menampilkan angka `0`.
    *   Grafik SVG area chart harus menangani koordinat `0` dengan baik tanpa memunculkan error konsol `NaN` atau memecahkan layout visual.
