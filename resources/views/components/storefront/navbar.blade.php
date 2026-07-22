@props(['overlay' => false])

<header @class([
    'z-50 w-full',
    'absolute inset-x-0 top-0 text-white' => $overlay,
    'sticky top-0 border-b border-[#92A1B5]/40 bg-white/95 text-[#0C1C39] backdrop-blur-xl' => ! $overlay,
]) data-homepage-navbar data-overlay-navigation="{{ $overlay ? 'true' : 'false' }}">
    <nav class="mx-auto flex h-16 max-w-screen-2xl items-center justify-between px-4 sm:h-20 sm:px-8 lg:px-12" aria-label="Navigasi toko">
        <a @class([
            'whitespace-nowrap font-logo text-2xl tracking-[-0.04em] sm:text-3xl',
            'text-white drop-shadow-lg' => $overlay,
            'text-[#0C1C39]' => ! $overlay,
        ]) href="{{ route('home') }}">laverie nails</a>

        <div @class(['flex items-center justify-end gap-0.5 sm:gap-1', 'drop-shadow-lg' => $overlay])>
            <a @class(['grid size-10 place-items-center rounded-full transition', 'text-white hover:bg-white/15' => $overlay, 'hover:bg-stone-100' => ! $overlay]) href="{{ route('products.index') }}" aria-label="Cari produk">
                <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
            </a>
            <a @class(['relative grid size-10 place-items-center rounded-full transition', 'text-white hover:bg-white/15' => $overlay, 'hover:bg-stone-100' => ! $overlay]) href="{{ route('products.index') }}" aria-label="Tas belanja">
                <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="M5.5 8.5h13l-1 11h-11l-1-11Z" /><path stroke-linecap="round" d="M9 9V6.75a3 3 0 0 1 6 0V9" /></svg>
            </a>
            <details class="group relative">
                <summary @class(['grid size-10 cursor-pointer list-none place-items-center rounded-full transition', 'text-white hover:bg-white/15 focus-visible:outline-white' => $overlay, 'hover:bg-stone-100 focus-visible:outline-[#0C1C39]' => ! $overlay, 'focus-visible:outline-2 focus-visible:outline-offset-2']) aria-label="Buka menu">
                    <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M3.75 7.5h16.5M3.75 12h16.5M3.75 16.5h16.5" /></svg>
                </summary>
                <div class="absolute right-0 top-12 w-64 rounded-2xl border border-[#92A1B5]/40 bg-white p-3 text-[#0C1C39] shadow-2xl shadow-[#0C1C39]/10 sm:top-14">
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('products.index') }}">Shop all</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('measurements.create') }}">Find your size</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('guidance') }}">Measurement guide</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ auth()->check() ? route('dashboard') : route('login') }}">{{ auth()->check() ? 'Dashboard' : 'Login' }}</a>
                    @auth<a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('history.index') }}">Measurement history</a>@endauth
                </div>
            </details>
        </div>
    </nav>
</header>