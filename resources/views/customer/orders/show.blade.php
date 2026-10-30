@php($fingerLabels = ['jempol' => __('ui.common.finger_thumb'), 'telunjuk' => __('ui.common.finger_index'), 'tengah' => __('ui.common.finger_middle'), 'manis' => __('ui.common.finger_ring'), 'kelingking' => __('ui.common.finger_pinky')])

<x-layouts.app :title="$order->id">
    <a class="inline-flex items-center gap-2 text-sm font-semibold text-stone-600 hover:text-[#0C1C39]" href="{{ route('dashboard') }}">{{ __('ui.dashboard.back_portal') }}</a>
    <section class="mt-6 rounded-[2rem] bg-[#0C1C39] p-7 text-white sm:p-10">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#B9C8DB]">{{ __('ui.dashboard.order_detail') }}</p>
        <h1 class="mt-3 break-all font-mono text-xl font-bold sm:text-2xl">{{ $order->id }}</h1>
        <div class="mt-6 grid gap-4 text-sm sm:grid-cols-3">
            <p><span class="block text-[#B9C8DB]">{{ __('ui.dashboard.date') }}</span>{{ $order->created_at->translatedFormat('d M Y, H.i') }}</p>
            <p><span class="block text-[#B9C8DB]">{{ __('ui.dashboard.payment') }}</span><span>{{ $order->payment_status->label() }}</span></p>
            <p><span class="block text-[#B9C8DB]">{{ __('ui.dashboard.fulfillment') }}</span><span>{{ $order->fulfillment_status->label() }}</span></p>
        </div>
    </section>
    <section class="mt-8 rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 sm:p-8" aria-labelledby="items-heading">
        <h2 class="font-display text-3xl text-[#0C1C39]" id="items-heading">{{ __('ui.dashboard.items') }}</h2>
        <div class="mt-6 space-y-4">
            @foreach ($order->items as $item)
                <article class="rounded-2xl border border-[#92A1B5]/40 bg-[#F8FAFC] p-5">
                    <div class="flex flex-wrap justify-between gap-3">
                        <div><h3 class="font-semibold text-[#0C1C39]">{{ $item->product_name }}</h3><p class="mt-1 text-sm text-stone-500">{{ $item->quantity }} × Rp {{ number_format($item->product_price, 0, ',', '.') }}</p></div>
                        <p class="font-bold">Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }}</p>
                    </div>
                    @if ($item->size_type->value === 'standard')
                        <p class="mt-4 text-sm"><span class="font-semibold">{{ __('ui.dashboard.size') }}:</span> {{ __('ui.product.standard_size') }} · {{ data_get($item->size_payload, 'size') }}</p>
                    @else
                        <div class="mt-4 grid gap-3 text-xs sm:grid-cols-2">
                            @foreach (['right_hand' => __('ui.measurements.right_hand'), 'left_hand' => __('ui.measurements.left_hand')] as $hand => $label)
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
            <div class="flex justify-between"><dt>{{ __('ui.checkout.subtotal') }}</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between"><dt>{{ __('ui.checkout.shipping_label') }}</dt><dd>{{ $order->shipping_cost > 0 ? 'Rp '.number_format($order->shipping_cost, 0, ',', '.') : ($order->shipping_zone ? __('ui.tracking.free') : __('ui.tracking.pay_on_delivery')) }}</dd></div>
                        <div class="flex justify-between text-base font-bold text-[#0C1C39]"><dt>{{ __('ui.checkout.total') }}</dt><dd>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</dd></div>
        </dl>
    </section>
    <section class="mt-6 rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 sm:p-8" aria-labelledby="shipping-heading">
        <h2 class="font-display text-3xl text-[#0C1C39]" id="shipping-heading">{{ __('ui.dashboard.shipping') }}</h2>
        <p class="mt-4 text-sm leading-6 text-stone-600">{{ $order->fullShippingAddress() }}</p>
        <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-stone-500">{{ __('ui.dashboard.courier') }}</dt><dd class="mt-1 font-semibold text-[#0C1C39]">{{ $order->courier ? strtoupper(str_replace(':', ' · ', $order->courier)) : __('ui.dashboard.courier_not_set') }}</dd></div>
                        <div><dt class="text-stone-500">{{ __('ui.dashboard.tracking_number') }}</dt><dd class="mt-1 break-all font-semibold text-[#0C1C39]">{{ $order->tracking_number ?: __('ui.dashboard.no_tracking') }}</dd></div>
        </dl>
        <p class="mt-4 text-xs leading-6 text-stone-500">{{ __('ui.dashboard.tracking_note') }}@if (! $order->shipping_zone && $order->shipping_cost === 0) {{ __('ui.payment.legacy_note') }}@endif</p>
    </section>
</x-layouts.app>