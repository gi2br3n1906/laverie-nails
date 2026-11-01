<footer class="border-t border-[#92A1B5]/40 bg-white text-[#0C1C39]">
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 lg:px-12" data-global-footer-layout>
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12" data-footer-top-row>
            <div data-footer-section="shop">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">{{ __('ui.footer.shop') }}</p>
                <div class="mt-5 grid gap-3 text-sm text-stone-600">
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('home') }}#pretty-picks">{{ __('ui.footer.pretty_picks') }}</a>
                    @foreach ([['classy', 'classy'], ['coquette', 'coquette'], ['floral', 'floral'], ['grunge', 'grunge'], ['y2k', 'y2k']] as [$key, $slug])
                        @php($categorySlug = in_array($slug, $footerCategorySlugs, true) ? $slug : 'demo-'.$slug)
                        <a class="transition hover:text-[#0C1C39]" href="{{ route('products.index', ['category' => $categorySlug]) }}">{{ __('ui.footer.'.$key) }}</a>
                    @endforeach
                </div>
            </div>
            <div data-footer-section="brand">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">{{ __('ui.footer.brand') }}</p>
                <div class="mt-5 grid gap-3 text-sm text-stone-600">
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('about') }}">{{ __('ui.footer.about') }}</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('faq') }}">{{ __('ui.footer.faq') }}</a>
                </div>
            </div>
            <div data-footer-section="help-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">{{ __('ui.footer.help_center') }}</p>
                <div class="mt-5 grid gap-3 text-sm text-stone-600">
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('guidance') }}">{{ __('ui.footer.measurement_guide') }}</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('measurements.create') }}">{{ __('ui.footer.sizing') }}</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('shipping.policy') }}">{{ __('ui.footer.shipping_policy') }}</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('privacy') }}">{{ __('ui.footer.privacy') }}</a>
                </div>
            </div>
            <div data-footer-section="newsletter">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">{{ __('ui.footer.newsletter') }}</p>
                <p class="mt-5 max-w-md text-sm leading-6 text-stone-600">{{ __('ui.footer.newsletter_copy') }}</p>
                <form class="mt-5 max-w-md" action="{{ route('register') }}" method="GET">
                    <label class="sr-only" for="footer-newsletter-email">{{ __('ui.footer.email') }}</label>
                    <div class="flex rounded-full border border-[#92A1B5] bg-[#F8FAFC] p-1.5 focus-within:border-[#0C1C39] focus-within:ring-4 focus-within:ring-[#92A1B5]/20">
                        <input class="min-w-0 flex-1 bg-transparent px-4 py-2 text-sm text-[#0C1C39] outline-none placeholder:text-[#92A1B5]" id="footer-newsletter-email" name="email" type="email" placeholder="{{ __('ui.footer.sign_up') }}" autocomplete="email">
                        <button class="grid size-10 shrink-0 place-items-center rounded-full bg-[#0C1C39] text-lg text-white transition hover:bg-[#192B48]" type="submit" aria-label="{{ __('ui.footer.newsletter_aria') }}">&gt;</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-12 pt-2 text-center" data-footer-bottom-row>
            <div class="flex items-center justify-center gap-3">
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[#0C1C39] transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="{{ config('laverie.contact.whatsapp_number') ? 'https://wa.me/'.config('laverie.contact.whatsapp_number') : '#' }}" target="{{ config('laverie.contact.whatsapp_number') ? '_blank' : '_self' }}" rel="noopener noreferrer" aria-label="WhatsApp">
                    <svg class="size-[18px]" data-social-icon="whatsapp" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>
                </a>
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[#0C1C39] transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="{{ config('laverie.contact.instagram_url') }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <svg class="size-[18px]" data-social-icon="instagram" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
                    </svg>
                </a>
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[#0C1C39] transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="{{ config('laverie.contact.tiktok_url') }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                    <svg class="size-[18px]" data-social-icon="tiktok" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M9 0h1.98c.144.715.54 1.617 1.235 2.512C12.895 3.389 13.797 4 15 4v2c-1.753 0-3.07-.814-4-1.829V11a5 5 0 1 1-5-5v2a3 3 0 1 0 3 3z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
    <div class="px-5 py-5 text-center text-xs text-stone-500">© {{ date('Y') }} Laverie Nails. {{ __('ui.footer.copyright') }}</div>
</footer>