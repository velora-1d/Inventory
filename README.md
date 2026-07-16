# 📦 Velora Inventory Management System

Sistem Manajemen Inventaris & Logistik Gudang berbasis web yang modern, premium, dan responsif. Didesain menggunakan **Laravel 11**, **Vue.js 3**, **Inertia.js**, **Tailwind CSS**, **MySQL**, serta didukung kontainerisasi **Docker**.

Sistem ini didesain khusus dengan tema warna hangat (**Warm Sand/Beige**) yang premium, kontras tinggi, navigasi yang cerdas, serta mendukung sepenuhnya mode gelap (*Dark Mode*).

---

## ✨ Fitur Utama

### 📊 1. Dashboard & Analitik Real-Time
* **KPI Summary**: Ringkasan data total produk, stok kritis (stok rendah), dan pengguna aktif.
* **Tren Transaksi**: Grafik analitik interaktif yang menggambarkan pergerakan barang masuk dan keluar.
* **Alert Stok Minimum**: Panel khusus yang memantau produk dengan stok kritis menggunakan ikon penanda visual.
* **Log Aktivitas**: Riwayat audit transaksi terakhir langsung di halaman utama.

### 📦 2. Master Data Terintegrasi
* **Data Barang**: Pengelolaan barang lengkap dengan SKU otomatis, manajemen foto produk, harga beli/jual rata-rata, dan batas stok minimum.
* **Kategori & Satuan**: Pengelompokan barang dan simbol satuan operasional.
* **Supplier & Gudang / Lokasi**: Manajemen data mitra penyedia barang dan lokasi multi-gudang (GD-UTAMA, GD-CABANG, dsb).

### 🔄 3. Modul Transaksi Stok
* **Barang Masuk & Barang Keluar**: Pencatatan keluar-masuk barang dengan status *Draft* dan *Confirmed*.
* **Transfer Stok Antar Gudang**: Pemindahan stok dari satu gudang ke gudang lain secara presisi dengan dukungan pengeditan draf.
* **Stock Opname**: Penyesuaian stok fisik secara berkala demi akurasi data.
* **Retur Barang**: Pencatatan pengembalian barang rusak/cacat.

### 📋 4. Laporan Logistik & Nilai Aset
* **Kartu Stok**: Laporan mutasi mendetail per barang.
* **Mutasi Barang**: Ringkasan alur masuk dan keluar logistik.
* **Nilai Persediaan**: Estimasi valuasi aset barang yang tersimpan di gudang berdasarkan harga beli.
* **Laporan Stok Minimum**: Laporan komprehensif semua barang yang harus segera di-restock.

### ⚙️ 5. Manajemen Hak Akses & Pengaturan
* **Matrix Hak Akses**: Manajemen peran (Role & Permission) menggunakan format **Checkbox Matrix Table** yang rapi berdasarkan modul (Lihat, Tambah, Ubah, Hapus, Input).
* **Pengaturan Notifikasi**: Konfigurasi notifikasi stok rendah dan laporan harian yang disimpan secara dinamis ke kolom JSON user dalam database.

---

## 🛠️ Tech Stack

* **Back-End**: Laravel 11 (PHP 8.3)
* **Front-End**: Vue.js 3 (Composition API, TypeScript)
* **Glue**: Inertia.js (Zero-API routing)
* **CSS Framework**: Tailwind CSS (Curated Warm Sand / Beige theme)
* **Database**: MySQL 8.0
* **Containerization**: Docker & Docker Compose

---

## 🔑 Akun Demo (Default Seeds)

Gunakan akun berikut setelah menjalankan database seeder:

| Peran (Role) | Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@inventory.com` | `password` | Akses penuh ke semua modul & pengaturan |
| **Staff Gudang Utama** | `staff.utama@inventory.com` | `password` | Transaksi gudang utama, laporan, & draf |
| **Staff Gudang Cabang** | `staff.cabang@inventory.com` | `password` | Transaksi gudang cabang, laporan, & draf |

---

## 🚀 Panduan Instalasi Lokal

### Prasyarat
* PHP >= 8.3
* Composer
* Node.js >= 20 & NPM
* MySQL Server

### Langkah-langkah Setup
1. **Clone repositori**:
   ```bash
   git clone https://github.com/velora-1d/Inventory.git
   cd Inventory
   ```

2. **Instal dependensi PHP & Javascript**:
   ```bash
   composer install
   npm install
   ```

3. **Salin konfigurasi env**:
   ```bash
   cp .env.example .env
   ```
   *Atur konfigurasi koneksi database Anda (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) di dalam file `.env`.*

4. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

5. **Jalankan migrasi database & seed**:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Build asset frontend & jalankan server lokal**:
   - Untuk mode pengembangan (HMR):
     ```bash
     npm run dev
     ```
   - Untuk kompilasi produksi:
     ```bash
     npm run build
     ```
   - Jalankan server Laravel:
     ```bash
     php artisan serve
     ```

Aplikasi dapat diakses melalui browser di alamat **`http://127.0.0.1:8000`**.

---

## 🐳 Panduan Instalasi dengan Docker

Sistem ini telah dilengkapi dengan konfigurasi Docker multi-stage yang siap pakai untuk menyatukan aplikasi PHP-FPM, Nginx, dan database MySQL dalam satu orkestrasi.

### Menjalankan Container
1. **Build dan jalankan seluruh container**:
   ```bash
   docker-compose up -d --build
   ```

2. **Jalankan migrasi dan seeder di dalam container**:
   ```bash
   docker-compose exec app php artisan migrate:fresh --seed
   ```

Aplikasi Docker Anda sekarang aktif dan dapat diakses di port **`http://localhost:8000`**.
Koneksi database MySQL dipetakan keluar di port default `3306` (host: `localhost`).

### Menghentikan Container
Untuk mematikan container tanpa menghapus data volume MySQL:
```bash
docker-compose down
```
Untuk membersihkan seluruh container beserta volume datanya:
```bash
docker-compose down -v
```
