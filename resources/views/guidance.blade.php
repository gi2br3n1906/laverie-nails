<x-layouts.app title="How to Measure Your Nails">
    <section class="mx-auto max-w-4xl text-center">
        <h1 class="font-display text-5xl font-semibold leading-tight tracking-[-0.04em] text-[#0C1C39] sm:text-7xl">How to Measure Your Nails</h1>
        <p class="mx-auto mt-6 max-w-2xl text-base leading-7 text-stone-600 sm:text-lg">a quick and easy guide to ensure your press on nails fit comfortably and look stunning</p>
    </section>

    <section class="mx-auto mt-20 max-w-4xl space-y-20" aria-label="Measurement instructions">
        <article class="flex flex-col items-center text-center">
            <div>
                <h2 class="font-display text-4xl font-semibold text-[#0C1C39] sm:text-5xl">Measure 1</h2>
            </div>
            <div class="mt-8 grid w-full gap-4 sm:grid-cols-3" data-guidance-image-grid="measure-1">
                @for ($image = 1; $image <= 3; $image++)
                    <img class="aspect-[3/4] w-full rounded-[1.5rem] border border-[#92A1B5]/40 bg-white object-cover shadow-xl shadow-[#0C1C39]/5" src="{{ asset('images/measure-1.svg') }}" alt="Measure 1 illustration {{ $image }}: place a millimeter ruler across the widest part of the nail plate">
                @endfor
            </div>
            <p class="mt-7 max-w-2xl text-base leading-8 text-stone-600">Place a millimeter ruler straight across the widest part of your nail plate. Begin at the left edge and keep the ruler flat rather than following the curve of your nail.</p>
        </article>

        <article class="flex flex-col items-center border-t border-[#92A1B5]/30 pt-20 text-center">
            <div>
                <h2 class="font-display text-4xl font-semibold text-[#0C1C39] sm:text-5xl">Measure 2</h2>
            </div>
            <div class="mt-8 grid w-full gap-4 sm:grid-cols-3" data-guidance-image-grid="measure-2">
                @for ($image = 1; $image <= 3; $image++)
                    <img class="aspect-[3/4] w-full rounded-[1.5rem] border border-[#92A1B5]/40 bg-white object-cover shadow-xl shadow-[#0C1C39]/5" src="{{ asset('images/measure-2.svg') }}" alt="Measure 2 illustration {{ $image }}: record the nail width in millimeters for all five fingers">
                @endfor
            </div>
            <p class="mt-7 max-w-2xl text-base leading-8 text-stone-600">Read the width in millimeters and record it for every finger, from your thumb to your pinky. Measure the other hand separately whenever its nail widths are different.</p>
        </article>
    </section>

    <section class="mx-auto mt-24 max-w-5xl text-center" id="video-panduan">
        <h2 class="font-display text-3xl font-semibold leading-tight text-[#0C1C39] sm:text-4xl">watch the video below for a more detailed guide</h2>
        <div class="mt-8 rounded-[2rem] border border-[#92A1B5]/40 bg-white p-3 shadow-xl shadow-[#0C1C39]/5 sm:p-5">
            <video class="aspect-video w-full rounded-2xl bg-[#EAF0F6]" controls preload="metadata" aria-label="Video guide for measuring nails"><track kind="captions" srclang="en" label="English"></video>
        </div>
        <a class="mt-8 inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-10 text-xs font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#192B48]" href="{{ route('measurements.create') }}" data-guidance-input-cta>Input Data</a>
    </section>
</x-layouts.app>