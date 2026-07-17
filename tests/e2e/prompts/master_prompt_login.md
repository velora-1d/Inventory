# MASTER PROMPT: PENGUJIAN KOMPREHENSIF - HALAMAN LOGIN INVENTORY

Jalankan pengujian E2E (End-to-End) menggunakan TestSprite secara mendalam dan menyeluruh khusus untuk **Halaman Login** pada sistem Inventory. Pengujian wajib mencakup semua aspek fungsionalitas, validasi, detail visual, transisi tema, dan penanganan kesalahan (error handling).

---

## 1. DETIL ELEMEN & VISUAL (UI/UX)
Pastikan layout dua-kolom (split panel) dan estetika premium berjalan dengan benar:
*   **Panel Kiri (Branding):**
    *   Verifikasi logo box berukuran besar `h-[4.5rem] w-[4.5rem]` dengan background emas `#c8a96e` tampil presisi.
    *   Verifikasi teks judul `INVENTORY` berukuran besar `text-3xl` berwarna putih.
    *   Verifikasi badge "Sistem Real-time Aktif" dengan dot hijau berkedip.
    *   Verifikasi headline "Kelola Stok Lebih Cerdas" berukuran `text-6xl`.
    *   Verifikasi copyright year menampilkan tahun dinamis saat ini (`2026`).
    *   Verifikasi pergeseran konten ke kanan sejauh ~15% (`pl-20 pr-8`) di panel kiri.
*   **Panel Kanan (Form):**
    *   Verifikasi greeting text "Selamat datang 👋" dan sub-judul.
    *   Verifikasi field input email dan password memiliki ikon di dalam kolom.
    *   Verifikasi tombol toggle tema 🌙/☀️ berada di sudut kanan atas.

---

## 2. KASUS UJI & SKENARIO FLOW (FUNCTIONAL)
Lakukan pengujian terhadap skenario interaksi pengguna berikut:
1.  **Validasi Input Kosong (HTML5 Validation):**
    *   Kosongkan field Email dan Password.
    *   Klik tombol "Masuk ke Sistem".
    *   Pastikan browser memunculkan warning validasi bawaan (`required` attribute) dan memfokuskan kursor ke input yang kosong.
2.  **Skenario Kredensial Salah:**
    *   Masukkan email dengan format salah (misal: `salah-email`). Pastikan sistem/browser memvalidasi format email.
    *   Masukkan email valid tapi belum terdaftar (misal: `tidakdaftar@example.com`) dan password acak.
    *   Klik masuk dan pastikan pesan kesalahan merah (InputError) muncul di bawah field terkait.
3.  **Fitur Sembunyi/Tampil Sandi (Eye Toggle):**
    *   Ketik password ke dalam input. Pastikan tipenya ter-masking (`type="password"`).
    *   Klik tombol ikon mata di sebelah kanan input password.
    *   Pastikan input bertipe teks biasa (`type="text"`) dan karakter sandi terlihat jelas.
    *   Klik kembali tombol ikon mata untuk memastikan tipe input kembali ter-masking.
4.  **Fitur Remember Me (Checkbox):**
    *   Verifikasi checkbox "Ingat saya" dapat dicentang dan diubah statusnya secara interaktif.
5.  **Skenario Login Sukses (Happy Path):**
    *   Masukkan email valid (`admin@inventory.com`) dan password benar (`password`).
    *   Klik "Masuk ke Sistem" (Verifikasi status button berubah menjadi loading "Memproses...").
    *   Pastikan sistem berhasil melakukan autentikasi dan melakukan pengalihan rute (redirect) secara mulus ke halaman `/dashboard`.

---

## 3. PENGUJIAN TRANSISI TEMA (LIGHT & DARK MODE)
*   Klik tombol toggle tema 🌙/☀️ di sudut kanan atas.
*   Verifikasi bahwa class `dark` ditambahkan pada elemen `<html>` dan warna background berganti secara mulus (smooth transition) ke warna gelap yang konsisten (espresso gelap/zinc).
*   Klik kembali tombol toggle tema and pastikan class `dark` hilang serta warna background kembali menjadi krem hangat (`#faf6eb`).
*   Pastikan seluruh teks input, border, dan tombol tetap memiliki kontras tinggi yang mudah dibaca di kedua mode tersebut.
