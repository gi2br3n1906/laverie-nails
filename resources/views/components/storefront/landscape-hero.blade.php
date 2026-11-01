@props([
    'heading',
    'eyebrow' => null,
])

<section class="relative isolate overflow-hidden bg-[#E8E1F1]" aria-label="{{ $heading }}" data-landscape-hero>
    <img class="absolute inset-0 -z-20 h-full w-full object-cover object-center" src="{{ asset('images/hero-banner.png') }}" alt="" fetchpriority="high" data-informational-hero-image>
    <div class="absolute inset-0 -z-10 bg-gradient-to-t from-[#241B31]/75 via-[#241B31]/10 to-white/10" aria-hidden="true"></div>

    <div class="relative mx-auto flex min-h-[19rem] max-w-4xl flex-col items-center justify-end px-5 pb-10 pt-28 text-center sm:min-h-[24rem] sm:pb-14">
        @if (filled($eyebrow))
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-white/85">{{ $eyebrow }}</p>
        @endif
        <h1 class="mt-3 font-script text-5xl leading-none text-white drop-shadow-[0_3px_10px_rgba(12,28,57,0.55)] sm:text-7xl">{{ $heading }}</h1>
    </div>
</section>
