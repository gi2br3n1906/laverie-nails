<x-layouts.storefront :title="__('ui.payment.title')">
    <section class="mx-auto max-w-screen-lg px-5 py-12 sm:px-8 sm:py-16 lg:px-12" aria-labelledby="payment-heading">
        <div class="overflow-hidden rounded-[2.25rem] border border-[#92A1B5]/35 bg-white shadow-[0_24px_70px_rgba(12,28,57,0.08)]">
            <div class="bg-[#0C1C39] px-6 py-10 text-white sm:px-10 sm:py-12">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#DDE6F0]">{{ __('ui.payment.confirmed') }}</p>
                        <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] sm:text-5xl" id="payment-heading">{{ $order->payment_status === \App\Enums\PaymentStatus::Paid ? __('ui.payment.received_heading') : __('ui.payment.heading') }}</h1>
                        <p class="mt-4 max-w-xl text-sm leading-7 text-[#DDE6F0]">{{ __('ui.payment.intro') }}</p>
                    </div>
                    <span class="w-fit rounded-full border border-white/25 px-4 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-[#DDE6F0]">{{ $order->payment_status->label() }}</span>
                </div>
            </div>

            <div class="grid gap-8 p-6 sm:p-10 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#60738C]">{{ __('ui.payment.order_number') }}</p>
                    <p class="mt-2 break-all font-display text-2xl text-[#0C1C39]">{{ $order->id }}</p>

                    <div class="mt-8 space-y-4">
                        @foreach ($order->items as $item)
                            <div class="flex items-start justify-between gap-4 border-b border-[#92A1B5]/30 pb-4">
                                <div>
                                    <p class="font-semibold text-[#0C1C39]">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-xs text-stone-500">{{ $item->quantity }} {{ __('ui.payment.sets') }} · {{ $item->size_type->value === 'standard' ? __('ui.checkout.size').' '.$item->size_payload['size'] : __('ui.checkout.custom_size') }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-[#0C1C39]">Rp {{ number_format($item->product_price * $item->quantity, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8 rounded-2xl bg-[#EAF0F6] p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-[#60738C]">{{ __('ui.payment.shipped_to') }}</p>
                        <p class="mt-2 font-semibold text-[#0C1C39]">{{ $order->customer_name }}</p>
                        <p class="mt-1 text-sm leading-6 text-stone-600">{{ $order->fullShippingAddress() }}</p>
                    </div>
                </div>

                <aside class="rounded-[1.75rem] border border-[#92A1B5]/40 bg-[#F8FAFC] p-6" aria-label="{{ __('ui.payment.total') }}">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4 text-stone-500">
                            <dt>{{ __('ui.checkout.subtotal') }}</dt>
                            <dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd>
                        </div>
                        @if ($order->shipping_cost > 0)
                            <div class="flex justify-between gap-4 text-stone-500">
                                <dt>{{ __('ui.checkout.shipping_label') }}</dt>
                                <dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        @if ($order->discount_amount > 0)
                            <div class="flex justify-between gap-4 text-stone-500">
                                <dt>{{ __('ui.checkout.discount') }} ({{ $order->coupon_code }})</dt>
                                <dd>−Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        <div class="border-t border-[#92A1B5]/40 pt-4">
                            <dt class="font-display text-xl text-[#0C1C39]">{{ __('ui.checkout.total') }}</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#0C1C39]">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</dd>
                        </div>
                    </dl>
                    @if (! $order->shipping_zone && $order->shipping_cost === 0)
                        <p class="mt-3 text-xs leading-5 text-stone-500">{{ __('ui.payment.legacy_note') }}</p>
                    @endif

                    @if ($order->payment_status === \App\Enums\PaymentStatus::Pending && $order->stock_restored_at === null)
                        <div
                            class="mt-6"
                            data-payment-snap
                            data-snap-token="{{ $order->snap_token }}"
                            data-snap-url="{{ $snapJsUrl }}"
                            data-client-key="{{ $midtransClientKey }}"
                            data-payment-status-url="{{ $paymentStatusUrl }}"
                            data-payment-check="{{ request()->boolean('check') ? '1' : '0' }}"
                        >
                            <button class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-[#0C1C39] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48] disabled:cursor-not-allowed disabled:opacity-60" type="button" data-pay-button>
                                {{ __('ui.payment.pay_now') }}
                            </button>
                            <p class="mt-3 text-center text-xs leading-5 text-stone-500" data-payment-status>{{ __('ui.payment.snap_note') }}</p>
                        </div>
                    @else
                        <div class="mt-6 rounded-2xl border border-[#92A1B5]/40 bg-white p-5 text-sm leading-6 text-stone-600" data-payment-unavailable>
                            @if ($order->payment_status === \App\Enums\PaymentStatus::Paid)
                                <p class="font-semibold text-[#0C1C39]">{{ __('ui.payment.received') }}</p>
                            @else
                                <p class="font-semibold text-[#0C1C39]">{{ __('ui.payment.unavailable') }}</p>
                            @endif
                            <p class="mt-2">{{ __('ui.payment.status', ['status' => $order->payment_status->label()]) }}@if ($order->stock_restored_at !== null) {{ __('ui.payment.stock_restored') }}@endif</p>
                                                        <a class="mt-3 inline-flex font-semibold text-[#0C1C39] underline underline-offset-4" href="{{ route('products.index') }}">{{ __('ui.payment.view_collection') }}</a>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>
</x-layouts.storefront>