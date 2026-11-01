<x-layouts.storefront :title="__('about.title')">
    <x-storefront.landscape-hero :heading="__('about.heading')" />

    <section class="bg-white px-5 py-16 sm:px-8 sm:py-24 lg:px-12" aria-labelledby="about-story-heading">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="sr-only" id="about-story-heading">{{ __('about.heading') }}</h2>
            <p class="font-display text-2xl leading-relaxed text-[#0C1C39] sm:text-3xl">{{ __('about.paragraphs')[0] }}</p>

            <div class="mt-9 space-y-7 font-serif text-[1.15rem] leading-[1.9] text-stone-700 sm:text-[1.25rem]">
                @foreach (array_slice(__('about.paragraphs'), 1) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>

            <div class="mt-14 border-t border-[#92A1B5]/40 pt-10" aria-label="{{ __('about.signature') }}">
                <p class="font-script text-5xl leading-none text-[#0C1C39]">{{ __('about.signature') }}</p>
                <p class="mt-1 font-script text-2xl text-stone-500">{{ __('about.tagline') }}</p>
            </div>
        </div>
    </section>
</x-layouts.storefront>
