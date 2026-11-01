<x-layouts.storefront :title="__('ui.home.title')" overlay-navigation>
    @php
        $heroBannerPath = 'images/hero-banner.png';
        $heroBannerVersion = substr(hash_file('sha256', public_path($heroBannerPath)), 0, 12);
        $heroSlides = $heroBanners->map(fn ($banner): array => [
            'url' => Storage::disk('public')->url($banner->image_path),
            'alt' => 'Laverie hero banner '.$banner->sequence,
            'versioned' => false,
        ]);

        if ($heroSlides->isEmpty()) {
            $heroSlides = collect([[
                'url' => asset($heroBannerPath),
                'alt' => __('ui.home.hero_alt'),
                'versioned' => true,
            ]]);
        }
    @endphp

    <section class="relative isolate flex min-h-[38rem] items-end justify-center overflow-hidden bg-[#EAF0F6] sm:min-h-[44rem] lg:min-h-[48rem]" aria-label="{{ __('ui.home.hero_aria') }}" aria-roledescription="carousel" tabindex="0" data-homepage-hero data-hero-carousel>
        <div class="absolute inset-0 -z-20" data-hero-slides>
            @foreach ($heroSlides as $slide)
                <div @class(['absolute inset-0 transition-opacity duration-700 ease-out', 'opacity-100' => $loop->first, 'pointer-events-none opacity-0' => ! $loop->first]) aria-hidden="{{ $loop->first ? 'false' : 'true' }}" aria-label="{{ __('ui.home.slide_label', ['current' => $loop->iteration, 'total' => count($heroSlides)]) }}" data-hero-slide>
                    <img class="h-full w-full object-cover object-left sm:object-center" src="{{ $slide['url'] }}{{ $slide['versioned'] ? '?v='.$heroBannerVersion : '' }}" alt="{{ $slide['alt'] }}" @if (! $loop->first) loading="lazy" @endif>
                </div>
            @endforeach
        </div>
        <div class="mx-auto w-full max-w-screen-2xl px-5 pb-7 pt-28 text-center text-white sm:px-8 sm:pb-9 lg:px-12 lg:pb-10">
            <div class="mx-auto max-w-4xl drop-shadow-lg">
                <h1 class="font-script text-[2.5rem] leading-[1.05] text-white sm:text-6xl lg:text-8xl">{{ __('ui.home.hero_title') }}</h1>
                <p class="-mt-2 text-sm font-semibold tracking-[0.25em] text-white sm:text-base">{{ __('ui.home.hero_subtitle') }}</p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row" data-homepage-hero-ctas>
                    <a class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#0C1C39] px-8 text-xs font-semibold uppercase tracking-[0.16em] text-white transition hover:bg-[#192B48]" href="{{ route('products.index') }}">{{ __('ui.home.cta_collection') }}</a>
                    <a class="inline-flex min-h-11 items-center justify-center rounded-full border border-white bg-white/10 px-8 text-xs font-semibold uppercase tracking-[0.16em] text-white backdrop-blur-sm transition hover:bg-white hover:text-[#0C1C39]" href="{{ route('measurements.create') }}">{{ __('ui.home.cta_sizing') }}</a>
                </div>
            </div>
            <div class="mt-7 flex items-center justify-center gap-3" aria-label="{{ __('ui.home.hero_choose_slide') }}" data-homepage-hero-indicators>
                @foreach ($heroSlides as $slide)
                    <button @class(['size-2.5 rounded-full border border-white transition', 'bg-white' => $loop->first, 'bg-white/30 hover:bg-white/70' => ! $loop->first]) type="button" aria-label="{{ __('ui.home.hero_show_slide', ['number' => $loop->iteration]) }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" data-hero-indicator="{{ $loop->index }}"></button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="overflow-x-auto bg-[#0C1C39] text-white" aria-label="{{ __('ui.home.benefits_aria') }}" data-homepage-benefits>
        <div class="mx-auto flex max-w-screen-2xl divide-x divide-white/20 lg:grid lg:grid-cols-5">
            <x-storefront.benefit :title="__('ui.home.benefit_salon')" icon="star" />
                        <x-storefront.benefit :title="__('ui.home.benefit_damage')" icon="shield" />
                        <x-storefront.benefit :title="__('ui.home.benefit_reusable')" icon="reuse" />
                        <x-storefront.benefit :title="__('ui.home.benefit_affordable')" icon="value" />
                        <x-storefront.benefit :title="__('ui.home.benefit_painted')" icon="painted" />
        </div>
    </section>

    @php
        $sizeProducts = $catalogs->take(5);
    @endphp

    <section id="pretty-picks" class="bg-white px-5 py-16 sm:px-8 sm:py-20 lg:px-12" aria-labelledby="featured-sets-heading" data-homepage-featured-sets>
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center">
                <h2 class="font-display text-4xl font-semibold tracking-wide text-[#0C1C39] sm:text-6xl" id="featured-sets-heading">{{ __('ui.home.pretty_picks') }}</h2>
            </div>

            <div class="mt-12 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-5" data-homepage-size-grid>
                @forelse ($sizeProducts as $catalog)
                    <x-storefront.product-card :product="$catalog" :badge="__('ui.home.pretty_pick_badge')" :homepage-card="false" />
                @empty
                    @for ($card = 0; $card < 5; $card++)<x-storefront.size-product-placeholder />@endfor
                @endforelse

                @if ($sizeProducts->isNotEmpty() && $sizeProducts->count() < 5)
                    @for ($card = $sizeProducts->count(); $card < 5; $card++)<x-storefront.size-product-placeholder />@endfor
                @endif
            </div>
        </div>
    </section>

    <section class="bg-white px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="shape-heading">
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center"><h2 class="font-display text-4xl tracking-wide text-[#0C1C39] sm:text-6xl" id="shape-heading">{{ __('ui.home.shop_by_style') }}</h2></div>
            <div class="mt-12 flex gap-6 overflow-x-auto pb-4 sm:justify-between sm:gap-4">
                @forelse ($styleCategories as $category)
                    <x-storefront.shape-card :name="$category->name" :category="$category->slug" :image="$category->products->first()?->primaryImage?->image_path" />
                @empty
                    <x-storefront.shape-card name="Classy" shape="Almond" variant="almond" />
                    <x-storefront.shape-card name="Coquette" shape="Coffin" variant="coffin" />
                    <x-storefront.shape-card name="Y2K" shape="Oval" variant="oval" />
                    <x-storefront.shape-card name="Floral" shape="Squoval" variant="squoval" />
                    <x-storefront.shape-card name="Grunge" shape="Square" variant="square" />
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-screen-2xl px-5 py-16 sm:px-8 sm:py-24 lg:px-12" id="collection" aria-labelledby="collections-heading">
        <div class="mx-auto max-w-4xl text-center"><h2 class="font-display text-4xl leading-tight tracking-wide text-[#0C1C39] sm:text-6xl" id="collections-heading">{{ __('ui.home.collection_heading') }}</h2><p class="mx-auto mt-5 max-w-3xl text-sm leading-7 text-stone-600 sm:text-base">{{ __('ui.home.collection_copy') }}</p></div>
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4 md:gap-x-6">
            @forelse ($editorialProducts as $product)
                <x-storefront.product-card :product="$product" />
            @empty
                @for ($card = 0; $card < 4; $card++)<x-storefront.product-placeholder />@endfor
            @endforelse
        </div>
        <div class="mt-12 text-center"><a class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-9 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:bg-[#192B48]" href="{{ route('products.index') }}">{{ __('ui.home.explore_all') }}</a></div>
    </section>

    <section class="bg-[#EAF0F6] px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="reviews-heading">
        <div class="mx-auto max-w-screen-2xl">
            @php($hasDemoReviews = $reviews->contains(fn ($review) => $review->is_demo))
            <div class="text-center"><h2 class="mx-auto font-script text-6xl leading-none text-[#0C1C39] sm:text-8xl" id="reviews-heading">{{ __('ui.home.reviews_heading') }}</h2><p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-stone-600 sm:text-base" @if ($hasDemoReviews) data-homepage-reviews-demo @endif>{{ $hasDemoReviews ? __('ui.home.reviews_demo') : __('ui.home.reviews_real') }}</p></div>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @forelse ($reviews as $review)
                    <x-storefront.review-card
                        :initials="str($review->user->name)->substr(0, 2)->upper()"
                        :name="$review->user->name"
                        :title="__('ui.home.review_title', ['product' => $review->product->name])"
                        :text="$review->comment"
                        :tone="$loop->iteration === 2 ? 'sand' : ($loop->iteration === 3 ? 'sage' : 'rose')"
                        :is-demo="$review->is_demo"
                    />
                @empty
                    @for ($card = 0; $card < 3; $card++)<x-storefront.review-placeholder />@endfor
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.storefront>