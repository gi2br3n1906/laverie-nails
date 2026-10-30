<footer class="border-t border-[#92A1B5]/40 bg-white text-[#0C1C39]">
    <div class="mx-auto max-w-5xl px-5 py-14 text-center sm:px-8 lg:px-12" data-global-footer-layout>
        <div class="grid justify-center gap-10 md:grid-cols-2 md:gap-16" data-footer-top-row>
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">{{ __('ui.footer.customer_service') }}</p>
                <div class="mt-5 grid gap-3 text-sm text-stone-600">
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('measurements.create') }}">{{ __('ui.footer.sizing') }}</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('guidance') }}">{{ __('ui.footer.measurement_guide') }}</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('privacy') }}">{{ __('ui.footer.privacy') }}</a>
                </div>
            </div>
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">{{ __('ui.footer.newsletter') }}</p>
                <p class="mx-auto mt-5 max-w-md text-sm leading-6 text-stone-600">{{ __('ui.footer.newsletter_copy') }}</p>
                <form class="mx-auto mt-5 max-w-md" action="{{ route('register') }}" method="GET">
                    <label class="sr-only" for="footer-newsletter-email">{{ __('ui.footer.email') }}</label>
                    <div class="flex rounded-full border border-[#92A1B5] bg-[#F8FAFC] p-1.5 focus-within:border-[#0C1C39] focus-within:ring-4 focus-within:ring-[#92A1B5]/20">
                        <input class="min-w-0 flex-1 bg-transparent px-4 py-2 text-sm text-[#0C1C39] outline-none placeholder:text-[#92A1B5]" id="footer-newsletter-email" name="email" type="email" placeholder="{{ __('ui.footer.sign_up') }}" autocomplete="email">
                        <button class="grid size-10 shrink-0 place-items-center rounded-full bg-[#0C1C39] text-lg text-white transition hover:bg-[#192B48]" type="submit" aria-label="Sign up for the newsletter">&gt;</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-12 pt-2 text-center" data-footer-bottom-row>
            <p class="font-logo text-3xl text-[#0C1C39]">Laverie Nails</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[0.65rem] font-bold transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="{{ config('laverie.contact.whatsapp_number') ? 'https://wa.me/'.config('laverie.contact.whatsapp_number') : '#' }}" target="{{ config('laverie.contact.whatsapp_number') ? '_blank' : '_self' }}" rel="noopener noreferrer" aria-label="WhatsApp">WA</a>
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[0.65rem] font-bold transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="{{ config('laverie.contact.instagram_url') }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">IG</a>
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[0.65rem] font-bold transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="{{ config('laverie.contact.tiktok_url') }}" target="_blank" rel="noopener noreferrer" aria-label="TikTok">TT</a>
            </div>
        </div>
    </div>
    <div class="px-5 py-5 text-center text-xs text-stone-500">© {{ date('Y') }} Laverie Nails. {{ __('ui.footer.copyright') }}</div>
</footer>