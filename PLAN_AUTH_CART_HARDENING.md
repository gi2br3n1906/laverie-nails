# PLAN — Auth, Cart & Inventory Hardening (Laverie)

Repo: `D:\Coding\Project Kerjaan\Laverie` · remote `gi2br3n1906/laverie-nails` · branch `main`
Disusun: 2026-10-30 · basis: Expert Council run `laverie-auth-cart-hardening`
Plan ini ditulis supaya bisa dieksekusi model murah tanpa membaca ulang seluruh repo.

---

## 0. Guardrails (wajib dipatuhi)

- **Jangan push.** Berhenti setelah commit lokal.
- **Jangan** menjalankan `migrate`, `db:seed`, atau `artisan tinker` terhadap database yang bukan lokal.
  Cek dulu: `php artisan about | grep -i environment` harus `local`, dan `DB_CONNECTION` harus `sqlite`/DB lokal.
- **Jangan** membaca, mencetak, atau mengubah nilai di `.env`. Hanya membaca **nama** key bila perlu.
- **Jangan** memanggil Midtrans Live. Semua test memakai `Http::fake()`; tidak ada request jaringan nyata.
- **Jangan** mengubah tes agar lulus dengan melonggarkan assertion. Kalau tes merah, perbaiki kode.
- Satu task = satu commit. Jangan gabungkan dua task dalam satu commit.
- Gate setiap task: `php artisan test` hijau + `./vendor/bin/pint --test` lulus + `git diff --check` bersih.

---

## Status saat plan ditulis (sudah selesai, jangan dikerjakan ulang)

| # | Perubahan | Bukti |
|---|---|---|
| ✅ | `GET/POST /checkout` wajib login | `routes/web.php:41-42` (`middleware('auth')`) |
| ✅ | `/checkout/logistics/*` wajib login | `routes/web.php:43-44` |
| ✅ | Cart tamu dipindahkan ke akun saat login/daftar | `CartService::claimGuestCart()` dipanggil `LoginController.php:33`, `RegisterController.php:38` |
| ✅ | Redirect kembali ke checkout setelah login/daftar | `redirect()->intended(...)` di kedua controller |
| ✅ | Tes regresi auth checkout | `tests/Feature/Checkout/CheckoutAuthenticationTest.php` (3 tes) |
| ✅ | Suite penuh hijau | `php artisan test` → **233 passed, 1411 assertions** |
| ✅ | T1: stok dirilis saat pembayaran expired/failed, idempotent | `StockReleaseTest` (4 tes) + migration `stock_restored_at` |
| ✅ | T2: cart tamu di-merge tanpa baris duplikat, qty di-clamp ke stok | `CartMergeOnLoginTest` (4 tes) |
| ✅ | T3: throttle registrasi (6/menit) & lacak pesanan (10/menit) | `RegistrationThrottleTest`, `OrderTrackingThrottleTest` |

T1–T3 **sudah dikerjakan dan hijau** (suite akhir: 243 passed, 1469 assertions).
T4 (commit staging) dan T5/T6 (butuh keputusan/kredensial) belum.

---

## T1 — Release stok saat pembayaran expired/failed (P0, uang & inventory)

**Root cause.** Stok dikurangi saat order dibuat, dan tidak pernah dikembalikan.

- `app/Services/CheckoutService.php:108` → `$product->decrement('stock', $cartItem->quantity)` di dalam transaksi order.
- `app/Http/Controllers/MidtransNotificationController.php:23-35` → hanya menulis `payment_status`, tidak menyentuh stok.
- `grep -rn "increment('stock')" app/` → **0 hasil**. Tidak ada kode kompensasi di mana pun.
- `app/Enums/PaymentStatus.php` sudah punya `Failed` dan `Expired`; `PaymentService.php:114-115` memetakan
  `deny/cancel` → `Failed`, `expire` → `Expired`.

**Dampak.** Setiap QRIS/VA yang ditinggalkan pelanggan menghapus stok dari penjualan secara permanen.

### Langkah

1. **Migration baru** `database/migrations/2026_10_30_100000_add_stock_restored_at_to_orders_table.php`
   (pola ikuti `2026_08_02_110000_add_order_notes_to_orders_table.php`):

   ```php
   Schema::table('orders', function (Blueprint $table): void {
       $table->timestamp('stock_restored_at')->nullable()->after('snap_token');
   });
   ```

   `down()`: `$table->dropColumn('stock_restored_at');`

2. **Model** `app/Models/Order.php`
   - tambahkan `'stock_restored_at'` ke `$fillable` (daftar di sekitar baris 43-44),
   - tambahkan `'stock_restored_at' => 'datetime'` ke `casts()` (sekitar baris 66-67).

3. **Webhook** `app/Http/Controllers/MidtransNotificationController.php` — ganti blok transaksi (baris 23-35) menjadi:

   ```php
   DB::transaction(function () use ($payload, $incomingStatus): void {
       $order = Order::query()->lockForUpdate()->findOrFail((string) $payload['order_id']);

       if ($order->payment_status === PaymentStatus::Paid) {
           return;
       }

       // Stok sudah dilepas: jangan pernah menandai paid lagi (lihat OPEN-1).
       if ($order->stock_restored_at !== null && $incomingStatus === PaymentStatus::Paid) {
           return;
       }

       if (in_array($incomingStatus, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
           if ($order->stock_restored_at === null) {
               $order->load('items');

               foreach ($order->items as $item) {
                   if ($item->product_id === null) {
                       continue;
                   }

                   Product::query()
                       ->whereKey($item->product_id)
                       ->lockForUpdate()
                       ->increment('stock', $item->quantity);
               }

               $order->stock_restored_at = now();
           }

           $order->payment_status = $incomingStatus;
           $order->save();

           return;
       }

       if ($incomingStatus === PaymentStatus::Pending && $order->payment_status !== PaymentStatus::Pending) {
           return;
       }

       $order->update(['payment_status' => $incomingStatus]);
   }, 3);
   ```

   Tambahkan `use App\Models\Product;` di header controller.

4. **Tes baru** `tests/Feature/Checkout/StockReleaseTest.php`.
   Pakai helper payload bertanda tangan dari `tests/Feature/Checkout/MidtransWebhookTest.php`
   (signature = `sha512(order_id + status_code + gross_amount + server_key)`), jangan bikin skema signature sendiri.

   Kasus wajib:
   - `expire` setelah checkout → `product.stock` kembali ke nilai awal, `orders.stock_restored_at` tidak null.
   - `expire` dikirim **dua kali** → stok hanya naik sekali (idempotent).
   - `paid` lalu `expire` → stok **tetap** terpotong, `stock_restored_at` tetap null.
   - `expire` lalu `settlement` → `payment_status` tetap `expired`, stok tidak berubah (perilaku OPEN-1).

### Acceptance

```bash
php artisan test tests/Feature/Checkout/StockReleaseTest.php tests/Feature/Checkout/MidtransWebhookTest.php
grep -rn "increment('stock')" app/          # harus >= 1 hasil
php artisan test && ./vendor/bin/pint --test && git diff --check
```

**Commit:** `fix(checkout): release reserved stock when payment expires or fails`

---

## T2 — Merge cart tamu ke cart akun tanpa duplikat (P0, data)

**Root cause.** `app/Services/CartService.php:20-34` `claimGuestCart()` melakukan satu `UPDATE` massal
(`user_id` diisi, `session_id` dikosongkan) tanpa memeriksa baris yang sudah ada.

**Dampak.** Pembeli yang sudah punya produk+ukuran sama di akunnya mendapat **dua baris** cart, dan qty gabungan
tidak pernah divalidasi terhadap `product.stock` (`ensureStock()` hanya dipanggil di `add()`/`updateQuantity()`).

### Langkah

1. Ganti isi `claimGuestCart()` menjadi merge per baris di dalam transaksi:

   ```php
   public function claimGuestCart(int $userId, ?string $guestSessionId): void
   {
       if (! is_string($guestSessionId) || $guestSessionId === '') {
           return;
       }

       DB::transaction(function () use ($userId, $guestSessionId): void {
           $guestItems = CartItem::query()
               ->whereNull('user_id')
               ->where('session_id', $guestSessionId)
               ->lockForUpdate()
               ->get();

           foreach ($guestItems as $guestItem) {
               $existing = CartItem::query()
                   ->where('user_id', $userId)
                   ->whereNull('session_id')
                   ->where('product_id', $guestItem->product_id)
                   ->where('size_type', $guestItem->size_type)
                   ->where('size_signature', $guestItem->size_signature)
                   ->lockForUpdate()
                   ->first();

               if ($existing === null) {
                   $guestItem->update(['user_id' => $userId, 'session_id' => null]);

                   continue;
               }

               $stock = (int) Product::query()->whereKey($guestItem->product_id)->value('stock');
               $combined = min($stock, $existing->quantity + $guestItem->quantity);

               if ($combined < 1) {
                   $existing->delete();
               } else {
                   $existing->update(['quantity' => $combined]);
               }

               $guestItem->delete();
           }
       });
   }
   ```

2. **Tes baru** `tests/Feature/Cart/CartMergeOnLoginTest.php`:
   - tamu + akun punya produk+ukuran sama → setelah login **satu** baris, qty = jumlah keduanya.
   - qty gabungan melebihi stok → qty di-clamp ke stok, tetap satu baris.
   - ukuran berbeda → dua baris tetap terpisah.
   - produk stok 0 → baris dihapus, tidak ada qty 0.

   Tes login/daftar yang sudah ada di `CheckoutAuthenticationTest` harus tetap hijau (kasus tanpa konflik).

### Acceptance

```bash
php artisan test tests/Feature/Cart/CartMergeOnLoginTest.php tests/Feature/Checkout/CheckoutAuthenticationTest.php
php artisan test && ./vendor/bin/pint --test && git diff --check
```

**Commit:** `fix(cart): merge guest cart into account cart without duplicate rows`

---

## T3 — Rate limit registrasi & pelacakan pesanan (P1, security)

**Root cause.** `app/Http/Requests/Auth/LoginRequest.php:41-72` membatasi login (5 percobaan),
tetapi `app/Http/Requests/Auth/RegisterRequest.php` tidak punya limiter sama sekali.
`POST /lacak-pesanan` (`routes/web.php:47`) juga tanpa throttle.

### Langkah

1. `routes/web.php:54` → `Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');`
2. `routes/web.php:47` → tambahkan `->middleware('throttle:10,1')` pada `orders.track.store`.
3. **Tes** `tests/Feature/Auth/RegistrationThrottleTest.php`:
   - 6 POST registrasi beruntun dari IP yang sama → request ke-7 mendapat **429**.
   - `POST /lacak-pesanan` ke-11 dalam semenit → **429**.

   Catatan: `RefreshDatabase` + `RateLimiter` memakai cache; panggil `RateLimiter::clear()` di `setUp()`
   supaya tes tidak saling mencemari.

### Acceptance

```bash
php artisan test tests/Feature/Auth tests/Feature/OrderTrackingTest.php
php artisan test && ./vendor/bin/pint --test && git diff --check
```

**Commit:** `feat(auth): rate limit registration and order tracking lookups`

---

## T4 — Pisahkan working tree jadi commit per intent (P1, hygiene)

Saat plan ditulis ada **28 entri** di `git status --short`, bercampur tiga pekerjaan.
Baseline perintah di bawah mengikuti status tersebut — **jalankan `git status --short` dulu** dan sesuaikan
kalau daftarnya sudah berubah sejak plan ini dibuat. Jangan pernah `git add .` atau `git add -A`.

```bash
# 1) Dokumentasi konfigurasi Midtrans Sandbox
git add .env.example
git diff --cached --stat            # harus 1 file
git commit -m "docs(env): clarify Midtrans sandbox key and endpoint pairing"

# 2) Katalog, review demo, mock ongkir lokal
git add app/Http/Controllers/StorefrontProductController.php \
        app/Models/CatalogReview.php \
        app/Services/CatalogReviewService.php \
        app/Services/MarketplaceService.php \
        app/Services/LogisticsService.php \
        config/services.php \
        database/factories/CatalogReviewFactory.php \
        database/seeders/PresentationCatalogSeeder.php \
        database/seeders/assets \
        database/migrations/2026_10_06_100000_add_is_demo_to_catalog_reviews_table.php \
        resources/views/admin/customers/index.blade.php \
        resources/views/components/storefront/review-card.blade.php \
        resources/views/storefront/products/show.blade.php \
        resources/views/welcome.blade.php \
        tests/Feature/Admin/CustomerManagementTest.php \
        tests/Feature/Frontend/HomepageTest.php \
        tests/Feature/Marketplace/CatalogReviewTest.php \
        tests/Feature/Marketplace/PresentationCatalogSeederTest.php \
        tests/Feature/Checkout/LogisticsServiceTest.php
git diff --cached --stat
git commit -m "feat(catalog): demo reviews, local shipping mock, storefront polish"

# 3) Checkout wajib login + cart tamu + tutup endpoint logistik
git add routes/web.php \
        app/Services/CartService.php \
        app/Http/Controllers/Auth/LoginController.php \
        app/Http/Controllers/Auth/RegisterController.php \
        tests/Feature/Checkout/CheckoutFlowTest.php \
        tests/Feature/Checkout/CheckoutViewTest.php \
        tests/Feature/Checkout/CheckoutAuthenticationTest.php \
        PLAN_AUTH_CART_HARDENING.md
git diff --cached --stat
git commit -m "feat(checkout): require login and carry guest cart into the account"
```

**Kalau satu file ternyata memuat dua intent** (mis. `routes/web.php` juga berisi perubahan non-checkout),
pecah dengan `git add -p routes/web.php` dan pilih hunk yang relevan. Jangan memaksa satu file masuk dua commit
tanpa `-p`.

**Verifikasi akhir:**

```bash
git status --short          # harus bersih (atau hanya sisa yang memang belum diputuskan)
git log --oneline -3
```

Jangan push.

---

## T5 — Notifikasi email pesanan (P2, opsional, butuh keputusan)

**Temuan.** `grep -rn "ShouldQueue|Notification::|Mail::" app/` → **0 hasil**. Aplikasi sama sekali tidak
mengirim email: pelanggan tidak menerima konfirmasi pesanan maupun bukti pembayaran.

**Kenapa belum dikerjakan sekarang.** Butuh keputusan produk + kredensial SMTP. `config/queue.php` default
`database` (`.env.example:40`), jadi kalau dibuat queued job, deploy **wajib** menjalankan `queue:work`.

Kalau disetujui: `OrderPlacedNotification` (setelah `CheckoutService::placeOrder`) dan `OrderPaidNotification`
(setelah webhook menandai paid), keduanya `ShouldQueue`, dengan `Notification::fake()` assertions.

**Commit (nanti):** `feat(orders): queue confirmation and payment-received emails`

---

## T6 — Kesiapan deploy (P2, checklist — tanpa ubah kode)

Belum diverifikasi, jangan diklaim selesai:

- [ ] CA bundle untuk cURL: `C:\php8.4\php.ini` masih punya `curl.cainfo` dan `openssl.cafile` kosong → error
      `cURL error 60` bisa muncul pada request keluar. Pasang CA tepercaya; **jangan** matikan verifikasi TLS.
- [ ] `php artisan migrate --force` di server hanya setelah backup dan setelah target DB dipastikan.
- [ ] `php artisan queue:work` (supervisor) — hanya relevan kalau T5 dikerjakan.
- [ ] Scheduler (`schedule:work` via cron) — hanya relevan kalau OPEN-2 diambil.
- [ ] Midtrans: Server/Client key Production + `MIDTRANS_IS_PRODUCTION=true` + `MIDTRANS_SNAP_URL` &
      `MIDTRANS_SNAP_JS_URL` Production **berpasangan**; Payment Notification URL publik HTTPS
      `.../api/payments/midtrans/notification`.
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, config cache (`php artisan config:cache`).

---

## Open notes (keputusan produk, jangan ditebak)

- **OPEN-1 — Settlement setelah expired.** *(Keputusan sementara yang dipakai T1: opsi **a** — settlement diabaikan,
  stok tetap sudah dilepas, admin merekonsiliasi manual.)* Dengan T1, order yang stoknya sudah dilepas tidak akan pernah otomatis
  menjadi `paid`. Kalau pelanggan tetap membayar (Midtrans telat kirim `settlement`), uang masuk tanpa order lunas.
  Pilihan: (a) abaikan + rekonsiliasi manual oleh admin, (b) izinkan `paid` tapi tandai `needs_restock_review`,
  (c) cegah pembayaran setelah expired. **Butuh keputusan user.**
- **OPEN-2 — Sweep order menggantung.** Webhook bisa tidak pernah datang (server mati, URL salah). Tanpa scheduler,
  order `pending` menahan stok selamanya. Perlu command terjadwal `orders:expire-stale` + cron.
- **OPEN-3 — Halaman pembayaran order tamu lama.** `checkout.payment` tetap tanpa `auth` demi order tamu historis
  (`CheckoutController.php:69` memakai ownership check). Tetapkan tanggal kapan akses tamu ini ditutup.
- **OPEN-4 — Duplikat baris cart lama.** T2 hanya mencegah duplikat baru. Kalau sudah ada baris duplikat di DB
  produksi, perlu skrip dedupe sekali jalan (belum dibuat, sengaja menunggu keputusan).

---

## Urutan eksekusi

```
T1 (stok)  →  T2 (cart merge)  →  T3 (throttle)  →  T4 (commit staging)  →  T5/T6 (opsional, butuh keputusan)
```

Gate penutup setelah semua task:
`php artisan test && ./vendor/bin/pint --test && npm run build && git diff --check`

Jangan push. Laporkan hasil per task beserta output tes yang sebenarnya.
