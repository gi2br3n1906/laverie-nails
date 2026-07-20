<header class="sticky top-0 z-50 border-b border-stone-200/80 bg-white/95 backdrop-blur-xl" data-homepage-navbar>
    <nav class="relative mx-auto grid h-16 max-w-screen-2xl grid-cols-3 items-center px-4 sm:h-20 sm:px-8 lg:px-12" aria-label="Navigasi toko">
        <div class="flex items-center justify-start">
            <details class="group relative">
                <summary class="grid size-10 cursor-pointer list-none place-items-center rounded-full transition hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-900" aria-label="Buka menu">
                    <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M3.75 7.5h16.5M3.75 12h16.5M3.75 16.5h16.5" /></svg>
                </summary>
                <div class="absolute left-0 top-12 w-64 rounded-2xl border border-stone-200 bg-white p-3 shadow-2xl shadow-stone-900/10 sm:top-14">
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('products.index') }}">Shop all</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('measurements.create') }}">Find your size</a>
                    <a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('guidance') }}">Measurement guide</a>
                    @auth<a class="block rounded-xl px-4 py-3 text-sm font-medium transition hover:bg-stone-100" href="{{ route('history.index') }}">Measurement history</a>@endauth
                </div>
            </details>
            <a class="ml-2 hidden text-[0.65rem] font-semibold uppercase tracking-[0.16em] text-stone-600 transition hover:text-black lg:inline" href="{{ route('products.index') }}">Shop</a>
        </div>

        <a class="justify-self-center whitespace-nowrap font-serif text-2xl tracking-[-0.04em] text-black sm:text-3xl" href="{{ route('home') }}">Laverie Nails</a>

        <div class="flex items-center justify-end gap-0.5 sm:gap-1">
            <span class="hidden rounded-full px-2 py-2 text-xs font-medium text-stone-600 lg:inline-flex">IDR</span>
            <span class="hidden rounded-full px-2 py-2 text-xs font-medium text-stone-600 md:inline-flex">ID</span>
            <a class="hidden size-10 place-items-center rounded-full transition hover:bg-stone-100 sm:grid" href="{{ route('products.index') }}" aria-label="Cari produk">
                <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
            </a>
            <a class="grid size-10 place-items-center rounded-full transition hover:bg-stone-100" href="{{ auth()->check() ? route('dashboard') : route('login') }}" aria-label="Profil pengguna">
                <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="3.25" /><path stroke-linecap="round" d="M5.75 20c.72-3.34 3.09-5.25 6.25-5.25s5.53 1.91 6.25 5.25" /></svg>
            </a>
            <a class="relative grid size-10 place-items-center rounded-full transition hover:bg-stone-100" href="{{ route('products.index') }}" aria-label="Tas belanja">
                <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="M5.5 8.5h13l-1 11h-11l-1-11Z" /><path stroke-linecap="round" d="M9 9V6.75a3 3 0 0 1 6 0V9" /></svg>
                <span class="absolute right-1 top-1 grid size-4 place-items-center rounded-full bg-black text-[0.55rem] font-bold text-white">0</span>
            </a>
        </div>
    </nav>
</header>