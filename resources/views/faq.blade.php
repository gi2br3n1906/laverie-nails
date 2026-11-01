<x-layouts.storefront :title="__('faq.title')">
    @php
        $whatsappNumber = (string) config('laverie.contact.whatsapp_number');
        $whatsappUrl = filled($whatsappNumber) && $whatsappNumber !== '6280000000000'
            ? 'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode(__('faq.contact_message'))
            : null;
    @endphp

    <x-storefront.landscape-hero :heading="__('faq.heading')" />

    <section class="bg-white px-4 py-14 sm:px-6 sm:py-20" aria-label="{{ __('faq.title') }}">
        <div class="mx-auto max-w-3xl font-open-sans">
            <div class="space-y-3" data-faq-list>
                @foreach (__('faq.items') as $item)
                    <details class="group overflow-hidden rounded-2xl bg-[#F1F4F8] transition-colors open:bg-[#EAF0F6]" data-faq-item>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-5 px-5 py-4 text-left text-[0.95rem] font-semibold leading-6 text-[#344761] marker:hidden [&::-webkit-details-marker]:hidden focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0C1C39]/30 sm:px-6 sm:py-5">
                            <span>{{ $item['question'] }}</span>
                            <span class="grid size-6 shrink-0 place-items-center text-xl font-normal leading-none text-[#60738C] transition-transform duration-200 group-open:rotate-45" aria-hidden="true">+</span>
                        </summary>
                        <div class="border-t border-[#DDE3EB] px-5 pb-5 pt-4 text-sm leading-7 text-stone-600 sm:px-6">
                            {{ $item['answer'] }}
                        </div>
                    </details>
                @endforeach
            </div>

            <div class="mt-12 rounded-[2rem] bg-[#0C1C39] px-6 py-9 text-center text-white sm:px-10">
                <h2 class="text-2xl font-semibold leading-tight tracking-[-0.02em] sm:text-3xl">{{ __('faq.contact_heading') }}</h2>
                <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-[#DDE6F0]">{{ __('faq.contact_copy') }}</p>
                @if ($whatsappUrl)
                    <a class="mt-6 inline-flex min-h-11 items-center justify-center rounded-full border border-white/50 px-6 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-white hover:text-[#0C1C39]" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">{{ __('faq.contact_cta') }}</a>
                @endif
            </div>
        </div>
    </section>
</x-layouts.storefront>
