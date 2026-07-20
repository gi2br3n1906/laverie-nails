<x-layouts.storefront title="Premium Press-On Nails">
    <section class="relative isolate flex min-h-[38rem] items-end overflow-hidden bg-gradient-to-br from-[#77625b] via-[#aa8379] to-[#d7afa3] sm:min-h-[44rem] lg:min-h-[48rem]" data-homepage-hero>
        <div class="absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <div class="absolute -right-24 top-10 h-96 w-96 rounded-full bg-white/10 blur-3xl sm:right-10 sm:h-[34rem] sm:w-[34rem]"></div>
            <div class="absolute -left-20 bottom-0 h-80 w-80 rounded-full bg-stone-950/20 blur-3xl sm:h-[30rem] sm:w-[30rem]"></div>
            <div class="absolute right-[8%] top-[12%] hidden rotate-12 items-end gap-4 opacity-75 md:flex">
                <span class="h-72 w-20 rounded-t-[70%] rounded-b-[42%] bg-gradient-to-b from-[#f9d8cf] to-[#d89f94] shadow-2xl shadow-stone-950/20"></span>
                <span class="h-80 w-24 rounded-t-[70%] rounded-b-[42%] bg-gradient-to-b from-[#f8cdc3] to-[#c9887e] shadow-2xl shadow-stone-950/20"></span>
                <span class="h-64 w-20 rounded-t-[70%] rounded-b-[42%] bg-gradient-to-b from-[#f9ddd5] to-[#dda79b] shadow-2xl shadow-stone-950/20"></span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-black/5"></div>
        </div>

        <div class="mx-auto w-full max-w-screen-2xl px-5 pb-16 text-white sm:px-8 sm:pb-20 lg:px-12 lg:pb-24">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.26em] text-white/85 sm:text-sm">Press-on nails, perfected for you</p>
                <h1 class="mt-5 font-serif text-5xl leading-[0.95] tracking-[-0.045em] sm:text-7xl lg:text-8xl">Find your perfect fit</h1>
                <p class="mt-6 max-w-xl text-sm leading-6 text-white/85 sm:text-base sm:leading-7">Koleksi press-on nails Laverie yang elegan, reusable, dan dipadukan dengan sistem pengukuran manual untuk hasil yang terasa personal.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a class="inline-flex min-h-12 items-center justify-center rounded-full bg-white px-8 text-xs font-semibold uppercase tracking-[0.15em] text-black transition hover:bg-stone-100" href="{{ route('products.index') }}">Shop the collection</a>
                    <a class="inline-flex min-h-12 items-center justify-center rounded-full border border-white px-8 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:bg-white hover:text-black" href="{{ route('measurements.create') }}">Find my size</a>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-x-auto border-b border-stone-200 bg-white" aria-label="Keunggulan Laverie">
        <div class="mx-auto flex max-w-screen-2xl divide-x divide-stone-200 sm:grid sm:grid-cols-3">
            <x-storefront.benefit title="Zero nail damage" icon="shield" />
            <x-storefront.benefit title="Reusable for life" icon="reuse" />
            <x-storefront.benefit title="Salon quality" icon="star" />
        </div>
    </section>

    @php
        $bestSellers = $catalogs->take(4);
        $collectionProducts = $catalogs->count() > 4 ? $catalogs->slice(4, 4) : $bestSellers;
    @endphp

    <section class="mx-auto max-w-screen-2xl px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="best-sellers-heading">
        <div class="flex items-end justify-between gap-5"><div><p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Most loved</p><h2 class="mt-3 font-serif text-4xl tracking-[-0.04em] sm:text-6xl" id="best-sellers-heading">Best Sellers</h2></div><a class="hidden border-b border-stone-900 pb-1 text-xs font-semibold uppercase tracking-[0.15em] sm:inline" href="{{ route('products.index') }}">View all</a></div>
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4 md:gap-x-6">
            @forelse ($bestSellers as $catalog)
                <x-storefront.product-card :catalog="$catalog" />
            @empty
                @for ($card = 0; $card < 4; $card++)<x-storefront.product-placeholder />@endfor
            @endforelse
        </div>
    </section>

    <section class="border-y border-stone-200 bg-white px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="shape-heading">
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Discover your silhouette</p><h2 class="mt-3 font-serif text-4xl tracking-[-0.04em] sm:text-6xl" id="shape-heading">Shop By Shape</h2></div>
            <div class="mt-12 flex gap-6 overflow-x-auto pb-4 sm:justify-between sm:gap-4">
                <x-storefront.shape-card name="Almond" variant="almond" />
                <x-storefront.shape-card name="Coffin" variant="coffin" />
                <x-storefront.shape-card name="Oval" variant="oval" />
                <x-storefront.shape-card name="Squoval" variant="squoval" />
                <x-storefront.shape-card name="Square" variant="square" />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-screen-2xl px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="collections-heading">
        <div class="max-w-2xl"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Made for every mood</p><h2 class="mt-3 font-serif text-4xl tracking-[-0.04em] sm:text-6xl" id="collections-heading">The Laverie Collections</h2><p class="mt-5 text-sm leading-7 text-stone-600 sm:text-base">Dari soft neutrals hingga statement finish, setiap set dirancang untuk memberi salon-quality look dalam hitungan menit.</p></div>
        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4 md:gap-x-6">
            @forelse ($collectionProducts as $catalog)
                <x-storefront.product-card :catalog="$catalog" badge="Laverie edit" />
            @empty
                @for ($card = 0; $card < 4; $card++)<x-storefront.product-placeholder />@endfor
            @endforelse
        </div>
        <div class="mt-12 text-center"><a class="inline-flex min-h-12 items-center justify-center rounded-full bg-black px-9 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:bg-stone-800" href="{{ route('products.index') }}">Explore all products</a></div>
    </section>

    <section class="bg-[#eee9e4] px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="reviews-heading">
        <div class="mx-auto max-w-screen-2xl">
            <div class="text-center"><p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">Real fits, real moments</p><h2 class="mx-auto mt-3 max-w-3xl font-serif text-4xl tracking-[-0.04em] sm:text-6xl" id="reviews-heading">Loved by the Laverie community</h2></div>
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