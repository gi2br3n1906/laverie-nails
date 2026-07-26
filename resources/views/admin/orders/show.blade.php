@php
    $fingerLabels = ['jempol' => 'Jempol', 'telunjuk' => 'Telunjuk', 'tengah' => 'Tengah', 'manis' => 'Manis', 'kelingking' => 'Kelingking'];
    $handLabels = ['right_hand' => 'Tangan kanan', 'left_hand' => 'Tangan kiri'];
    [$courierCode, $courierService] = array_pad(explode(':', $order->courier, 2), 2, '');
    $measurement = static fn (mixed $value): string => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
@endphp

<x-layouts.app title="Detail Pesanan {{ $order->id }}">
    <section aria-labelledby="order-heading">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a class="text-xs font-bold uppercase tracking-[0.14em] text-[#60738C]" href="{{ route('admin.orders.index') }}">← Kembali ke pesanan</a>
                <h1 class="mt-4 break-all font-serif text-3xl font-semibold text-[#0C1C39] sm:text-5xl" id="order-heading">{{ $order->id }}</h1>
                <p class="mt-3 text-sm text-stone-500">Dibuat {{ $order->created_at?->format('d M Y, H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-[#EAF0F6] px-4 py-2 text-xs font-bold uppercase tracking-[0.1em] text-[#0C1C39]">Payment · {{ $order->payment_status->value }}</span>
                <span class="rounded-full border border-[#92A1B5]/50 px-4 py-2 text-xs font-bold uppercase tracking-[0.1em] text-[#60738C]">Fulfillment · {{ $order->fulfillment_status->value }}</span>
            </div>
        </div>

        @if (session('status'))
            <div class="mt-7 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm font-semibold text-[#0C1C39]" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-7 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm text-[#0C1C39]" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <div class="mt-9 grid gap-7 xl:grid-cols-[minmax(0,1fr)_22rem] xl:items-start">
            <div class="space-y-7">
                <div class="grid gap-5 md:grid-cols-2">
                    <article class="rounded-3xl border border-[#92A1B5]/35 bg-white p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#60738C]">Customer details</p>
                        <p class="mt-4 text-lg font-semibold text-[#0C1C39]">{{ $order->customer_name }}</p>
                        <p class="mt-2 text-sm text-stone-600">{{ $order->customer_email }}</p>
                        <p class="mt-1 text-sm text-stone-600">{{ $order->customer_phone }}</p>
                    </article>
                    <article class="rounded-3xl border border-[#92A1B5]/35 bg-white p-6">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#60738C]">Shipping details</p>
                        <p class="mt-4 font-semibold text-[#0C1C39]">{{ strtoupper($courierCode) }} · {{ $courierService }}</p>
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $order->shipping_address }}</p>
                        <p class="mt-2 text-xs text-stone-400">Province {{ $order->province_id }} · City {{ $order->city_id }}</p>
                    </article>
                </div>

                <section class="rounded-3xl border border-[#92A1B5]/35 bg-white p-6 sm:p-8" aria-labelledby="crafting-heading">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div><p class="text-xs font-bold uppercase tracking-[0.14em] text-[#60738C]">Crafting brief</p><h2 class="mt-2 font-serif text-3xl text-[#0C1C39]" id="crafting-heading">Item & ukuran kuku</h2></div>
                        <p class="text-xs text-stone-500">{{ $order->items->sum('quantity') }} set</p>
                    </div>

                    <div class="mt-7 space-y-6">
                        @foreach ($order->items as $item)
                            <article class="rounded-2xl bg-[#F8FAFC] p-5">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div><h3 class="font-semibold text-[#0C1C39]">{{ $item->product_name }}</h3><p class="mt-1 text-xs text-stone-500">{{ $item->quantity }} × Rp {{ number_format($item->product_price, 0, ',', '.') }}</p></div>
                                    <p class="font-semibold text-[#0C1C39]">Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }}</p>
                                </div>

                                @if ($item->size_type === \App\Enums\CartSizeType::Standard)
                                    <div class="mt-4 rounded-xl border border-[#92A1B5]/40 bg-white px-4 py-3 text-sm font-semibold text-[#0C1C39]">Standard · {{ $item->size_payload['size'] }}</div>
                                @else
                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        @foreach ($handLabels as $hand => $handLabel)
                                            <div class="rounded-xl border border-[#92A1B5]/40 bg-white p-4">
                                                <p class="text-xs font-bold uppercase tracking-[0.1em] text-[#60738C]">{{ $handLabel }}</p>
                                                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                                    @foreach ($fingerLabels as $finger => $fingerLabel)
                                                        <div class="contents"><dt class="text-stone-500">{{ $fingerLabel }}</dt><dd class="text-right font-semibold text-[#0C1C39]">{{ $measurement($item->size_payload[$hand][$finger]) }} mm</dd></div>
                                                    @endforeach
                                                </dl>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="rounded-3xl bg-[#0C1C39] p-6 text-white xl:sticky xl:top-28" aria-label="Update fulfillment">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-[#DDE6F0]">Fulfillment action</p>
                <h2 class="mt-2 font-serif text-2xl">Update pesanan</h2>
                <form class="mt-6 space-y-5" action="{{ route('admin.orders.update', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="text-sm font-semibold" for="fulfillment_status">Fulfillment status</label>
                        <select class="mt-2 block min-h-12 w-full rounded-xl border border-white/30 bg-white px-4 text-sm text-[#0C1C39]" id="fulfillment_status" name="fulfillment_status" required>
                            @foreach ($fulfillmentStatuses as $status)
                                <option value="{{ $status->value }}" @selected(old('fulfillment_status', $order->fulfillment_status->value) === $status->value)>{{ ucfirst($status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold" for="tracking_number">Nomor resi</label>
                        <input class="mt-2 block min-h-12 w-full rounded-xl border border-white/30 bg-white px-4 text-sm text-[#0C1C39] placeholder:text-stone-400" id="tracking_number" name="tracking_number" type="text" value="{{ old('tracking_number', $order->tracking_number) }}" maxlength="100" placeholder="Contoh: JNE-123456789">
                        <p class="mt-2 text-xs leading-5 text-[#DDE6F0]">Wajib diisi ketika status diubah menjadi shipped.</p>
                    </div>
                    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-white px-6 text-xs font-bold uppercase tracking-[0.12em] text-[#0C1C39] transition hover:bg-[#EAF0F6]" type="submit">Simpan perubahan</button>
                </form>

                <dl class="mt-7 space-y-3 border-t border-white/20 pt-6 text-sm">
                    <div class="flex justify-between gap-4 text-[#DDE6F0]"><dt>Subtotal</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4 text-[#DDE6F0]"><dt>Shipping</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4 border-t border-white/20 pt-4 font-semibold"><dt>Grand Total</dt><dd>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</dd></div>
                </dl>
            </aside>
        </div>
    </section>
</x-layouts.app>