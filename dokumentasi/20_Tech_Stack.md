# DOKUMENTASI SISTEM: TECH STACK (TEKNOLOGI PENDUKUNG)

Dokumentasi ini menjelaskan secara mendalam spesifikasi teknologi, library, dan infrastruktur pendukung yang digunakan dalam membangun dan menjalankan Aplikasi Inventory.

---

## 1. BACKEND ARCHITECTURE (SERVER-SIDE)
Aplikasi ini dibangun menggunakan arsitektur monolitik modern berbasis PHP dengan framework Laravel.

*   **Runtime Environment:**
    *   **PHP ^8.3:** Memanfaatkan fitur *strict typing*, *readonly properties*, *constructor property promotion*, dan performa eksekusi JIT compiler yang optimal.
*   **Core Framework:**
    *   **Laravel ^13.8 (Latest Stable):** Framework utama yang menangani routing, database ORM, queue handling, HTTP middleware, session, keamanan autentikasi, serta dependency injection container.
*   **Database ORM & Query Engine:**
    *   **Eloquent ORM:** Mapping database relational ke object model. Mendukung fungsionalitas eager loading untuk meminimalkan query N+1.
    *   **ACID Compliance:** Memanfaatkan fitur `DB::transaction()` untuk menjamin *Atomicity* dan `.lockForUpdate()` (Row-Level Locking) pada tabel `stocks` untuk menjamin tingkat *Isolation* yang tinggi dari *race conditions*.
*   **Otorisasi & Keamanan Pengguna:**
    *   **Spatie Laravel Permission ^8.3:** Menangani implementasi *Role-Based Access Control* (RBAC) secara dinamis melalui pemetaan *Role & Permission Matrix*.
*   **Media & Cloud Storage Integration:**
    *   **Intervention Image ^4.2 (dengan GD Driver):** Digunakan untuk mengotomatiskan kompresi dan konversi format upload foto produk menjadi WebP (maksimum lebar 800px, kualitas 80%).
    *   **League Flysystem AWS S3 ^3.35:** Adapter driver untuk mengintegrasikan penyimpanan awan (S3 API compatible) seperti **RustFS**, Cloudflare R2, atau MinIO secara transparan tanpa mengubah baris kode.
*   **Helper & Routing Frontend:**
    *   **Ziggy ^2.0:** Mentransfer route Laravel secara langsung ke file Javascript/Vue, sehingga rute backend dapat dipanggil menggunakan helper `route('nama.rute')` di frontend.

---

## 2. FRONTEND ARCHITECTURE (CLIENT-SIDE)
Frontend aplikasi mengadopsi konsep Single Page Application (SPA) yang cepat dengan integrasi Inertia.js untuk menjembatani komunikasi data tanpa memerlukan REST API terpisah.

*   **Reactive UI Library:**
    *   **Vue.js ^3.4:** UI framework utama menggunakan sintaks modern `<script setup lang="ts">` dan Composition API untuk modularitas kode yang tinggi.
*   **Data Bridge (Backend-Frontend Link):**
    *   **Inertia.js ^2.0:** Menghilangkan kebutuhan untuk membangun REST API tradisional. Data dikirim langsung sebagai *Inertia Page Props* dari Laravel controller ke Vue view.
*   **Asset Bundler & Compiler:**
    *   **Vite ^8.0 & Laravel Vite Plugin ^3.1:** Alat kompilasi super cepat untuk Hot Module Replacement (HMR) selama pengembangan, dan kompilasi build produksi yang teroptimasi.
*   **Typing System:**
    *   **TypeScript ^5.6:** Menjamin keamanan tipe data (type-safety) di sisi client untuk menghindari kesalahan runtime.
*   **Styling & CSS Engine:**
    *   **Tailwind CSS v4 (melalui `@tailwindcss/vite ^4.3.2`):** Utility-first CSS framework untuk pembuatan antarmuka modern, responsif, dan konsisten.
*   **HTTP Client:**
    *   **Axios ^1.18.1:** Library untuk melakukan request asynchronous (AJAX) di luar alur default routing Inertia (misalnya, auto-fetch detail item di modal popup).

---

## 3. TESTING FRAMEWORK (QUALITY ASSURANCE)
*   **E2E (End-to-End) Testing:**
    *   **Playwright ^1.61.1:** Digunakan untuk menjalankan simulasi alur kerja pengguna (seperti proses Login, pembuatan transaksi, dan verifikasi UI) secara otomatis pada browser Chromium, Firefox, dan WebKit secara lokal.
*   **Unit & Integration Testing:**
    *   **PHPUnit ^12.5.12:** Framework testing backend bawaan Laravel untuk menguji logika bisnis unit controller dan model.

---

## 4. DRAFT STRUKTUR INTEGRASI SISTEM

```mermaid
graph LR
    subgraph Browser (Client-Side)
        Vue[Vue 3 Engine]
        Tailwind[Tailwind CSS v4]
        InertiaClient[Inertia.js Client]
    end
    
    subgraph Server (Backend-Side)
        InertiaLaravel[Inertia.js Laravel Bridge]
        Laravel[Laravel 13 Core]
        Spatie[Spatie RBAC Engine]
    end
    
    subgraph Database Layer
        DB[(PostgreSQL / MySQL)]
    end

    Vue --- Tailwind
    Vue --> InertiaClient
    InertiaClient <===>|JSON over HTTP| InertiaLaravel
    InertiaLaravel --> Laravel
    Laravel --> Spatie
    Laravel <===>|Eloquent ORM / ACID Transactions| DB
```

---

## 5. DOCKER DEPLOYMENT ENVIRONMENT
Aplikasi ini dikemas menggunakan Docker Container untuk kemudahan portabilitas deployment:

*   **Dockerfile (Multi-Stage Build):**
    *   *Stage 1:* Node.js 20-alpine untuk compile & build asset frontend (Vite & Tailwind CSS v4).
    *   *Stage 2:* PHP 8.3-fpm-alpine terintegrasi Nginx dan Supervisor sebagai server produksi utama. GD extension dikonfigurasi dengan dukungan `freetype`, `jpeg`, dan `webp`.
*   **Docker Compose:**
    *   `app` container (web server & Laravel framework di port `8000`).
    *   `db` container (MySQL 8.0 server di port `3306`).
*   **Database Entrypoint Automation:**
    *   Menyertakan script [entrypoint.sh](file:///etc/entrypoint.sh) yang otomatis mendeteksi koneksi database dan menjalankan migrasi (`php artisan migrate --force`).
    *   **Keamanan Data:** Sistem melakukan cek eksistensi data (`User::count()`). Seeder hanya akan dijalankan jika database dalam kondisi kosong (Fresh Setup) untuk menghindari penumpukan seeder atau penghapusan data lama (`truncate`) saat kontainer di-restart.
