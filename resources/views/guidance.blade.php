<x-layouts.app :title="__('ui.guidance.title')">
    <section class="mx-auto max-w-4xl text-center">
        <h1 class="font-display text-5xl font-semibold leading-tight tracking-[-0.04em] text-[#0C1C39] sm:text-7xl">{{ __('ui.guidance.title') }}</h1>
        <p class="mx-auto mt-6 max-w-2xl text-base leading-7 text-stone-600 sm:text-lg">{{ __('ui.guidance.intro') }}</p>
    </section>

    <section class="mx-auto mt-20 max-w-4xl space-y-20" aria-label="{{ __('ui.guidance.instructions_aria') }}">
        <article class="flex flex-col items-center text-center">
            <div>
                <h2 class="font-display text-4xl font-semibold text-[#0C1C39] sm:text-5xl">{{ __('ui.guidance.measure_1') }}</h2>
            </div>
            <div class="mt-8 grid w-full grid-cols-3 gap-2 sm:gap-3 md:gap-4" data-guidance-image-grid="measure-1">
                @for ($image = 1; $image <= 3; $image++)
                    <img class="aspect-[3/4] h-auto w-full rounded-[1.5rem] border border-[#92A1B5]/40 bg-white object-contain shadow-xl shadow-[#0C1C39]/5" src="{{ asset('images/measure-1.svg') }}" alt="{{ __('ui.guidance.measure_1_alt', ['number' => $image]) }}">
                @endfor
            </div>
            <p class="mt-7 max-w-2xl text-xs leading-6 text-stone-600 md:text-sm md:leading-7">{{ __('ui.guidance.measure_1_copy') }}</p>
        </article>

        <article class="flex flex-col items-center border-t border-[#92A1B5]/30 pt-20 text-center">
            <div>
                <h2 class="font-display text-4xl font-semibold text-[#0C1C39] sm:text-5xl">{{ __('ui.guidance.measure_2') }}</h2>
            </div>
            <div class="mt-8 grid w-full grid-cols-3 gap-2 sm:gap-3 md:gap-4" data-guidance-image-grid="measure-2">
                @for ($image = 1; $image <= 3; $image++)
                    <img class="aspect-[3/4] h-auto w-full rounded-[1.5rem] border border-[#92A1B5]/40 bg-white object-contain shadow-xl shadow-[#0C1C39]/5" src="{{ asset('images/measure-2.svg') }}" alt="{{ __('ui.guidance.measure_2_alt', ['number' => $image]) }}">
                @endfor
            </div>
            <p class="mt-7 max-w-2xl text-xs leading-6 text-stone-600 md:text-sm md:leading-7">{{ __('ui.guidance.measure_2_copy') }}</p>
        </article>
    </section>

    <section class="mx-auto mt-24 max-w-5xl text-center" id="video-panduan">
        <h2 class="font-display text-3xl font-semibold leading-tight text-[#0C1C39] sm:text-4xl">{{ __('ui.guidance.video_heading') }}</h2>
        <div class="mt-8 rounded-[2rem] border border-dashed border-[#92A1B5]/60 bg-white p-8 text-center shadow-sm sm:p-12" data-guidance-video-placeholder>
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-[#EAF0F6] text-[#0C1C39]" aria-hidden="true">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linejoin="round" d="M8 5.5v13l10-6.5-10-6.5Z" /></svg>
            </span>
            <h2 class="mt-5 font-display text-2xl font-semibold text-[#0C1C39] sm:text-3xl">{{ __('ui.guidance.video_soon') }}</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-stone-600">{{ __('ui.guidance.video_copy') }}</p>
        </div>
        <a class="mt-8 inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-10 text-xs font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#192B48]" href="{{ route('measurements.create') }}" data-guidance-input-cta>{{ __('ui.guidance.input_cta') }}</a>
    </section>
</x-layouts.app>