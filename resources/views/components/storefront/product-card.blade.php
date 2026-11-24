@props(['product', 'badge' => null, 'homepageCard' => true, 'scrollCard' => false, 'compact' => false])

@php($badgeLabel = $badge ?? __('ui.product.badge_default'))

<article @class(['group min-w-0', 'w-[68%] shrink-0 snap-start sm:w-[45%] md:w-auto md:overflow-visible' => $scrollCard === 'pretty-picks']) @if ($homepageCard) data-homepage-product-card @endif @if ($homepageCard === false) data-recommended-product-card @endif>
    <div @class(['relative overflow-hidden bg-[#EAF0F6]', 'rounded-xl sm:rounded-[1.5rem]' => $compact === 'catalog', 'rounded-[1.5rem]' => $compact !== 'catalog'])>
        <a class="block aspect-square sm:aspect-[4/5]" href="{{ route('storefront.products.show', $product) }}" aria-label="{{ __('ui.product.view_aria', ['name' => $product->name]) }}">
            @if ($product->primaryImage)
                <img class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]" src="{{ Storage::disk('public')->url($product->primaryImage->image_path) }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <span class="grid size-full place-items-center bg-gradient-to-br from-[#EAF0F6] via-white to-[#DDE6F0] text-xs font-semibold uppercase tracking-[0.12em] text-stone-400" data-product-image-fallback>{{ __('ui.common.no_image') }}</span>
            @endif
        </a>
        <span @class(['absolute left-2 top-2 rounded-full bg-[#DDE6F0] px-2 py-1 font-semibold uppercase text-[#0C1C39]', 'text-[0.5rem] leading-none tracking-normal sm:left-4 sm:top-4 sm:px-3 sm:py-1.5 sm:text-[0.65rem] sm:tracking-[0.1em]' => $compact === 'catalog', 'left-3 top-3 px-3 py-1.5 text-[0.65rem] tracking-[0.1em] sm:left-4 sm:top-4' => $compact !== 'catalog'])>{{ $badgeLabel }}</span>
        <button @class(['absolute grid place-items-center rounded-full bg-white/90 text-stone-700 shadow-sm transition hover:bg-white hover:text-stone-900', 'right-2 top-2 size-7 sm:right-4 sm:top-4 sm:size-9' => $compact === 'catalog', 'right-3 top-3 size-9 sm:right-4 sm:top-4' => $compact !== 'catalog']) type="button" aria-label="{{ __('ui.product.save_aria', ['name' => $product->name]) }}">
            <svg @class(['size-3.5 sm:size-4.5' => $compact === 'catalog', 'size-4.5' => $compact !== 'catalog']) aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.8 9c0 5.2-8.8 10.2-8.8 10.2S3.2 14.2 3.2 9A4.7 4.7 0 0 1 12 6.65 4.7 4.7 0 0 1 20.8 9Z" /></svg>
        </button>
    </div>
    <div @class(['px-1', 'pt-2 sm:pt-4' => $compact === 'catalog', 'pt-4' => $compact !== 'catalog'])>
        <p @class(['font-semibold uppercase text-stone-400', 'text-[0.55rem] leading-tight tracking-[0.08em] sm:text-xs sm:tracking-[0.12em]' => $compact === 'catalog', 'text-xs tracking-[0.12em]' => $compact !== 'catalog'])>{{ $product->category->name }}</p>
        <h3 @class(['font-display leading-tight text-[#0C1C39]', 'mt-1.5 text-sm sm:mt-2 sm:text-xl md:text-2xl' => $compact === 'catalog', 'mt-2 text-xl tracking-[-0.02em] sm:text-2xl' => $compact !== 'catalog'])><a class="transition hover:text-[#60738C]" href="{{ route('storefront.products.show', $product) }}">{{ $product->name }}</a></h3>
        <p @class(['font-semibold text-stone-800', 'mt-1.5 text-xs sm:mt-2 sm:text-sm' => $compact === 'catalog', 'mt-2 text-sm' => $compact !== 'catalog'])>Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
        @if (($product->reviews_count ?? 0) > 0)<p class="mt-1 text-xs font-medium text-stone-500">★ {{ number_format((float) $product->reviews_avg_rating, 1) }}</p>@endif
        <p class="mt-1 text-xs uppercase tracking-[0.12em] text-stone-400">{{ $product->stock > 0 ? __('ui.product.available', ['count' => $product->stock]) : __('ui.product.out_of_stock') }}</p>
        <a @class(['inline-flex rounded-full bg-[#0C1C39] font-semibold uppercase text-white', 'mt-2 px-2.5 py-1.5 text-[0.6rem] tracking-normal sm:mt-4 sm:px-4 sm:py-2 sm:text-xs sm:tracking-[0.1em]' => $compact === 'catalog', 'mt-4 px-4 py-2 text-xs tracking-[0.1em]' => $compact !== 'catalog']) href="{{ route('storefront.products.show', $product) }}">{{ __('ui.product.view_add') }}</a>
    </div>
</article>