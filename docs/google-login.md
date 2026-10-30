# Google Login Setup

Fitur ini menambahkan tombol **Continue with Google** di halaman login dan register. Login manual dengan email/password tetap tersedia.

## 1. Yang sudah disiapkan di aplikasi

- `laravel/socialite` terpasang.
- Route:
  - `GET /auth/google/redirect` → `auth.google.redirect`
  - `GET /auth/google/callback` → `auth.google.callback`
- Kolom baru di `users`: `provider`, `provider_id`; kolom `password` sekarang nullable.
- Login Google pertama otomatis membuat akun customer.
- Email yang sudah terdaftar akan dihubungkan ke Google, tanpa menghapus password lama.
- Akun admin sengaja **tidak boleh** login lewat Google; admin tetap memakai email/password.
- User yang hanya punya login Google bisa membuat password di **Profil → Keamanan**.
- Tombol Google tidak muncul kalau `GOOGLE_CLIENT_ID` atau `GOOGLE_CLIENT_SECRET` masih kosong.

## 2. Buat OAuth client di Google Cloud

1. Buka <https://console.cloud.google.com/>.
2. Buat project baru, misalnya **Laverie Nails**, atau pilih project yang sudah ada.
3. Buka **APIs & Services → OAuth consent screen** (di UI baru bisa bernama **Google Auth Platform**).
4. Pilih **External** untuk website publik.
5. Isi:
   - App name: `Laverie Nails`
   - User support email: email client
   - Developer contact email: email developer/client
   - Authorized domain: `laverie.web.id`
6. Di bagian **Scopes**, cukup tambahkan:
   - `openid`
   - `email`
   - `profile`
7. Di bagian **Test users**, tambahkan email Google yang akan dipakai untuk testing.
8. Buka **APIs & Services → Credentials → Create credentials → OAuth client ID**.
9. Application type: **Web application**.
10. Isi **Authorized redirect URIs** persis seperti ini:
    - `http://localhost:8000/auth/google/callback`
    - `http://127.0.0.1:8000/auth/google/callback` (opsional, kalau membuka lewat 127.0.0.1)
    - `https://laverie.web.id/auth/google/callback`
11. Klik **Create**, lalu salin **Client ID** dan **Client Secret**.

> Redirect URI harus sama persis: scheme, host, port, dan path. Jangan menambahkan slash di akhir.

## 3. Isi environment lokal

Tambahkan ke `.env`:

```dotenv
GOOGLE_CLIENT_ID=isi-client-id-dari-google
GOOGLE_CLIENT_SECRET=isi-client-secret-dari-google
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

Lalu jalankan migrasi dan bersihkan cache konfigurasi:

```bash
php artisan migrate
php artisan config:clear
```

Jalankan server dan buka **http://localhost:8000/login** — jangan campur dengan `127.0.0.1` kalau redirect URI-nya memakai `localhost`, karena session/cookie dianggap host berbeda.

## 4. Tes lokal

1. Klik **Continue with Google**.
2. Pilih akun Google yang sudah didaftarkan sebagai test user.
3. Setelah kembali ke aplikasi, cek:
   - login berhasil dan diarahkan ke dashboard;
   - baris baru di tabel `users` punya `provider = google` dan `provider_id` terisi;
   - keranjang tamu ikut pindah ke akun.
4. Logout, lalu login lagi lewat Google untuk memastikan akun yang sama dipakai.
5. Coba login manual dengan email/password akun lama; harus tetap bisa.
6. Buka **Profil → Keamanan** sebagai user Google-only; harus muncul **Buat Password**, bukan **Ganti Password**.

## 5. Deploy produksi

1. Tambahkan tiga variabel yang sama di `.env` VPS:
   ```dotenv
   GOOGLE_CLIENT_ID=...
   GOOGLE_CLIENT_SECRET=...
   GOOGLE_REDIRECT_URI=https://laverie.web.id/auth/google/callback
   ```
2. Jalankan:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   ```
3. Pastikan `https://laverie.web.id/auth/google/callback` sudah terdaftar di Google Cloud.
4. Ubah status OAuth consent screen dari **Testing** menjadi **Published** agar user umum bisa login.
5. Untuk publikasi, Google biasanya meminta URL **Privacy Policy** dan **Terms of Service**. Siapkan halaman tersebut sebelum publish.

## 6. Kalau error

| Error | Penyebab umum |
|---|---|
| `redirect_uri_mismatch` | URI di Google Cloud tidak sama persis dengan `GOOGLE_REDIRECT_URI`. |
| `invalid_client` | Client ID/Secret salah, atau config masih cache. |
| `Access blocked` | OAuth consent screen masih Testing dan email belum jadi test user. |
| State mismatch / session hilang | Pindah host (`localhost` vs `127.0.0.1`) atau cookie domain berubah. |
| Tombol Google tidak muncul | `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` kosong atau `config:cache` belum dijalankan ulang. |
| Admin gagal login Google | Disengaja; admin harus memakai email/password. |

## 7. Catatan keamanan

- Jangan commit `.env` atau Client Secret.
- Jangan menonaktifkan pengecekan email terverifikasi dari Google.
- Jangan mengaktifkan Google untuk akun admin tanpa 2FA.
- Tetap sediakan login manual; Google hanya opsi tambahan.
