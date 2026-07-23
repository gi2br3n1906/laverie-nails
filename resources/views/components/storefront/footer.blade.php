<footer class="border-t border-[#92A1B5]/40 bg-white text-[#0C1C39]">
    <div class="mx-auto max-w-5xl px-5 py-14 text-center sm:px-8 lg:px-12" data-global-footer-layout>
        <div class="grid justify-center gap-10 md:grid-cols-2 md:gap-16" data-footer-top-row>
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">CUSTOMER SERVICE</p>
                <div class="mt-5 grid gap-3 text-sm text-stone-600">
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('measurements.create') }}">Sizing</a>
                    <a class="transition hover:text-[#0C1C39]" href="{{ route('guidance') }}">Measurement guide</a>
                </div>
            </div>
            <div class="text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#0C1C39]">NEWSLETTER</p>
                <p class="mx-auto mt-5 max-w-md text-sm leading-6 text-stone-600">Sign up to save measurement history and get 10% off for your first order.</p>
                <form class="mx-auto mt-5 max-w-md" action="{{ route('register') }}" method="GET">
                    <label class="sr-only" for="footer-newsletter-email">Email address</label>
                    <div class="flex rounded-full border border-[#92A1B5] bg-[#F8FAFC] p-1.5 focus-within:border-[#0C1C39] focus-within:ring-4 focus-within:ring-[#92A1B5]/20">
                        <input class="min-w-0 flex-1 bg-transparent px-4 py-2 text-sm text-[#0C1C39] outline-none placeholder:text-[#92A1B5]" id="footer-newsletter-email" name="email" type="email" placeholder="sign up" autocomplete="email">
                        <button class="grid size-10 shrink-0 place-items-center rounded-full bg-[#0C1C39] text-lg text-white transition hover:bg-[#192B48]" type="submit" aria-label="Sign up for the newsletter">&gt;</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-12 border-t border-[#92A1B5]/30 pt-10 text-center" data-footer-bottom-row>
            <p class="font-logo text-3xl text-[#0C1C39]">Laverie Nails</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[0.65rem] font-bold transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="https://wa.me/6280000000000" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">WA</a>
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[0.65rem] font-bold transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">IG</a>
                <a class="grid size-10 place-items-center rounded-full border border-[#92A1B5]/60 text-[0.65rem] font-bold transition hover:border-[#0C1C39] hover:bg-[#0C1C39] hover:text-white" href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" aria-label="TikTok">TT</a>
            </div>
        </div>
    </div>
    <div class="border-t border-[#92A1B5]/30 px-5 py-5 text-center text-xs text-stone-500">© {{ date('Y') }} Laverie Nails. Official single-vendor collection.</div>
</footer>