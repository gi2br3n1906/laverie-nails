@props(['overlay' => false])

<header @class([
    'sticky top-0 z-50 isolate w-full',
    '-mb-16 bg-transparent text-white sm:-mb-20' => $overlay,
    'bg-white text-[#0C1C39]' => ! $overlay,
]) data-homepage-navbar data-overlay-navigation="{{ $overlay ? 'true' : 'false' }}" data-navbar-scrolled="false">
    <nav class="mx-auto grid h-16 max-w-screen-2xl grid-cols-[1fr_auto_1fr] items-center px-4 sm:h-20 sm:px-8 lg:px-12" aria-label="{{ __('ui.nav.aria') }}">
        <div class="flex items-center justify-start" data-navbar-left>
            <details class="group relative">
                <summary @class(['grid size-10 cursor-pointer list-none place-items-center rounded-full transition', 'text-white hover:bg-white/15 focus-visible:outline-white' => $overlay, 'hover:bg-stone-100 focus-visible:outline-[#0C1C39]' => ! $overlay, 'focus-visible:outline-2 focus-visible:outline-offset-2']) aria-label="{{ __('ui.nav.open_menu') }}">
                    <svg @class(['size-5', 'drop-shadow-lg' => $overlay]) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-navbar-icon="menu"><path stroke-linecap="round" d="M3.75 7.5h16.5M3.75 12h16.5M3.75 16.5h16.5" /></svg>
                </summary>
                <div class="absolute left-0 top-12 w-64 rounded-2xl border border-[#92A1B5]/40 bg-white p-3 text-[#0C1C39] shadow-2xl shadow-[#0C1C39]/10 sm:top-14">
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('products.index') }}">{{ __('ui.nav.all_products') }}</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('measurements.create') }}">{{ __('ui.nav.sizing') }}</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('guidance') }}">{{ __('ui.nav.measurement_guide') }}</a>
                    <div class="mt-2 border-t border-[#DDE3EB] pt-2" data-hamburger-account-menu>
                        @guest
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                        @else
                            <p class="px-4 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-[#60738C]">{{ __('ui.nav.account') }}</p>
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route(auth()->user()->accountHomeRouteName()) }}">{{ __('ui.nav.dashboard') }}</a>
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('profile.edit') }}">{{ __('ui.nav.account_settings') }}</a>
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('history.index') }}">{{ __('ui.nav.measurement_history') }}</a>
                            <form action="{{ route('logout') }}" method="POST">@csrf<button class="block w-full rounded-xl px-4 py-3 text-left text-sm font-medium transition hover:bg-stone-100" type="submit">{{ __('ui.nav.logout') }}</button></form>
                        @endguest
                    </div>
                </div>
            </details>
        </div>
        <a @class(['justify-self-center whitespace-nowrap font-logo text-2xl tracking-[-0.04em] sm:text-3xl', 'text-white drop-shadow-lg' => $overlay, 'text-[#0C1C39]' => ! $overlay]) href="{{ route('home') }}" data-navbar-brand>Laverie Nails</a>
        <div class="flex items-center justify-end gap-0.5 sm:gap-1" data-navbar-right>
            <x-storefront.language-switcher :overlay="$overlay" />
            <form action="{{ route('products.index') }}" method="GET" class="relative" data-navbar-search-form>
                <button @class(['grid size-11 place-items-center rounded-full transition focus-visible:outline-2 focus-visible:outline-offset-2', 'text-white hover:bg-white/15 focus-visible:outline-white' => $overlay, 'text-[#0C1C39] hover:bg-[#EAF0F6] focus-visible:outline-[#0C1C39]' => ! $overlay]) type="submit" aria-label="{{ __('ui.nav.search_products') }}" aria-expanded="false" aria-controls="navbar-search-panel" data-navbar-search-button><svg @class(['size-5', 'drop-shadow-lg' => $overlay]) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-navbar-icon="search"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4 4" /></svg></button>
                <div class="absolute right-0 top-12 hidden w-72 max-w-[calc(100vw-2rem)] rounded-2xl border border-[#92A1B5]/40 bg-white p-3 text-[#0C1C39] shadow-2xl shadow-[#0C1C39]/10 sm:top-14" id="navbar-search-panel" data-navbar-search-panel>
                    <label class="sr-only" for="navbar-search-input">{{ __('ui.nav.search_products') }}</label>
                    <div class="flex gap-2">
                        <input class="min-w-0 flex-1 rounded-full border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-2.5 text-sm text-[#0C1C39] outline-none placeholder:text-stone-400 focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" id="navbar-search-input" type="search" name="search" autocomplete="off" placeholder="{{ __('ui.nav.search_placeholder') }}" data-navbar-search-input>
                        <button class="rounded-full bg-[#0C1C39] px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.1em] text-white transition hover:bg-[#192B48]" type="submit">{{ __('ui.nav.search') }}</button>
                    </div>
                </div>
            </form>
            <button @class(['relative grid size-11 place-items-center rounded-full border transition focus-visible:outline-2 focus-visible:outline-offset-2', 'border-transparent text-white hover:bg-white/15 focus-visible:outline-white' => $overlay, 'border-[#92A1B5]/50 bg-[#EAF0F6] text-[#0C1C39] hover:bg-[#DDE6F0] focus-visible:outline-[#0C1C39]' => ! $overlay]) type="button" aria-label="{{ __('ui.nav.cart') }}" aria-controls="cart-drawer" data-cart-drawer-trigger data-navbar-cart-button><svg @class(['size-5', 'drop-shadow-lg' => $overlay]) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-navbar-icon="cart"><path stroke-linejoin="round" d="M5.5 8.5h13l-1 11h-11l-1-11Z" /><path stroke-linecap="round" d="M9 9V6.75a3 3 0 0 1 6 0V9" /></svg><span @class(['absolute -right-0.5 -top-0.5 min-h-4 min-w-4 place-items-center rounded-full bg-[#60738C] px-1 text-[0.6rem] font-bold leading-none text-white ring-2 ring-white', 'grid' => $cartQuantity > 0, 'hidden' => $cartQuantity < 1]) data-cart-count>{{ $cartQuantity }}</span><span class="sr-only" data-cart-count-label>{{ __('ui.nav.cart_items', ['count' => $cartQuantity]) }}</span></button>
        </div>
    </nav>
</header>
