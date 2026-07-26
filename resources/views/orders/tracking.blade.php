<x-layouts.storefront title="Lacak Pesanan">
    <section class="border-b border-[#92A1B5]/25 bg-gradient-to-br from-white via-[#F8FAFC] to-[#EAF0F6]" aria-labelledby="tracking-heading">
        <div class="mx-auto max-w-screen-lg px-5 py-14 text-center sm:px-8 sm:py-20 lg:px-12">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#60738C]">Order journey</p>
            <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] text-[#0C1C39] sm:text-6xl" id="tracking-heading">Lacak Pesanan</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-stone-600">Masukkan Order ID dan email checkout untuk melihat progres pembayaran, pengerjaan, dan pengiriman Anda.</p>
        </div>
    </section>

    <section class="mx-auto max-w-screen-lg px-5 py-10 sm:px-8 sm:py-14 lg:px-12">
        <div class="grid gap-8 lg:grid-cols-[22rem_minmax(0,1fr)] lg:items-start">
            <form class="rounded-[2rem] bg-[#0C1C39] p-6 text-white sm:p-8" action="{{ route('orders.track.store') }}" method="POST">
                @csrf
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#DDE6F0]">Find your order</p>
                <div class="mt-6 space-y-5">
                    <div>
                        <label class="text-sm font-semibold" for="order_id">Order ID</label>
                        <input class="mt-2 block min-h-12 w-full rounded-xl border border-white/30 bg-white px-4 text-sm uppercase text-[#0C1C39] placeholder:text-stone-400" id="order_id" name="order_id" type="text" value="{{ old('order_id', request('order_id')) }}" autocomplete="off" placeholder="ORD-2026..." required>
                        <x-input-error class="mt-2 text-[#DDE6F0]" :messages="$errors->get('order_id')" />
                    </div>
                    <div>
                        <label class="text-sm font-semibold" for="email">Email checkout</label>
                        <input class="mt-2 block min-h-12 w-full rounded-xl border border-white/30 bg-white px-4 text-sm text-[#0C1C39] placeholder:text-stone-400" id="email" name="email" type="email" value="{{ old('email', request('email')) }}" autocomplete="email" placeholder="nama@email.com" required>
                        <x-input-error class="mt-2 text-[#DDE6F0]" :messages="$errors->get('email')" />
                    </div>
                </div>
                <button class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-full bg-white px-6 text-xs font-semibold uppercase tracking-[0.14em] text-[#0C1C39] transition hover:bg-[#EAF0F6]" type="submit">Lacak sekarang</button>
                <p class="mt-4 text-xs leading-5 text-[#DDE6F0]">Data pesanan hanya ditampilkan ketika Order ID dan email cocok.</p>
            </form>

            <div>
                @if ($lookupFailed)
                    <div class="rounded-[2rem] border border-[#92A1B5]/40 bg-[#EAF0F6] p-7 text-[#0C1C39]" role="alert">
                        <h2 class="font-display text-2xl">Pesanan belum ditemukan</h2>
                        <p class="mt-3 text-sm leading-6">Pesanan tidak ditemukan. Periksa kembali Order ID dan email Anda.</p>
                    </div>
                @elseif ($order)
                    <article class="overflow-hidden rounded-[2rem] border border-[#92A1B5]/35 bg-white" aria-labelledby="tracked-order-heading">
                        <div class="bg-[#EAF0F6] p-6 sm:p-8">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#60738C]">Order found</p>
                            <h2 class="mt-2 break-all font-display text-2xl text-[#0C1C39] sm:text-3xl" id="tracked-order-heading">{{ $order->id }}</h2>
                            <div class="mt-5 flex flex-wrap gap-2">
                                <span class="rounded-full bg-white px-4 py-2 text-xs font-bold text-[#0C1C39]">Payment · {{ ucfirst($order->payment_status->value) }}</span>
                                <span class="rounded-full border border-[#92A1B5]/50 bg-white px-4 py-2 text-xs font-bold text-[#60738C]">Fulfillment · {{ ucfirst($order->fulfillment_status->value) }}</span>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8">
                            <div class="space-y-4">
                                @foreach ($order->items as $item)
                                    <div class="flex items-start justify-between gap-4 border-b border-[#92A1B5]/25 pb-4">
                                        <div><p class="font-semibold text-[#0C1C39]">{{ $item->product_name }}</p><p class="mt-1 text-xs text-stone-500">{{ $item->quantity }} set</p></div>
                                        <p class="shrink-0 text-sm font-semibold text-[#0C1C39]">Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <dl class="mt-6 space-y-3 text-sm">
                                <div class="flex justify-between gap-4 text-stone-500"><dt>Subtotal</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                                <div class="flex justify-between gap-4 text-stone-500"><dt>Shipping</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                                <div class="flex justify-between gap-4 border-t border-[#92A1B5]/30 pt-4 font-semibold text-[#0C1C39]"><dt>Grand Total</dt><dd>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</dd></div>
                            </dl>

                            @if ($order->tracking_number)
                                <div class="mt-7 rounded-2xl bg-[#0C1C39] p-5 text-white">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#DDE6F0]">Nomor resi</p>
                                    <p class="mt-2 break-all font-display text-2xl">{{ $order->tracking_number }}</p>
                                    <p class="mt-2 text-xs text-[#DDE6F0]">Kurir {{ strtoupper(str_replace(':', ' · ', $order->courier)) }}</p>
                                </div>
                            @else
                                <p class="mt-7 rounded-2xl bg-[#F8FAFC] px-5 py-4 text-sm leading-6 text-stone-500">Nomor resi akan tersedia setelah pesanan diserahkan kepada kurir.</p>
                            @endif
                        </div>
                    </article>
                @else
                    <div class="rounded-[2rem] border border-dashed border-[#92A1B5]/60 bg-white px-7 py-14 text-center">
                        <div class="mx-auto grid size-16 place-items-center rounded-full bg-[#EAF0F6] text-[#0C1C39]">
                            <svg class="size-7" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h10.5v10.5H3.75zM14.25 8.25h3l3 3v4.5h-6zM7.5 18.75a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5ZM17.25 18.75a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" /></svg>
                        </div>
                        <h2 class="mt-5 font-display text-2xl text-[#0C1C39]">Perjalanan pesanan Anda</h2>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-stone-500">Status terbaru dan nomor resi akan tampil di sini setelah detail pesanan diverifikasi.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.storefront>