# Laverie Nails - Press-On Nail Measurement System

Sistem penentuan ukuran *press-on nails* berbasis web yang dirancang untuk membantu pengguna mengukur kuku secara mandiri. Sistem ini mengklasifikasikan ukuran kuku ke dalam kategori standar (XS, S, M, L, atau Custom) menggunakan algoritma perhitungan jarak (Manhattan/L1 Distance) berdasarkan metrik empiris.

Proyek ini dikembangkan sebagai bagian dari Tugas Akhir/Skripsi (D4 Tata Rias dan Kecantikan - Universitas Negeri Yogyakarta).

## 🚀 Tech Stack
- **Framework Backend:** Laravel 12 (PHP 8.2+)
- **Frontend & Styling:** Blade Templates, Tailwind CSS 4, Vite 7
- **Database:** SQLite (Lokal/Development), kompatibel dengan MySQL (Produksi)
- **Arsitektur:** Service-Layer Pattern (Controller Ringan, Logika Bisnis di Service)

## 📦 Prasyarat Instalasi (Prerequisites)
Pastikan sistem kamu sudah terinstal perangkat lunak berikut:
- PHP 8.2 atau lebih baru
- Composer 2.x
- Node.js (v18+) & NPM
- Git

## 🛠️ Cara Instalasi Lokal (Local Setup)

1. **Clone repository ini**
   ```bash
   git clone <URL_REPOSITORY_KAMU>
   cd <NAMA_FOLDER_PROYEK>
   ```

2. **Instal dependensi PHP**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`, lalu *generate application key*.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database (SQLite)**
   Secara *default*, Laravel 12 sudah menggunakan SQLite. Pastikan koneksi di `.env` sudah sesuai:
   ```env
   DB_CONNECTION=sqlite
   ```
   Jalankan migrasi dan *seeder* untuk memasukkan standar ukuran `XS, S, M, L` ke dalam *database*:
   ```bash
   php artisan migrate --seed
   ```

5. **Instal & Build dependensi Frontend (Tailwind 4 & Vite)**
   ```bash
   npm install
   npm run build
   ```

6. **Jalankan Aplikasi**
   Buka 2 terminal terpisah. Terminal pertama untuk menjalankan server backend:
   ```bash
   php artisan serve
   ```
   Terminal kedua untuk menjalankan *hot-reload* frontend (Vite):
   ```bash
   npm run dev
   ```
   Aplikasi sekarang dapat diakses di `http://localhost:8000`.

## 📜 Struktur Dokumen Penting
Silakan baca panduan arsitektur berikut sebelum berkontribusi atau mengubah kode:
- `ROADMAP.md` - Target dan tahapan pengerjaan proyek.
- `ARCHITECTURE.md` - Cetak biru struktur *database* dan arsitektur *Service Layer*.
- `AGENT_INSTRUCTIONS.md` - Tata tertib penulisan kode untuk AI *Agent* dan kolaborator.
- `DOMAIN_BLUEPRINT.md` - Batasan logika bisnis dan persyaratan skripsi.