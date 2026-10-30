# Plan Perbaikan Temuan Blackbox — Laverie

Sumber: sesi blackbox manual (browser Chromium) 2026-10-30 di `127.0.0.1:8000`, DB SQLite lokal.
Plan ini self-contained: setiap task punya anchor, root cause, langkah, acceptance check, dan commit message.

## Guardrails

- **Jangan push.** Berhenti setelah commit lokal.
- **Satu task = satu commit.** Jangan `git add .` / `git add -A`; stage per file.
- Jangan `migrate:fresh`, `db:seed`, atau `tinker` ke DB non-lokal.
- Jangan hapus produk hasil `PresentationCatalogSeeder` (id 3–8, slug `demo-presentation-*`).
- Jangan melonggarkan assertion tes agar lulus.
- Gate tiap task: `php artisan test` hijau + `./vendor/bin/pint --test` + `git diff --check`.
- Copywriting mengikuti **OPEN-1**; kalau user pilih opsi lain, T4 disesuaikan sebelum eksekusi.

## Peta temuan → task

| # | Temuan | Task | Severity |
|---|---|---|---|
| 1 | Hero terpotong di mobile + kontras rendah | T1 | Medium |
| 2 | Size type tanpa default + pesan validasi Inggris | T2 | Medium |
| 3 | Pesan kupon kedaluwarsa menyesatkan | T3 | Medium-low |
| 4 | Copy campur EN/ID + badge status EN | T4 | Low-medium |
| 5 | Produk/kategori lorem di storefront | T5 | High (go-live) |
| 6 | `/admin` 404 | T6 | Low |
| 7 | Verifikasi ulang menyeluruh | T7 | — |

## Keputusan yang perlu dijawab user (OPEN)

- **OPEN-1 — Bahasa.** Default plan: **copy fungsional → Indonesia** (form, tombol, status, keranjang), sedangkan wordmark/brand tetap. Kalau klien mau gaya editorial Inggris, T4 hanya dikerjakan sebagian (badge status + pesan validasi tetap ID).
- **OPEN-2 — Data lorem.** Default: **hapus** produk id 9–12 dan kategori id 6–9. Sudah diverifikasi: `order_items` = 0, `cart_items` = 0, `product_images` = 0 untuk produk tersebut. Alternatif: nonaktifkan (`is_active = 0`) — tapi chip kategori tetap muncul karena tabel `categories` tidak punya kolom aktif.
- **OPEN-3 — Default size.** Default: radio **Standard Size** ter-check, ukuran (XS/S/M/L) tetap harus dipilih sendiri. Alternatif: paksa user memilih tanpa default (butuh pesan error yang jelas).
- **OPEN-4 — Hero mobile.** Default: font dikecilkan agar tetap satu baris di 390px, bukan di-wrap dua baris.

---

## T1 — Hero muat di viewport mobile + overlay kontras

**Root cause.** `resources/views/welcome.blade.php:30` memakai `whitespace-nowrap` + `text-5xl`. Di 390px, `scrollWidth 392 > clientWidth 350` → teks terpotong ("Nail It, Fit It, Wear"). Tidak ada overlay gelap, sehingga script putih di atas foto terang kontrasnya rendah.

**Langkah**

1. Ganti class `<h1>` di `welcome.blade.php:30` menjadi:

   ```blade
   <h1 class="font-script text-[2.5rem] leading-[1.05] text-white sm:text-6xl lg:text-8xl">Nail It, Fit It, Wear It</h1>
   ```

   (hapus `whitespace-nowrap`, turunkan base ke 40px; 60px di `sm` masih muat di container 576px; 96px di `lg` muat di 928px.)

2. Tambah overlay setelah blok `data-hero-slides` ditutup (sekitar `welcome.blade.php:27`, sebelum `<div class="mx-auto w-full max-w-screen-2xl ...">`):

   ```blade
   <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-[#0C1C39]/55 via-[#0C1C39]/10 to-transparent" aria-hidden="true"></div>
   ```

   Slides memakai `-z-20`, jadi overlay `-z-10` berada di atas foto dan di bawah teks.

**Acceptance**

- Di 390px: `document.querySelector('h1').scrollWidth <= clientWidth + 1` dan `document.documentElement.scrollWidth === innerWidth`.
- Di 640px dan 1024px: headline tetap satu baris.
- Screenshot hero masih memperlihatkan foto (overlay tidak menutup gambar).

**Commit:** `fix(storefront): keep the hero headline readable on small screens`

---

## T2 — Size type punya default + pesan validasi Indonesia

**Root cause.** `resources/views/storefront/products/show.blade.php:47` dan `:51` hanya memakai `@checked(old('size_type') === ...)`, sehingga saat pertama dibuka tidak ada radio ter-check (padahal label "Standard Size" tampak aktif). `StoreCartItemRequest` tidak punya `messages()`, dan repo tidak punya folder `lang/`, jadi semua error jatuh ke bahasa Inggris: "The size type field is required."

**Langkah**

1. `show.blade.php:47` → `@checked(old('size_type', 'standard') === 'standard')`.
   `show.blade.php:51` tetap `@checked(old('size_type') === 'custom')`.

2. `app/Http/Requests/StoreCartItemRequest.php` — tambah dua method setelah `rules()`:

   ```php
   /** @return array<string, string> */
   public function messages(): array
   {
       return [
           'size_type.required' => 'Tipe ukuran wajib dipilih.',
           'size_type.enum' => 'Tipe ukuran tidak dikenali.',
           'standard_size.required_if' => 'Ukuran standar wajib dipilih.',
           'standard_size.enum' => 'Ukuran standar tidak dikenali.',
           'custom_measurements.required_if' => 'Ukuran custom wajib diisi.',
           'product_id.exists' => 'Produk tidak tersedia.',
           'quantity.min' => 'Jumlah minimal 1 item.',
       ];
   }

   /** @return array<string, string> */
   public function attributes(): array
   {
       return [
           'size_type' => 'tipe ukuran',
           'standard_size' => 'ukuran standar',
           'custom_measurements' => 'ukuran custom',
           'product_id' => 'produk',
           'quantity' => 'jumlah',
       ];
   }
   ```

3. Cek `UpdateCartItemRequest` — kalau punya pesan quantity, samakan gayanya (opsional, hanya kalau sudah ada `messages()`).

**Acceptance**

- Buka PDP fresh: radio Standard Size `checked === true`.
- Klik "Tambah ke Keranjang" tanpa memilih ukuran → status drawer: "Ukuran standar wajib dipilih." (bukan bahasa Inggris).
- `php artisan test tests/Feature/Cart tests/Feature/Storefront` hijau.

**Commit:** `fix(product): default the size type and localise cart validation messages`

---

## T3 — Pesan kupon sesuai penyebabnya

**Root cause.** `app/Http/Controllers/CartController.php:38-42` memakai satu pesan untuk semua kegagalan: *"Kode diskon tidak valid untuk subtotal keranjang saat ini."* Kupon yang ada tapi kedaluwarsa dapat pesan yang menyalahkan subtotal. `CheckoutService.php:71-74` punya pesan berbeda lagi, jadi tidak konsisten.

**Langkah**

1. `app/Models/Coupon.php` — tambah method:

   ```php
   public function invalidReason(int $subtotal): ?string
   {
       if (! $this->is_active) {
           return 'Kode diskon sudah tidak aktif.';
       }

       if ($this->expires_at && $this->expires_at->isPast()) {
           return 'Kode diskon sudah kedaluwarsa.';
       }

       if ($subtotal < $this->minimum_order_amount) {
           $minimum = number_format($this->minimum_order_amount, 0, ',', '.');

           return "Minimal belanja Rp {$minimum} untuk memakai kode ini.";
       }

       if ($this->discountFor($subtotal) < 1) {
           return 'Kode diskon tidak dapat dipakai untuk keranjang ini.';
       }

       return null;
   }
   ```

   Catatan: `discountFor()` memanggil `isValidForSubtotal()`; urutan cek di atas memastikan pesan spesifik keluar lebih dulu.

2. `CartController::applyCoupon()` — ganti blok `if (! $coupon || ...)` menjadi:

   ```php
   if (! $coupon) {
       throw ValidationException::withMessages(['code' => 'Kode diskon tidak ditemukan.']);
   }

   if ($reason = $coupon->invalidReason($subtotal)) {
       throw ValidationException::withMessages(['code' => $reason]);
   }
   ```

3. `CheckoutService.php` — ganti pesan generik di blok kupon menjadi `$reason = $coupon?->invalidReason($subtotal) ?? 'Kode diskon tidak ditemukan.';` lalu `throw ValidationException::withMessages(['coupon' => $reason]);`. Pastikan perilaku saat `$couponId !== null` tapi kupon terhapus tetap ditangani.

4. `tests/Feature/Cart/CartCouponTest.php` — tambah assertion pesan pada 3 tes yang sudah ada:
   - unknown → `assertSessionHasErrors(['code' => 'Kode diskon tidak ditemukan.'])`
   - expired → pesan mengandung `kedaluwarsa`
   - minimum → pesan mengandung `Minimal belanja`

**Acceptance**

- Browser: kupon `PRESENTASI10` dengan `expires_at` kemarin → "Kode diskon sudah kedaluwarsa."
- Kupon `minimum_order_amount` di atas subtotal → "Minimal belanja Rp …".
- Kode acak → "Kode diskon tidak ditemukan."
- `php artisan test tests/Feature/Cart tests/Feature/Checkout` hijau.

**Commit:** `fix(cart): explain exactly why a coupon cannot be used`

---

## T4 — Copy Indonesia konsisten + label status

**Root cause.** Sebagian label fungsional masih Inggris di tengah UI Indonesia, dan badge status mencetak nilai enum mentah (`pending`, `shipped`).

**Langkah**

1. `app/Enums/PaymentStatus.php` — tambah:

   ```php
   public function label(): string
   {
       return match ($this) {
           self::Pending => 'Menunggu pembayaran',
           self::Paid => 'Lunas',
           self::Failed => 'Gagal',
           self::Expired => 'Kedaluwarsa',
       };
   }
   ```

2. `app/Enums/FulfillmentStatus.php` — tambah:

   ```php
   public function label(): string
   {
       return match ($this) {
           self::Pending => 'Menunggu',
           self::Processing => 'Diproses',
           self::Shipped => 'Dikirim',
           self::Completed => 'Selesai',
       };
   }
   ```

3. Ganti `->value` → `->label()` pada tampilan badge/opsi (bukan pada form value):
   - `resources/views/checkout/payment.blade.php:11`
   - `resources/views/admin/orders/index.blade.php:50-51`
   - `resources/views/admin/orders/show.blade.php:17-18` dan `:96` (teks `<option>`)
   - `resources/views/customer/orders/show.blade.php:10-11`
   - `resources/views/dashboard.blade.php:30-31`
   - Cek juga halaman lacak pesanan (`grep -rn "payment_status->value" resources/views`).

4. Ganti string berikut (teks saja, jangan ubah class):

   | File:line | Sekarang | Menjadi |
   |---|---|---|
   | `components/storefront/cart-drawer.blade.php:5` | `Cart (<span>0</span> items)` | `Keranjang (<span>0</span> item)` |
   | `checkout/create.blade.php:8` | `Secure checkout` | `Pembayaran aman` |
   | `checkout/create.blade.php:48` | `Customer details` | `Detail pelanggan` |
   | `checkout/create.blade.php:69` | `Shipping details` | `Detail pengiriman` |
   | `checkout/create.blade.php:143` | `Place order` | `Buat pesanan` |
   | `checkout/payment.blade.php:7` | `Order confirmed` | `Pesanan dikonfirmasi` |
   | `checkout/payment.blade.php:17` | `Order number` | `Nomor pesanan` |
   | `checkout/payment.blade.php:46` | `Shipping` | `Ongkos kirim` |
   | `components/storefront/product-card.blade.php:23` | `View / Add` | `Lihat / Tambah` |
   | `components/storefront/navbar.blade.php:15-17` | `Shop all` / `Sizing` / `Measurement guide` | `Semua produk` / `Ukuran` / `Panduan ukuran` |
   | `auth/login.blade.php:2` | `Welcome back` + subtitle EN | `Selamat datang kembali` + subtitle ID |
   | `admin/orders/show.blade.php:36,42` | `Customer details` / `Shipping details` | `Detail pelanggan` / `Detail pengiriman` |

5. Cek `php artisan test` — beberapa view test mungkin meng-assert string lama; perbarui **teks yang di-assert**, jangan melonggarkan assertion.

**Acceptance**

- `grep -rn "Customer details\|Place order\|Cart (\|View / Add\|Shop all\|Order confirmed" resources/views` → kosong.
- Badge status di payment, lacak pesanan, dashboard, admin menampilkan kata Indonesia.
- Semua tes hijau.

**Commit:** `refactor(copy): make cart, checkout, and status labels consistently Indonesian`

---

## T5 — Bersihkan produk/kategori lorem dari storefront

**Root cause.** Produk id 9–12 (`A Eligendi Doloremque`, `Ut Nobis Expedita`, `Voluptatum Earum Corporis`, `Et Dicta Est`) dan kategori id 6–9 bernama lorem masih `is_active = 1`, tanpa gambar, dan tampil paling atas di `/produk` karena diurutkan terbaru. Bukan berasal dari `PresentationCatalogSeeder`.

**Verifikasi sebelum hapus (ulangi, jangan percaya plan ini buta):**

```bash
php -r "require 'vendor/autoload.php'; \$a=require 'bootstrap/app.php'; \$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo 'orders=', Illuminate\Support\Facades\DB::table('order_items')->whereIn('product_id',[9,10,11,12])->count(),
     ' carts=', Illuminate\Support\Facades\DB::table('cart_items')->whereIn('product_id',[9,10,11,12])->count(),
     ' images=', Illuminate\Support\Facades\DB::table('product_images')->whereIn('product_id',[9,10,11,12])->count(), PHP_EOL;"
```

Kalau ketiganya `0`, lanjut. **Kalau tidak, STOP** dan lapor.

**Langkah (DB lokal saja):**

```bash
php -r "require 'vendor/autoload.php'; \$a=require 'bootstrap/app.php'; \$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
App\Models\Product::whereIn('id',[9,10,11,12])->delete();
App\Models\Category::whereIn('id',[6,7,8,9])->delete();
echo 'products=', App\Models\Product::count(), ' categories=', App\Models\Category::count(), PHP_EOL;"
```

Produk 9–12 tidak dipakai order/cart/gambar (sudah diverifikasi 2026-10-30), dan `products.category_id` cascade saat kategori dihapus.

**Acceptance**

- `/produk` hanya menampilkan 8 produk; tidak ada kartu "No image".
- Filter kategori hanya berisi 5 kategori demo + kategori sah lainnya.
- Halaman admin produk tidak lagi memuat nama lorem.
- Untuk production: jalankan pengecekan yang sama dulu, lalu hapus lewat DB/tinker production **setelah backup**.

**Commit:** `chore(catalog): remove leftover lorem products and categories`

---

## T6 — `/admin` tidak 404

**Root cause.** Grup route hanya mendefinisikan `/admin/dashboard`, `/admin/orders`, dst. `GET /admin` tidak punya route.

**Langkah.** Di `routes/web.php`, sebelum grup `Route::prefix('admin')` (sekitar baris 91), tambah:

```php
Route::redirect('/admin', '/admin/orders');
```

Redirect ini publik; middleware `auth` + `role:admin` di tujuan tetap menjaga akses.

**Acceptance**

- Guest `GET /admin` → 302 ke `/admin/orders` → akhirnya halaman login.
- Admin login `GET /admin` → daftar pesanan.
- `php artisan route:list --path=admin | head` menampilkan redirect.

**Commit:** `fix(routes): redirect /admin to the orders dashboard`

---

## T7 — Verifikasi akhir (tanpa commit)

1. `./vendor/bin/pint --test`
2. `php artisan test` (target: semua hijau, jumlah tes ≥ 247)
3. `npm run build`
4. `git diff --check`
5. Blackbox ulang di browser:
   - 390px: hero tidak terpotong (`scrollWidth === innerWidth`), CTA masih terlihat.
   - PDP fresh: Standard Size ter-check; add tanpa ukuran → pesan Indonesia.
   - Kupon: expired / min-spend / unknown → tiga pesan berbeda.
   - Copy: keranjang "Keranjang (1 item)", checkout "Detail pelanggan", tombol "Buat pesanan", badge "Menunggu pembayaran".
   - `/produk`: 8 produk, tanpa "No image", tanpa chip kategori lorem.
   - `/admin` sebagai admin → daftar pesanan.
6. Catat hasil di `PLAN_BLACKBOX_FIXES.md` (checkbox/✅) lalu commit `docs(qa): record blackbox fixes verification`.

## Urutan commit

1. `fix(storefront): keep the hero headline readable on small screens`
2. `fix(product): default the size type and localise cart validation messages`
3. `fix(cart): explain exactly why a coupon cannot be used`
4. `refactor(copy): make cart, checkout, and status labels consistently Indonesian`
5. `chore(catalog): remove leftover lorem products and categories`
6. `fix(routes): redirect /admin to the orders dashboard`
7. `docs(qa): record blackbox fixes verification` (opsional, setelah T7)

---

## Hasil eksekusi (2026-10-30)

| # | Status | Commit | Catatan |
|---|--------|--------|---------|
| T1 | ✅ | `fix(storefront): keep the hero headline readable on small screens` | Hero 390px: `scrollWidth 350 = clientWidth 350`, tanpa overflow; headline tetap satu baris |
| T2 | ✅ | `fix(product): default the size type and localise cart validation messages` | Standard Size ter-check; pesan validasi Indonesia |
| T3 | ✅ | `fbe2fc8` | `Coupon::invalidReason()` jadi sumber tunggal pesan kupon |
| T4 | ✅ | `50cdad6` | `label()` di 2 enum + copy ID di cart/checkout/status; 2 tes copy disesuaikan |
| T5 | ✅ (data lokal) | — | Produk id 9–12 dan kategori id 6–9 dihapus dari SQLite lokal setelah verifikasi 0 order/0 cart/0 image; tidak ada perubahan kode untuk di-commit |
| T6 | ✅ | `1b37b2c` | `/admin` → `/admin/orders` + `AdminEntryPointTest` |
| T7 | ✅ | `de46675` | 250 tes / 1503 assertion lulus, `npm run build` lulus, blackbox ulang lulus |

**Perbaikan tambahan saat verifikasi:** banner hero memakai `object-left sm:object-center` agar wordmark "laverie" tidak terpotong di 390px (commit `de46675`).

**Sisa terbuka**
- Kontras navbar overlay: logo/nav putih di atas bagian foto yang terang. `GlobalPremiumUiTest` dan `HomepageTest` mengunci `bg-transparent` dan melarang `data-homepage-navbar-contrast`, jadi perlu keputusan desain sebelum diubah.
- String Inggris di footer (`Sizing`, `Measurement guide`, dsb.) belum disentuh.
- Pembayaran sampai status `settlement` Midtrans dan race stok 2 tab belum diuji.
- Verifikasi sender Brevo menunggu MX `laverie.web.id` (Cloudflare Email Routing).
