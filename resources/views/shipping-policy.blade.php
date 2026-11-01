<x-layouts.storefront :title="__('shipping.title')">
    <div class="font-open-sans">
        @php
            $replacements = [
                ':java' => 'Rp '.number_format($shippingRates['java'], 0, ',', '.'),
                ':outside' => 'Rp '.number_format($shippingRates['outside_java'], 0, ',', '.'),
            ];
        @endphp

    <section class="bg-white px-5 pb-6 pt-16 sm:px-8 sm:pt-24">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.22em] text-stone-500">{{ __('shipping.eyebrow') }}</p>
            <h1 class="mt-4 text-5xl font-semibold leading-tight tracking-[-0.04em] text-[#0C1C39] sm:text-6xl">{{ __('shipping.title') }}</h1>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.12em] text-stone-500">{{ __('shipping.updated') }}</p>
            <p class="mx-auto mt-6 max-w-2xl text-sm leading-7 text-stone-600 sm:text-base">{{ __('shipping.intro') }}</p>
        </div>
    </section>

    <section class="bg-white px-5 pb-20 pt-8 sm:px-8" aria-label="{{ __('shipping.title') }}">
        <div class="mx-auto max-w-3xl divide-y divide-[#DDE3EB] border-y border-[#DDE3EB]">
            @foreach (__('shipping.sections') as $section)
                @php
                    $paragraphs = $section['paragraphs'];
                    $items = $section['items'] ?? [];
                @endphp

                <section class="py-8" aria-labelledby="shipping-section-{{ $loop->index }}">
                    <h2 class="flex items-start gap-3 text-xs font-bold uppercase tracking-[0.18em] text-[#0C1C39]" id="shipping-section-{{ $loop->index }}">
                        <span class="text-base leading-none" aria-hidden="true">{{ $section['icon'] }}</span>
                        <span>{{ $section['heading'] }}</span>
                    </h2>

                    <div class="mt-4 space-y-4">
                        <p class="text-sm leading-7 text-stone-600">{{ strtr($paragraphs[0], $replacements) }}</p>

                        @if (filled($items))
                            <ul class="grid gap-3 rounded-2xl bg-[#F1F4F8] px-4 py-4 sm:px-5">
                                @foreach ($items as $item)
                                    <li class="flex gap-3 text-sm leading-6 text-stone-600">
                                        <span class="mt-2 size-1.5 shrink-0 rounded-full bg-[#0C1C39]" aria-hidden="true"></span>
                                        <span><strong class="font-semibold text-[#344761]">{{ $item['label'] }}:</strong> {{ strtr($item['text'], $replacements) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @foreach (array_slice($paragraphs, 1) as $paragraph)
                            <p class="text-sm leading-7 text-stone-600">{{ strtr($paragraph, $replacements) }}</p>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </section>
    </div>
</x-layouts.storefront>
