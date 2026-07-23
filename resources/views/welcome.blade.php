<x-layouts.storefront title="Premium Press-On Nails" overlay-navigation>
    @php
        $heroBannerPath = 'images/hero-banner.png';
        $heroBannerVersion = substr(hash_file('sha256', public_path($heroBannerPath)), 0, 12);
    @endphp

    <section class="relative isolate flex min-h-[38rem] items-end justify-center overflow-hidden bg-[#EAF0F6] sm:min-h-[44rem] lg:min-h-[48rem]" data-homepage-hero>
        <img class="absolute inset-0 -z-20 h-full w-full object-cover" src="{{ asset($heroBannerPath) }}?v={{ $heroBannerVersion }}" alt="Elegant Laverie press-on nail collection">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-b from-[#0C1C39]/50 to-transparent" aria-hidden="true" data-homepage-navbar-contrast></div>

        <div class="mx-auto w-full max-w-screen-2xl px-5 pb-7 pt-28 text-center text-white sm:px-8 sm:pb-9 lg:px-12 lg:pb-10">
            <div class="mx-auto max-w-4xl drop-shadow-lg">
                <h1 class="font-script text-6xl leading-none text-white sm:text-8xl lg:text-9xl">Nail It, Fit It, Wear It</h1>
                <p class="mt-4 text-[0.65rem] font-semibold tracking-[0.3em] text-white sm:text-xs">temukan ukuranmu, tampil lebih maksimal</p>
                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row" data-homepage-hero-ctas>
                    <a class="inline-flex min-h-11 items-center justify-center rounded-full bg-[#0C1C39] px-8 text-xs font-semibold uppercase tracking-[0.16em] text-white transition hover:bg-[#192B48]" href="{{ route('products.index') }}">OUR COLLECTION</a>
                    <a class="inline-flex min-h-11 items-center justify-center rounded-full border border-white bg-white/10 px-8 text-xs font-semibold uppercase tracking-[0.16em] text-white backdrop-blur-sm transition hover:bg-white hover:text-[#0C1C39]" href="{{ route('measurements.create') }}">SIZING</a>
                </div>
            </div>
            <div class="mt-7 flex items-center justify-center gap-2" aria-label="Hero slide 1 of 3" data-homepage-hero-indicators>
                <span class="size-2 rounded-full bg-white" aria-hidden="true"></span>
                <span class="size-2 rounded-full bg-white/60" aria-hidden="true"></span>
                <span class="size-2 rounded-full bg-white/60" aria-hidden="true"></span>
            </div>
        </div>
    </section>

    @php
        $sizeProducts = $catalogs->take(5);
        $collectionProducts = $catalogs->count() > 5 ? $catalogs->slice(5, 4) : $catalogs->take(4);
    @endphp

    <section class="bg-white px-5 py-16 sm:px-8 sm:py-20 lg:px-12" aria-labelledby="featured-sets-heading" data-homepage-featured-sets>
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center">
                <h2 class="font-display text-4xl font-semibold tracking-[-0.04em] text-[#0C1C39] sm:text-6xl" id="featured-sets-heading">Featured Sets</h2>
            </div>

            <div class="mt-12 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-5" data-homepage-size-grid>
                @forelse ($sizeProducts as $catalog)
                    <x-storefront.size-product-card :catalog="$catalog" />
                @empty
                    @for ($card = 0; $card < 5; $card++)<x-storefront.size-product-placeholder />@endfor
                @endforelse

                @if ($sizeProducts->isNotEmpty() && $sizeProducts->count() < 5)
                    @for ($card = $sizeProducts->count(); $card < 5; $card++)<x-storefront.size-product-placeholder />@endfor
                @endif
            </div>
        </div>
    </section>

    <section class="overflow-x-auto border-b border-[#92A1B5]/40 bg-white" aria-label="Keunggulan Laverie">
        <div class="mx-auto flex max-w-screen-2xl divide-x divide-[#92A1B5]/30 lg:grid lg:grid-cols-5">
            <x-storefront.benefit title="SALON QUALITY LOOKS" icon="star" />
            <x-storefront.benefit title="ZERO NAIL DAMAGE" icon="shield" />
            <x-storefront.benefit title="REUSABLE" icon="reuse" />
            <x-storefront.benefit title="AFFORDABLE" icon="value" />
            <x-storefront.benefit title="100% HAND PAINTED" icon="painted" />
        </div>
    </section>

    <section class="border-y border-[#92A1B5]/40 bg-white px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="shape-heading">
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#92A1B5]">Find your signature look</p><h2 class="mt-3 font-display text-4xl tracking-[-0.04em] text-[#0C1C39] sm:text-6xl" id="shape-heading">Shop By Style</h2></div>
            <div class="mt-12 flex gap-6 overflow-x-auto pb-4 sm:justify-between sm:gap-4">
                <x-storefront.shape-card name="Classy" shape="Almond" variant="almond" />
                <x-storefront.shape-card name="Coquette" shape="Coffin" variant="coffin" />
                <x-storefront.shape-card name="Y2K" shape="Oval" variant="oval" />
                <x-storefront.shape-card name="Floral" shape="Squoval" variant="squoval" />
                <x-storefront.shape-card name="Grunge" shape="Square" variant="square" />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-screen-2xl px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="collections-heading">
        <div class="max-w-5xl"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#92A1B5]">Made for every mood</p><h2 class="mt-3 font-display text-4xl leading-tight tracking-[-0.04em] text-[#0C1C39] sm:text-6xl" id="collections-heading">Our Handpainted press on nails designed to match every mood, occasion, and style</h2></div>
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4 md:gap-x-6">
            @forelse ($collectionProducts as $catalog)
                <x-storefront.product-card :catalog="$catalog" badge="Laverie edit" />
            @empty
                @for ($card = 0; $card < 4; $card++)<x-storefront.product-placeholder />@endfor
            @endforelse
        </div>
        <div class="mt-12 text-center"><a class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-9 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:bg-[#192B48]" href="{{ route('products.index') }}">Explore all products</a></div>
    </section>

    <section class="bg-[#EAF0F6] px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="reviews-heading">
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center"><h2 class="mx-auto font-script text-6xl leading-none text-[#0C1C39] sm:text-8xl" id="reviews-heading">Speak to Us</h2><p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-stone-600 sm:text-base">Real reviews from those who trust laverie for salon quality nails at home</p></div>
            <div class="mt-12 grid gap-5 md:grid-cols-3">
                @forelse ($reviews as $review)
                    <x-storefront.review-card
                        :initials="str($review->user->name)->substr(0, 2)->upper()"
                        :name="$review->user->name"
                        :title="'Ulasan untuk '.$review->catalog->title"
                        :text="$review->comment"
                        :tone="$loop->iteration === 2 ? 'sand' : ($loop->iteration === 3 ? 'sage' : 'rose')"
                    />
                @empty
                    @for ($card = 0; $card < 3; $card++)<x-storefront.review-placeholder />@endfor
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.storefront>