@php($fieldClass = 'mt-2 block min-h-12 w-full rounded-2xl border border-[#92A1B5]/60 bg-white px-4 py-3 text-sm text-[#0C1C39] outline-none transition placeholder:text-stone-400 focus:border-[#0C1C39] focus:ring-2 focus:ring-[#DDE6F0] disabled:cursor-not-allowed disabled:bg-[#F1F5F9]')

<x-layouts.storefront title="Checkout">
    <section class="border-b border-[#92A1B5]/25 bg-gradient-to-br from-white via-[#F8FAFC] to-[#EAF0F6]" aria-labelledby="checkout-heading">
        <div class="mx-auto max-w-screen-xl px-5 py-12 sm:px-8 sm:py-16 lg:px-12">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#60738C]">Secure checkout</p>
                    <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] text-[#0C1C39] sm:text-6xl" id="checkout-heading">Detail pesanan Anda</h1>
                    <p class="mt-4 max-w-xl text-sm leading-7 text-stone-600">Lengkapi alamat pengiriman, pilih kurir, lalu lanjutkan ke pembayaran aman melalui Midtrans.</p>
                </div>
                <ol class="flex items-center gap-3 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-[#60738C]" aria-label="Tahapan checkout">
                    <li class="rounded-full bg-[#0C1C39] px-4 py-2 text-white">1 · Details</li>
                    <li aria-hidden="true">—</li>
                    <li>2 · Payment</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-screen-xl px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
        @if ($errors->any())
            <div class="mb-8 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm text-[#0C1C39]" role="alert">
                <p class="font-semibold">Mohon periksa kembali detail checkout:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start"
            action="{{ route('checkout.store') }}"
            method="POST"
            data-checkout-form
            data-cities-url="{{ route('checkout.logistics.cities') }}"
            data-shipping-options-url="{{ route('checkout.logistics.shipping-options') }}"
            data-subtotal="{{ $subtotal }}"
        >
            @csrf
            <input name="shipping_option" type="hidden" value="{{ old('shipping_option') }}" data-shipping-option-input>

            <div class="space-y-6">
                <fieldset class="rounded-[2rem] border border-[#92A1B5]/35 bg-white p-5 sm:p-8">
                    <legend class="px-2 font-display text-2xl text-[#0C1C39]">Customer details</legend>
                    <div class="mt-2 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-[#0C1C39]" for="customer_name">Nama lengkap</label>
                            <input class="{{ $fieldClass }}" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', auth()->user()?->name) }}" autocomplete="name" placeholder="Nama penerima" required>
                            <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-[#0C1C39]" for="customer_email">Email</label>
                            <input class="{{ $fieldClass }}" id="customer_email" name="customer_email" type="email" value="{{ old('customer_email', auth()->user()?->email) }}" autocomplete="email" placeholder="nama@email.com" required>
                            <x-input-error class="mt-2" :messages="$errors->get('customer_email')" />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-[#0C1C39]" for="customer_phone">Nomor telepon</label>
                            <input class="{{ $fieldClass }}" id="customer_phone" name="customer_phone" type="tel" value="{{ old('customer_phone', auth()->user()?->phone) }}" autocomplete="tel" placeholder="08xxxxxxxxxx" required>
                            <x-input-error class="mt-2" :messages="$errors->get('customer_phone')" />
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-[2rem] border border-[#92A1B5]/35 bg-white p-5 sm:p-8">
                    <legend class="px-2 font-display text-2xl text-[#0C1C39]">Shipping details</legend>
                    <div class="mt-2 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-[#0C1C39]" for="province_id">Provinsi</label>
                            <select class="{{ $fieldClass }}" id="province_id" name="province_id" data-province-select required>
                                <option value="">Pilih provinsi</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province['id'] }}" @selected(old('province_id', auth()->user()?->province_id) === $province['id'])>{{ $province['name'] }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('province_id')" />
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-[#0C1C39]" for="city_id">Kota / Kabupaten</label>
                            <select class="{{ $fieldClass }}" id="city_id" name="city_id" data-city-select data-old-value="{{ old('city_id', auth()->user()?->city_id) }}" disabled required>
                                <option value="">Pilih provinsi terlebih dahulu</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('city_id')" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold text-[#0C1C39]" for="shipping_address">Alamat lengkap</label>
                            <textarea class="{{ $fieldClass }} min-h-32 resize-y" id="shipping_address" name="shipping_address" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, kecamatan, kode pos, dan patokan" required>{{ old('shipping_address', auth()->user()?->address) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('shipping_address')" />
                        </div>
                    </div>
                </fieldset>

                <fieldset class="rounded-[2rem] border border-[#92A1B5]/35 bg-white p-5 sm:p-8">
                    <legend class="px-2 font-display text-2xl text-[#0C1C39]">Delivery service</legend>
                    <p class="mt-2 text-sm leading-6 text-stone-500">Pilihan ongkir akan muncul setelah kota tujuan dipilih. Nominal akan diverifikasi kembali oleh server saat pesanan dibuat.</p>
                    <div class="mt-5 grid gap-3" data-shipping-options role="radiogroup" aria-label="Pilihan layanan pengiriman">
                        <p class="rounded-2xl border border-dashed border-[#92A1B5]/60 px-5 py-6 text-sm text-stone-500" data-shipping-placeholder>Pilih kota tujuan untuk melihat ongkos kirim.</p>
                    </div>
                    <x-input-error class="mt-3" :messages="$errors->get('shipping_option')" />
                </fieldset>
            </div>

            <aside class="rounded-[2rem] bg-[#0C1C39] p-6 text-white lg:sticky lg:top-28" aria-label="Ringkasan checkout">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#DDE6F0]">Order summary</p>
                <div class="mt-6 space-y-4">
                    @foreach ($items as $item)
                        <div class="flex items-start justify-between gap-4 border-b border-white/15 pb-4">
                            <div>
                                <p class="text-sm font-semibold">{{ $item->product->name }}</p>
                                <p class="mt-1 text-xs text-[#DDE6F0]">{{ $item->quantity }} × Rp {{ number_format((float) $item->product->price, 0, ',', '.') }} · {{ $item->size_type->value === 'standard' ? 'Size '.$item->size_payload['size'] : 'Custom size' }}</p>
                            </div>
                            <p class="shrink-0 text-sm">Rp {{ number_format($item->subtotalInCents() / 100, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between gap-4 text-[#DDE6F0]">
                        <dt>Subtotal</dt>
                        <dd>Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 text-[#DDE6F0]">
                        <dt>Shipping</dt>
                        <dd data-shipping-total>Belum dipilih</dd>
                    </div>
                    <div class="flex items-end justify-between gap-4 border-t border-white/20 pt-5">
                        <dt class="font-display text-2xl">Grand Total</dt>
                        <dd class="text-lg font-semibold" data-grand-total>Rp {{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <button class="mt-7 inline-flex min-h-12 w-full items-center justify-center rounded-full bg-white px-6 text-xs font-semibold uppercase tracking-[0.14em] text-[#0C1C39] transition hover:bg-[#EAF0F6] disabled:cursor-not-allowed disabled:opacity-60" type="submit" data-place-order-button>
                    Place order
                </button>
                <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-[#DDE6F0]">
                    <svg class="mt-0.5 size-4 shrink-0" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect width="14" height="11" x="5" y="10" rx="2" /><path stroke-linecap="round" d="M8.5 10V7.5a3.5 3.5 0 0 1 7 0V10" /></svg>
                    Pembayaran diproses melalui halaman aman Midtrans.
                </p>
            </aside>
        </form>
    </section>
</x-layouts.storefront>