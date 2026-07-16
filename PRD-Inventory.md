# PRD — Aplikasi Inventory

**Tech Stack:** Laravel (Backend/API) + Vue.js (Frontend) + MySQL (Database)

---

## 1. Overview

**Nama Aplikasi:** Inventory
**Tujuan:** Sistem manajemen persediaan barang multi-gudang dengan kontrol akses berbasis role.

**Role Pengguna:**
- Admin
- Staff Gudang
- (Dinamis — bisa tambah role baru via Manajemen Role & Permission)

---

## 2. Role Matrix (Default)

| Menu | Admin | Staff Gudang |
|---|---|---|
| Dashboard | Full | View only |
| Data Barang | CRUD | View |
| Kategori Barang | CRUD | - |
| Satuan Barang | CRUD | - |
| Supplier | CRUD | - |
| Gudang/Lokasi | CRUD | - |
| Barang Masuk | CRUD | Input |
| Barang Keluar | CRUD | Input |
| Transfer Stok | CRUD | Input |
| Stock Opname | CRUD | Input |
| Retur Barang | CRUD | Input |
| Laporan | Full | View (gudang sendiri) |
| Manajemen User | Full | - |
| Manajemen Role & Permission | Full | - |
| Pengaturan Notifikasi | Full | - |

> Matrix ini bersifat default. Sistem menggunakan role & permission dinamis (lihat Section 4.13), sehingga admin dapat membuat role baru dan mengatur ulang matrix akses kapan saja.

---

## 3. Daftar Menu

1. Dashboard
2. Data Barang
3. Kategori Barang
4. Satuan Barang (Multi Unit)
5. Supplier
6. Gudang/Lokasi
7. Barang Masuk
8. Barang Keluar
9. Transfer Stok
10. Stock Opname
11. Retur Barang
12. Laporan Kartu Stok
13. Laporan Mutasi Barang
14. Laporan Nilai Persediaan
15. Laporan Stok Minimum
16. Manajemen User
17. Manajemen Role & Permission
18. Pengaturan Notifikasi Stok

---

## 4. Functional Requirements

### 4.1 Dashboard

**Widget:**
- Total Barang (jumlah SKU aktif)
- Total Nilai Persediaan (semua gudang)
- Grafik Barang Masuk vs Keluar (periode 30 hari terakhir)
- List Barang Stok Menipis (top 5–10)
- Transaksi Terbaru (5 transaksi terakhir, semua jenis)
- Filter Gudang (untuk user akses multi-gudang)

---

### 4.2 Data Barang

**Field:**
- Kode Barang/SKU (auto-generate atau manual)
- Nama Barang
- Kategori (relasi)
- Satuan Dasar/Base Unit (relasi, wajib)
- Satuan Beli (opsional)
- Satuan Jual (opsional)
- Harga Beli
- Harga Jual
- Stok Minimum
- Gudang Default (relasi)
- Deskripsi (opsional)
- Foto Barang (opsional)
- Status (Aktif/Nonaktif)

**Fungsi:**
- CRUD (delete = soft delete)
- Import/Export Excel (opsional)
- Pencarian & filter (kategori, gudang, status)

---

### 4.3 Kategori Barang

**Field:** Nama Kategori, Deskripsi (opsional), Status

**Fungsi:** CRUD, validasi nama tidak boleh duplikat

---

### 4.4 Satuan Barang (Multi Unit)

**Konsep:** Setiap barang punya 1 base unit untuk perhitungan stok, dan bisa punya satuan turunan dengan nilai konversi (contoh: 1 dus = 40 pcs).

**Field Master Satuan:** Nama Satuan, Simbol, Status

**Field Konversi Satuan per Barang:**
- Barang (relasi)
- Satuan Asal
- Satuan Tujuan
- Nilai Konversi

**Fungsi:**
- CRUD master satuan
- CRUD konversi satuan per barang (bisa multi-tingkat: pcs → dus → karton)
- Saat transaksi, user input pakai satuan apapun, sistem auto-convert ke base unit

---

### 4.5 Supplier

**Field:** Kode Supplier, Nama, Alamat, No. Telepon, Email (opsional), Contact Person (opsional), Status

**Fungsi:** CRUD, pencarian & filter status

---

### 4.6 Gudang/Lokasi

**Field:** Kode Gudang, Nama Gudang, Alamat, Penanggung Jawab (opsional, relasi User), Status

**Fungsi:** CRUD. Stok dihitung per gudang (1 barang bisa punya stok berbeda di tiap gudang).

---

### 4.7 Barang Masuk

**Field Header:**
- No. Transaksi (auto)
- Tanggal
- Supplier (relasi)
- Gudang Tujuan (relasi)
- No. PO/Referensi (opsional)
- Catatan (opsional)
- Status (Draft, Selesai)
- Dibuat oleh (relasi User)

**Field Detail Item:**
- Barang, Satuan, Qty, Harga Beli per satuan, Subtotal

**Fungsi:**
- CRUD (edit/hapus hanya saat Draft)
- Status "Selesai" → stok bertambah (convert ke base unit)
- Cetak/Export PDF bukti penerimaan
- Riwayat + filter (tanggal, supplier, gudang, status)

**Validasi:** Qty > 0, barang & satuan wajib, transaksi Selesai tidak bisa diedit/dihapus.

---

### 4.8 Barang Keluar

**Field Header:**
- No. Transaksi (auto)
- Tanggal
- Tujuan (customer/departemen)
- Gudang Asal (relasi)
- No. Referensi (opsional)
- Catatan (opsional)
- Status (Draft, Selesai)
- Dibuat oleh

**Field Detail Item:**
- Barang, Satuan, Qty, Harga Jual per satuan (opsional), Subtotal

**Fungsi:**
- CRUD (edit/hapus hanya saat Draft)
- Status "Selesai" → stok berkurang (convert ke base unit)
- Validasi stok cukup sebelum "Selesai"
- Cetak/Export PDF bukti pengeluaran
- Riwayat + filter (tanggal, gudang, status)

**Validasi:** Qty > 0 dan tidak melebihi stok tersedia.

---

### 4.9 Transfer Stok

**Field Header:**
- No. Transaksi (auto)
- Tanggal
- Gudang Asal
- Gudang Tujuan (harus beda dari asal)
- Catatan (opsional)
- Status (Draft, Selesai)
- Dibuat oleh

**Field Detail Item:** Barang, Satuan, Qty

**Fungsi:**
- CRUD (edit hanya saat Draft)
- Validasi stok cukup di gudang asal
- Status "Selesai" → stok pindah sekaligus (berkurang di asal, bertambah di tujuan)
- Cetak/Export PDF bukti transfer
- Riwayat + filter (tanggal, gudang asal/tujuan, status)

---

### 4.10 Stock Opname

**Field Header:**
- No. Transaksi (auto)
- Tanggal Opname
- Gudang (relasi)
- Catatan (opsional)
- Status (Draft, Selesai)
- Dibuat oleh

**Field Detail Item:**
- Barang, Stok Sistem (read-only), Stok Fisik (input manual), Selisih (auto-calculate), Catatan per item

**Fungsi:**
- Pilih gudang → load semua barang + stok sistem
- Status "Selesai" → stok sistem disesuaikan (adjustment)
- Cetak/Export PDF hasil opname
- Riwayat + filter (tanggal, gudang, status)

**Validasi:** Transaksi Selesai tidak bisa diedit (audit trail). Hanya 1 opname Draft aktif per gudang.

---

### 4.11 Retur Barang

**Jenis:**
- Retur Masuk (dari Barang Keluar → stok bertambah)
- Retur Keluar (dari Barang Masuk → stok berkurang)

**Field Header:**
- No. Transaksi (auto)
- Tanggal
- Jenis Retur
- Referensi Transaksi Asal (relasi)
- Gudang
- Alasan Retur
- Status (Draft, Selesai)
- Dibuat oleh

**Field Detail Item:** Barang (auto-fetch dari transaksi asal), Satuan, Qty Retur, Catatan

**Fungsi:**
- Pilih transaksi asal → load item yang bisa diretur
- Status "Selesai" → stok disesuaikan sesuai jenis retur
- Cetak/Export PDF bukti retur
- Riwayat + filter (tanggal, jenis, gudang, status)

**Validasi:** Qty retur ≤ qty transaksi asal (dikurangi qty yang sudah pernah diretur).

---

### 4.12 Laporan

**a. Kartu Stok**
Kolom: Tanggal, No. Transaksi, Jenis Transaksi, Qty Masuk, Qty Keluar, Saldo Stok (running balance)
Filter: Barang, Gudang, Rentang Tanggal

**b. Laporan Mutasi Barang**
Kolom: Barang, Stok Awal, Total Masuk, Total Keluar, Total Transfer, Total Retur, Stok Akhir
Filter: Gudang, Kategori, Rentang Tanggal

**c. Laporan Nilai Persediaan**
Kolom: Barang, Qty Stok, Harga Rata-rata (Average/Weighted Average), Total Nilai
Filter: Gudang, Kategori, Per Tanggal (snapshot)
Metode Valuasi: **Average (rata-rata tertimbang)** — dihitung ulang setiap ada Barang Masuk dengan harga baru.

**d. Laporan Stok Minimum**
Kolom: Barang, Gudang, Stok Saat Ini, Stok Minimum, Status (Menipis/Aman)
Filter: Gudang, Kategori
Menjadi sumber data untuk Pengaturan Notifikasi Stok.

Semua laporan mendukung export Excel/PDF.

---

### 4.13 Manajemen User

**Field:** Nama Lengkap, Username/Email, Password, Role (relasi), Gudang (opsional, pembatasan akses), Status

**Fungsi:** CRUD (nonaktifkan = soft delete), reset password, filter (role, status)

---

### 4.14 Manajemen Role & Permission

**Field Role:** Nama Role, Deskripsi

**Field Permission Matrix:** Menu × Aksi (View/Create/Update/Delete) × Role, dalam bentuk checkbox

**Fungsi:**
- CRUD Role
- Atur matrix permission per role
- Perubahan permission langsung berlaku ke semua user dengan role tsb
- Implementasi: dinamis (data-driven), bukan hardcode — disarankan pakai package **Spatie Laravel-Permission**

---

### 4.15 Pengaturan Notifikasi Stok

**Field:** Jenis Notifikasi (Stok Menipis/Habis), Channel (Email/In-app), Penerima (relasi User/Role), Status Aktif

**Fungsi:**
- Auto-cek stok vs stok minimum setiap ada transaksi Barang Keluar/Transfer
- Kirim notifikasi jika stok ≤ stok minimum
- Log riwayat notifikasi terkirim

---

## 5. Database Schema (MySQL)

### 5.1 Master Data

```sql
-- USERS
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    warehouse_id BIGINT UNSIGNED NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- ROLES (Spatie Laravel-Permission)
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    guard_name VARCHAR(50) DEFAULT 'web',
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- PERMISSIONS
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,   -- contoh: barang.view, barang.create
    guard_name VARCHAR(50) DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- MODEL_HAS_ROLES (pivot user-role)
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(150) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- ROLE_HAS_PERMISSIONS (pivot role-permission)
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- CATEGORIES
CREATE TABLE categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- UNITS
CREATE TABLE units (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,          -- pcs, dus, karton
    symbol VARCHAR(20) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- SUPPLIERS
CREATE TABLE suppliers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    address TEXT NULL,
    phone VARCHAR(30) NULL,
    email VARCHAR(150) NULL,
    contact_person VARCHAR(100) NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

-- WAREHOUSES
CREATE TABLE warehouses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    address TEXT NULL,
    pic_user_id BIGINT UNSIGNED NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (pic_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- PRODUCTS
CREATE TABLE products (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    base_unit_id BIGINT UNSIGNED NOT NULL,
    purchase_unit_id BIGINT UNSIGNED NULL,
    sale_unit_id BIGINT UNSIGNED NULL,
    purchase_price DECIMAL(15,2) DEFAULT 0,
    sale_price DECIMAL(15,2) DEFAULT 0,
    avg_price DECIMAL(15,2) DEFAULT 0,     -- untuk valuasi average
    min_stock DECIMAL(15,2) DEFAULT 0,
    default_warehouse_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    photo VARCHAR(255) NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (base_unit_id) REFERENCES units(id),
    FOREIGN KEY (purchase_unit_id) REFERENCES units(id),
    FOREIGN KEY (sale_unit_id) REFERENCES units(id),
    FOREIGN KEY (default_warehouse_id) REFERENCES warehouses(id)
);

-- PRODUCT UNIT CONVERSIONS (multi unit)
CREATE TABLE product_unit_conversions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    from_unit_id BIGINT UNSIGNED NOT NULL,
    to_unit_id BIGINT UNSIGNED NOT NULL,
    conversion_value DECIMAL(15,4) NOT NULL,  -- 1 from_unit = conversion_value * to_unit
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (from_unit_id) REFERENCES units(id),
    FOREIGN KEY (to_unit_id) REFERENCES units(id)
);

-- STOCKS (stok per barang per gudang)
CREATE TABLE stocks (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,2) DEFAULT 0,   -- dalam base unit
    updated_at TIMESTAMP NULL,
    UNIQUE KEY uniq_product_warehouse (product_id, warehouse_id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);
```

### 5.2 Transaksi

```sql
-- STOCK IN (Barang Masuk)
CREATE TABLE stock_ins (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transaction_no VARCHAR(50) NOT NULL UNIQUE,
    transaction_date DATE NOT NULL,
    supplier_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    reference_no VARCHAR(50) NULL,
    notes TEXT NULL,
    status ENUM('draft','completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_in_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stock_in_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,2) NOT NULL,
    qty_base_unit DECIMAL(15,2) NOT NULL,   -- hasil convert
    price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (stock_in_id) REFERENCES stock_ins(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- STOCK OUT (Barang Keluar)
CREATE TABLE stock_outs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transaction_no VARCHAR(50) NOT NULL UNIQUE,
    transaction_date DATE NOT NULL,
    destination VARCHAR(150) NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    reference_no VARCHAR(50) NULL,
    notes TEXT NULL,
    status ENUM('draft','completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_out_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stock_out_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,2) NOT NULL,
    qty_base_unit DECIMAL(15,2) NOT NULL,
    price DECIMAL(15,2) NULL,
    subtotal DECIMAL(15,2) NULL,
    FOREIGN KEY (stock_out_id) REFERENCES stock_outs(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- STOCK TRANSFERS
CREATE TABLE stock_transfers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transaction_no VARCHAR(50) NOT NULL UNIQUE,
    transaction_date DATE NOT NULL,
    from_warehouse_id BIGINT UNSIGNED NOT NULL,
    to_warehouse_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    status ENUM('draft','completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (from_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (to_warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_transfer_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stock_transfer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,2) NOT NULL,
    qty_base_unit DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (stock_transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- STOCK OPNAME
CREATE TABLE stock_opnames (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transaction_no VARCHAR(50) NOT NULL UNIQUE,
    opname_date DATE NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    status ENUM('draft','completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_opname_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stock_opname_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    system_qty DECIMAL(15,2) NOT NULL,
    physical_qty DECIMAL(15,2) NOT NULL,
    difference DECIMAL(15,2) NOT NULL,
    notes VARCHAR(255) NULL,
    FOREIGN KEY (stock_opname_id) REFERENCES stock_opnames(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- RETURNS
CREATE TABLE stock_returns (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    transaction_no VARCHAR(50) NOT NULL UNIQUE,
    return_date DATE NOT NULL,
    return_type ENUM('return_in','return_out') NOT NULL,
    reference_type ENUM('stock_in','stock_out') NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    reason TEXT NULL,
    status ENUM('draft','completed') DEFAULT 'draft',
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE stock_return_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    stock_return_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,2) NOT NULL,
    qty_base_unit DECIMAL(15,2) NOT NULL,
    notes VARCHAR(255) NULL,
    FOREIGN KEY (stock_return_id) REFERENCES stock_returns(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- STOCK LEDGER (kartu stok, sumber laporan mutasi)
CREATE TABLE stock_ledgers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    transaction_type ENUM('stock_in','stock_out','transfer_in','transfer_out','opname','return_in','return_out') NOT NULL,
    reference_id BIGINT UNSIGNED NOT NULL,
    qty_in DECIMAL(15,2) DEFAULT 0,
    qty_out DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) NOT NULL,     -- running balance
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);
```

### 5.3 Notifikasi

```sql
-- NOTIFICATION SETTINGS
CREATE TABLE notification_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    notification_type ENUM('low_stock','out_of_stock') NOT NULL,
    channel ENUM('email','in_app') NOT NULL,
    recipient_type ENUM('user','role') NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- NOTIFICATION LOGS
CREATE TABLE notification_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    notification_type ENUM('low_stock','out_of_stock') NOT NULL,
    channel ENUM('email','in_app') NOT NULL,
    recipient_id BIGINT UNSIGNED NOT NULL,
    sent_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id)
);
```

### 5.4 Audit Trail

```sql
-- ACTIVITY LOGS (audit trail semua aksi penting)
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    module VARCHAR(50) NOT NULL,        -- contoh: stock_in, product, user
    action VARCHAR(50) NOT NULL,        -- create, update, delete, approve
    description TEXT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 6. Security

### 6.1 Authentication
- Login berbasis session/token menggunakan **Laravel Sanctum** (SPA authentication untuk Vue.js).
- Password di-hash dengan **bcrypt** (default Laravel).
- Rate limiting pada endpoint login (throttle, misal 5 percobaan/menit) untuk mencegah brute force.
- Auto-logout setelah periode idle tertentu (session timeout, dikonfigurasi).
- Opsional: Two-Factor Authentication (2FA) untuk role Admin.

### 6.2 Authorization
- Role & Permission berbasis **Spatie Laravel-Permission**.
- Setiap endpoint API dilindungi middleware permission check (`can:barang.view`, dll).
- Validasi akses gudang: user dengan `warehouse_id` terbatas hanya bisa akses data gudang miliknya (row-level restriction), dicek di setiap query/controller.
- Frontend (Vue) menyembunyikan menu/tombol sesuai permission user, namun validasi utama tetap di backend (jangan andalkan hide UI saja).

### 6.3 Data Protection
- Semua komunikasi wajib HTTPS (SSL/TLS).
- Validasi input di setiap request (Laravel Form Request) — mencegah SQL Injection & mass assignment.
- Sanitasi output untuk mencegah XSS (Vue otomatis escape, tapi hati-hati saat pakai `v-html`).
- CSRF protection aktif untuk semua request state-changing (built-in Laravel + Sanctum).
- File upload (foto barang) divalidasi: tipe file, ukuran maksimum, disimpan di luar public root atau dengan nama ter-randomisasi.

### 6.4 Audit & Logging
- Semua transaksi krusial (create/update/delete/status change) tercatat di `activity_logs` — siapa, kapan, perubahan apa (old value → new value).
- Log akses gagal (failed login attempts) untuk deteksi anomali.
- Transaksi dengan status "Selesai" bersifat immutable (tidak bisa diedit/dihapus langsung) — perubahan harus lewat Retur/Opname, demi menjaga integritas data.

### 6.5 Database Security
- Gunakan **prepared statements** (default Eloquent ORM) — mencegah SQL Injection.
- Backup database terjadwal (harian), dengan retensi sesuai kebijakan.
- Kredensial database disimpan di `.env`, tidak pernah di-commit ke repository.
- Least privilege: user database aplikasi tidak diberi hak `DROP`/`GRANT` di production.

### 6.6 API Security
- Semua endpoint API menggunakan autentikasi token (Sanctum), tidak ada endpoint terbuka untuk data sensitif.
- Rate limiting per endpoint (Laravel throttle middleware) untuk mencegah abuse.
- Versioning API (`/api/v1/...`) untuk menjaga kompatibilitas saat ada perubahan.
- CORS dikonfigurasi ketat — hanya domain frontend resmi yang diizinkan.

---

*Dokumen ini adalah PRD awal, dapat disesuaikan lebih lanjut sesuai kebutuhan client.*
