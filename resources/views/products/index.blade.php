<x-layouts.app :title="__('ui.catalog.title')">
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
        <h1 class="font-serif text-5xl font-semibold tracking-wide sm:text-6xl">{{ __('ui.catalog.title') }}</h1>
                <p class="mx-auto mt-5 max-w-3xl leading-7 text-stone-600">{{ __('ui.catalog.copy') }}</p>
    </section>

    <section class="mx-auto mt-9 max-w-2xl">
        <form action="{{ route('products.index') }}" method="GET" class="relative" data-products-search>
            @if ($hasActiveCategory)
                <input type="hidden" name="category" value="{{ $selectedCategory }}">
            @endif
            @if ($hasActiveSize)
                <input type="hidden" name="size" value="{{ $currentSizeValue }}">
            @endif

            <label class="sr-only" for="products-search">{{ __('ui.catalog.search_label') }}</label>
            <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-[#60738C]" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4 4" /></svg>
            <input
                id="products-search"
                type="search"
                name="search"
                value="{{ $searchQuery }}"
                placeholder="{{ __('ui.catalog.search_placeholder') }}"
                class="w-full rounded-full border border-[#92A1B5]/70 bg-white py-3 pl-11 pr-14 text-sm text-[#0C1C39] shadow-[0_2px_10px_rgba(12,28,57,0.04)] outline-none transition placeholder:text-stone-500 focus:border-[#0C1C39]/40 focus:ring-4 focus:ring-[#92A1B5]/20 sm:pr-28"
            >
            <button class="absolute inset-y-1 right-1 grid min-w-10 place-items-center rounded-full bg-[#0C1C39] px-3 text-white transition hover:bg-[#192B48] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0C1C39] sm:px-5" type="submit" aria-label="{{ __('ui.catalog.search_aria') }}">
                <svg class="size-4 sm:hidden" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                <span class="hidden text-xs font-semibold uppercase tracking-[0.12em] sm:inline">{{ __('ui.catalog.search') }}</span>
            </button>
        </form>
    </section>

    <section class="mt-8" aria-label="{{ __('ui.catalog.filter_aria') }}">
            <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ __('ui.catalog.filter_label') }}</p>
            <nav class="flex flex-wrap gap-2" aria-label="{{ __('ui.catalog.filter_aria') }}">
            <a
                @class([
                    'rounded-full px-5 py-2.5 text-sm font-semibold transition',
                    'bg-[#0C1C39] text-white' => ! $hasActiveCategory,
                    'border border-[#92A1B5]/50 bg-white text-stone-600' => $hasActiveCategory,
                ])
                href="{{ route('products.index', array_filter(['search' => $searchQuery ?: null, 'size' => $currentSizeValue ?: null])) }}"
            >{{ __('ui.catalog.all_styles') }}</a>
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

    @if ($hasActiveFilter)
        <div class="mt-8">
            <a class="inline-flex rounded-full border border-[#92A1B5]/50 px-5 py-2.5 text-sm font-semibold transition hover:border-[#0C1C39] hover:text-[#0C1C39]" href="{{ route('products.index') }}">{{ __('ui.catalog.reset_filter') }}</a>
        </div>
    @endif

    <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($catalogs as $product)
            <x-storefront.product-card :product="$product" :badge="__('ui.catalog.badge')" :homepage-card="false" />
        @empty
            <div class="rounded-3xl border border-dashed border-stone-300 bg-white p-12 text-center sm:col-span-2 lg:col-span-3">
                <p class="font-serif text-2xl font-semibold">{{ __('ui.catalog.empty_title') }}</p>
                                <p class="mt-2 text-stone-500">{{ __('ui.catalog.empty_copy') }}</p>
                <a class="mt-6 inline-flex rounded-full bg-[#0C1C39] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#192B48]" href="{{ route('products.index') }}">{{ __('ui.catalog.back_all') }}</a>
            </div>
        @endforelse
    </div>
</x-layouts.app>