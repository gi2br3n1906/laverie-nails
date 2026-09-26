# PLAN: Sizing Conversion, Catalog Categories, Length Variants, Cart Drawer Upgrades

Repo: Laverie (`laverie-nails`), branch `main`, baseline 184 tests green @ 18b1861.

Keputusan desain (dikonfirmasi user 2026-09-26):
1. Seeder size standard **ditulis ulang** ke angka plan (XS idx=9, M idx=11.5, pinky 8/8/9.5/10) — test `SizeStandardSeederTest` harus diupdate.
2. `cart_items.length` = **kolom string terpisah** (migration baru), bukan di size_payload.
3. Kupon **minimal**: kode + persen, validasi AJAX, diskon tampil di subtotal drawer (belum masuk Midtrans checkout).

## Task 1 — NailSizeConverter + tip numbers di hasil sizing
- [ ] Test: `tests/Unit/Services/NailSizeConverterTest.php` (mapping penuh 18mm→0 … 8mm→14, tie → nomor lebih kecil, clamp di luar range)
- [ ] `app/Services/NailSizeConverter.php` — `toTipNumber(float $mm): int`, lookup array berurutan
- [ ] Suntik ke hasil sizing: cari view yang merender `components/hand-result-card.blade.php`, tampilkan tip number per jari menggantikan mm mentah
- [ ] Feature test hasil sizing menampilkan tip number
- Commit: `feat(sizing): convert mm measurements to tip numbers on result page`

## Task 2 — Seeder size standard baru
- [ ] Update `SizeStandardSeederTest` dulu (RED) ke angka: XS(14,9,11,10,8) S(15,11,12,11,8) M(16,11.5,13,12,9.5) L(17,12.5,14,13,10)
- [ ] Update `database/seeders/SizeStandardSeeder.php` (tetap updateOrCreate + idempotent)
- [ ] Jalankan test classifier + seeder
- Commit: `feat(sizing): retune empirical size standards`

## Task 3 — Katalog: kategori baru, hapus heading KATEGORI + pills size
- [ ] Cek `CategoryService`/tabel categories — samakan daftar: All Styles, Classy, Coquette, Y2K, Floral, Grunge, Tools
- [ ] Test: `Marketplace/ProductCatalogTest` — kategori tampil, pills size tidak ada, heading KATEGORI tidak ada
- [ ] Ubah `resources/views/products/index.blade.php`
- Commit: `feat(catalog): restyle category navigation, drop size pills`

## Task 4 — `products.available_lengths` + form admin
- [ ] Migration baru: JSON `available_lengths` default ["Short","Medium","Long"]
- [ ] Model Product cast + helper `availableLengths()`
- [ ] Test `Admin/ProductCrudTest`: create/edit mencentang Length selain Size
- [ ] Update `admin/products/create|edit.blade.php`
- Commit: `feat(products): available length variants`

## Task 5 — `cart_items.length` + badge & checkbox di drawer
- [ ] Migration baru: `cart_items.length` nullable string
- [ ] CartService terima/validasi length; CartItem fillable/casts
- [ ] Test `Cart/CartOperationsTest`: add item dengan length; length invalid ditolak
- [ ] Drawer: badge size + length per item, checkbox per baris (is_selected? — ikuti pola update endpoint yang ada; bila belum ada kolom seleksi, tambahkan `is_selected` boolean di migration yang sama)
- Commit: `feat(cart): length variant on items, badges and row checkboxes in drawer`

## Task 6 — Discount Code (kupon minimal) di drawer
- [ ] Migration: tabel `coupons` (code unique, discount_percentage, is_active)
- [ ] Endpoint AJAX apply coupon (session `cart_coupon`), payload drawer menyertakan discount + total
- [ ] Test feature: apply valid/invalid coupon; subtotal/discount/total benar
- [ ] Drawer UI: input Discount Code + tombol submit AJAX **di atas** Add Notes
- Commit: `feat(cart): minimal discount code in drawer`

## Task 7 — Verifikasi
- [ ] `php artisan migrate` + `php artisan db:seed --class=SizeStandardSeeder` (DB lokal; backup dulu)
- [ ] `php artisan test` full green
- [ ] `./vendor/bin/pint --dirty`
- [ ] `npm run build`
- [ ] `git diff --check`

Guardrails: jangan sentuh `routes`/flow checkout Midtrans; jangan revert commit repo lain; commit per task dengan pesan di atas; TIDAK push tanpa diminta.
