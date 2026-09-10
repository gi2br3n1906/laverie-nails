<x-layouts.app title="Our Collection">
    @php
        $searchQuery = (string) ($search ?? '');
        $activeCategoryId = is_numeric((string) ($selectedCategory ?? '')) ? (int) $selectedCategory : null;
        $activeCategorySlug = $activeCategoryId === null ? (string) ($selectedCategory ?? '') : null;
        $currentSizeValue = $selectedSize?->value;

        $hasActiveCategory = $selectedCategory !== null && $selectedCategory !== '';
        $hasActiveSize = $currentSizeValue !== null;
        $hasActiveSearch = trim($searchQuery) !== '';
        $hasActiveFilter = $hasActiveCategory || $hasActiveSize || $hasActiveSearch;
    @endphp

    <section class="text-center">
        <h1 class="font-serif text-5xl font-semibold tracking-wide sm:text-6xl">Our Collection</h1>
        <p class="mx-auto mt-5 max-w-3xl leading-7 text-stone-600">Handpainted press on nails designed to match every mood, occasion, and style</p>
    </section>

    <section class="mx-auto mt-9 max-w-3xl">
        <form action="{{ route('products.index') }}" method="GET" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-center" data-products-search>
            @if ($hasActiveCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            @if ($hasActiveSize)
                <input type="hidden" name="size" value="{{ $currentSizeValue }}">
            @endif

            <label class="sr-only" for="products-search">Cari produk</label>
            <div class="relative">
                <input
                    id="products-search"
                    type="search"
                    name="search"
                    value="{{ $searchQuery }}"
                    placeholder="Search by name or description"
                    class="w-full rounded-full border border-[#92A1B5] bg-white px-4 py-2.5 pr-11 text-sm text-[#0C1C39] outline-none placeholder:text-stone-500 focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20"
                >
                <button type="submit" class="absolute inset-y-0 right-1 my-1 flex w-9 items-center justify-center rounded-full bg-[#0C1C39] text-white transition hover:bg-[#192B48]" aria-label="Cari produk">
                    <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
                </button>
            </div>

            <button class="min-h-11 rounded-full bg-[#0C1C39] px-5 text-sm font-semibold text-white transition hover:bg-[#192B48]" type="submit" aria-label="Jalankan pencarian">
                Search
            </button>
        </form>
    </section>

    <section class="mt-8" aria-label="Filter kategori">
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Kategori</p>
        <nav class="flex flex-wrap gap-2" aria-label="Filter kategori">
            <a
                @class([
                    'rounded-full px-5 py-2.5 text-sm font-semibold transition',
                    'bg-[#0C1C39] text-white' => ! $hasActiveCategory,
                    'border border-[#92A1B5]/50 bg-white text-stone-600' => $hasActiveCategory,
                ])
                href="{{ route('products.index', array_filter(['search' => $searchQuery ?: null, 'size' => $currentSizeValue ?: null])) }}"
            >All Styles</a>
            @foreach ($categories as $category)
                @php
                    $categoryIsActive = $activeCategoryId === $category->id || $activeCategorySlug === (string) $category->slug;
                @endphp
                <a
                    @class([
                        'rounded-full px-5 py-2.5 text-sm font-semibold transition',
                        'bg-[#0C1C39] text-white' => $categoryIsActive,
                        'border border-[#92A1B5]/50 bg-white text-[#0C1C39]' => ! $categoryIsActive,
                    ])
                    href="{{ route('products.index', array_filter([
                        'search' => $searchQuery ?: null,
                        'category' => $category->slug,
                        'size' => $currentSizeValue ?: null,
                    ])) }}"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>
    </section>

    <section class="mt-8" aria-label="Filter ukuran">
        <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">Ukuran</p>
        <nav class="flex flex-wrap gap-2" aria-label="Filter ukuran">
            <a
                @class([
                    'rounded-full px-5 py-2.5 text-sm font-semibold transition',
                    'bg-[#0C1C39] text-white' => ! $hasActiveSize,
                    'border border-[#92A1B5]/50 bg-white text-stone-600' => $hasActiveSize,
                ])
                href="{{ route('products.index', array_filter(['search' => $searchQuery ?: null, 'category' => $selectedCategory ?: null])) }}"
            >All</a>
            @foreach ($sizes as $size)
                <a
                    @class([
                        'rounded-full px-5 py-2.5 text-sm font-semibold transition',
                        'bg-[#0C1C39] text-white' => $selectedSize === $size,
                        'border border-[#92A1B5]/50 bg-white text-[#0C1C39]' => $selectedSize !== $size,
                    ])
                    href="{{ route('products.index', array_filter(['search' => $searchQuery ?: null, 'size' => $size->value, 'category' => $selectedCategory ?: null])) }}"
                >{{ $size->value }}</a>
            @endforeach
        </nav>
    </section>

    @if ($hasActiveFilter)
        <div class="mt-8">
            <a class="inline-flex rounded-full border border-[#92A1B5]/50 px-5 py-2.5 text-sm font-semibold transition hover:border-[#0C1C39] hover:text-[#0C1C39]" href="{{ route('products.index') }}">Reset Filter</a>
        </div>
    @endif

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($catalogs as $product)
            <x-storefront.product-card :product="$product" badge="Shop All" :homepage-card="false" />
        @empty
            <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-12 text-center sm:col-span-2 lg:col-span-3">
                <p class="font-serif text-2xl font-semibold">Tidak ada produk untuk filter yang dipilih</p>
                <p class="mt-2 text-stone-500">Coba ulangi pencarian dengan kata kunci lain atau kosongkan filter.</p>
                <a class="mt-6 inline-flex rounded-full bg-[#0C1C39] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#192B48]" href="{{ route('products.index') }}">Kembali ke semua produk</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>