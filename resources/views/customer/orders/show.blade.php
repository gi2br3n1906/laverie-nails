@php($fingerLabels = ['jempol' => 'Jempol', 'telunjuk' => 'Telunjuk', 'tengah' => 'Tengah', 'manis' => 'Manis', 'kelingking' => 'Kelingking'])

<x-layouts.app :title="'Detail '.$order->id">
    <a class="inline-flex items-center gap-2 text-sm font-semibold text-stone-600 hover:text-[#0C1C39]" href="{{ route('dashboard') }}">← Kembali ke portal</a>
    <section class="mt-6 rounded-[2rem] bg-[#0C1C39] p-7 text-white sm:p-10">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#B9C8DB]">Detail pesanan</p>
        <h1 class="mt-3 break-all font-mono text-xl font-bold sm:text-2xl">{{ $order->id }}</h1>
        <div class="mt-6 grid gap-4 text-sm sm:grid-cols-3">
            <p><span class="block text-[#B9C8DB]">Tanggal</span>{{ $order->created_at->translatedFormat('d M Y, H.i') }}</p>
            <p><span class="block text-[#B9C8DB]">Pembayaran</span><span class="capitalize">{{ $order->payment_status->value }}</span></p>
            <p><span class="block text-[#B9C8DB]">Pengiriman</span><span class="capitalize">{{ $order->fulfillment_status->value }}</span></p>
        </div>
    </section>
    <section class="mt-8 rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 sm:p-8" aria-labelledby="items-heading">
        <h2 class="font-display text-3xl text-[#0C1C39]" id="items-heading">Item yang Dibeli</h2>
        <div class="mt-6 space-y-4">
            @foreach ($order->items as $item)
                <article class="rounded-2xl border border-[#92A1B5]/40 bg-[#F8FAFC] p-5">
                    <div class="flex flex-wrap justify-between gap-3">
                        <div><h3 class="font-semibold text-[#0C1C39]">{{ $item->product_name }}</h3><p class="mt-1 text-sm text-stone-500">{{ $item->quantity }} × Rp {{ number_format($item->product_price, 0, ',', '.') }}</p></div>
                        <p class="font-bold">Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }}</p>
                    </div>
                    @if ($item->size_type->value === 'standard')
                        <p class="mt-4 text-sm"><span class="font-semibold">Ukuran:</span> Standard · {{ data_get($item->size_payload, 'size') }}</p>
                    @else
                        <div class="mt-4 grid gap-3 text-xs sm:grid-cols-2">
                            @foreach (['right_hand' => 'Tangan Kanan', 'left_hand' => 'Tangan Kiri'] as $hand => $label)
                                <div class="rounded-xl bg-white p-4">
                                    <p class="font-semibold text-[#0C1C39]">{{ $label }}</p>
                                    <p class="mt-2 leading-6 text-stone-600">@foreach ($fingerLabels as $finger => $fingerLabel){{ $fingerLabel }} {{ data_get($item->size_payload, "{$hand}.{$finger}") }} mm{{ $loop->last ? '' : ' · ' }}@endforeach</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        <dl class="ml-auto mt-8 max-w-sm space-y-3 border-t border-[#92A1B5]/40 pt-5 text-sm">
            <div class="flex justify-between"><dt>Subtotal</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between"><dt>Ongkir</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div>
            <div class="flex justify-between text-base font-bold text-[#0C1C39]"><dt>Total</dt><dd>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</dd></div>
        </dl>
    </section>
</x-layouts.app>