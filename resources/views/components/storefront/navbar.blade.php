@props(['overlay' => false])

<header @class([
    'sticky top-0 z-50 isolate w-full',
    '-mb-16 bg-gradient-to-b from-[#0C1C39]/70 via-[#0C1C39]/40 to-[#0C1C39]/10 text-white backdrop-blur-md sm:-mb-20' => $overlay,
    'bg-white text-[#0C1C39]' => ! $overlay,
]) data-homepage-navbar data-overlay-navigation="{{ $overlay ? 'true' : 'false' }}" data-navbar-scrolled="false">
    @if ($overlay)
        <span class="pointer-events-none absolute inset-x-0 -bottom-8 -z-10 h-8" data-navbar-blend aria-hidden="true"></span>
    @endif
    <nav class="mx-auto grid h-16 max-w-screen-2xl grid-cols-[1fr_auto_1fr] items-center px-4 sm:h-20 sm:px-8 lg:px-12" aria-label="Navigasi toko">
        <div class="flex items-center justify-start" data-navbar-left>
            <details class="group relative">
                <summary @class(['grid size-10 cursor-pointer list-none place-items-center rounded-full transition', 'text-white hover:bg-white/15 focus-visible:outline-white' => $overlay, 'hover:bg-stone-100 focus-visible:outline-[#0C1C39]' => ! $overlay, 'focus-visible:outline-2 focus-visible:outline-offset-2']) aria-label="Buka menu">
                    <svg @class(['size-5', 'drop-shadow-lg' => $overlay]) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-navbar-icon="menu"><path stroke-linecap="round" d="M3.75 7.5h16.5M3.75 12h16.5M3.75 16.5h16.5" /></svg>
                </summary>
                <div class="absolute left-0 top-12 w-64 rounded-2xl border border-[#92A1B5]/40 bg-white p-3 text-[#0C1C39] shadow-2xl shadow-[#0C1C39]/10 sm:top-14">
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('products.index') }}">Shop all</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('measurements.create') }}">Sizing</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('guidance') }}">Measurement guide</a>
                    <div class="mt-2 border-t border-[#DDE3EB] pt-2" data-hamburger-account-menu>
                        @guest
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('login') }}">Login</a>
                        @else
                            <p class="px-4 pb-1 pt-2 text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-[#60738C]">Akun</p>
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route(auth()->user()->accountHomeRouteName()) }}">Dashboard</a>
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('profile.edit') }}">Pengaturan Akun</a>
                            <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('history.index') }}">Measurement history</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="block w-full rounded-xl px-4 py-3 text-left text-sm font-medium transition hover:bg-stone-100" type="submit">Logout</button>
                            </form>
                        @endguest
                    </div>
                </div>
            </details>
        </div>

        <a @class([
            'justify-self-center whitespace-nowrap font-logo text-2xl tracking-[-0.04em] sm:text-3xl',
            'text-white drop-shadow-lg' => $overlay,
            'text-[#0C1C39]' => ! $overlay,
        ]) href="{{ route('home') }}" data-navbar-brand>Laverie Nails</a>

        <div class="flex items-center justify-end gap-0.5 sm:gap-1" data-navbar-right>
            <a @class(['grid size-10 place-items-center rounded-full transition', 'text-white hover:bg-white/15' => $overlay, 'hover:bg-stone-100' => ! $overlay]) href="{{ route('products.index') }}" aria-label="Cari produk">
                <svg @class(['size-5', 'drop-shadow-lg' => $overlay]) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-navbar-icon="search"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
            </a>
            <a @class(['relative grid size-10 place-items-center rounded-full transition', 'text-white hover:bg-white/15' => $overlay, 'hover:bg-stone-100' => ! $overlay]) href="{{ route('cart.index') }}" aria-label="Tas belanja">
                <svg @class(['size-5', 'drop-shadow-lg' => $overlay]) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-navbar-icon="cart"><path stroke-linejoin="round" d="M5.5 8.5h13l-1 11h-11l-1-11Z" /><path stroke-linecap="round" d="M9 9V6.75a3 3 0 0 1 6 0V9" /></svg>
                @if ($cartQuantity > 0)
                    <span class="absolute -right-0.5 -top-0.5 grid min-h-4 min-w-4 place-items-center rounded-full bg-[#60738C] px-1 text-[0.6rem] font-bold leading-none text-white ring-2 ring-white" data-cart-count>{{ $cartQuantity }}</span>
                    <span class="sr-only">{{ $cartQuantity }} item</span>
                @endif
            </a>
        </div>
    </nav>
</header>