<x-layouts.app :title="__('privacy.title')">
    <div class="mx-auto max-w-4xl">
        <header class="border-b border-[#92A1B5]/40 pb-10">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-stone-500">{{ __('privacy.eyebrow') }}</p>
            <h1 class="mt-4 font-display text-5xl leading-tight tracking-[-0.04em] text-[#0C1C39] sm:text-6xl">{{ __('privacy.title') }}</h1>
            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.12em] text-stone-500">{{ __('privacy.updated') }}</p>
            <p class="mt-6 max-w-3xl text-base leading-7 text-stone-600">{{ __('privacy.intro') }}</p>
        </header>

        <div class="mt-10 space-y-5">
            @foreach (trans('privacy.sections') as $section)
                <section class="rounded-3xl border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="privacy-section-{{ $loop->index }}">
                    <h2 class="font-display text-2xl font-semibold text-[#0C1C39] sm:text-3xl" id="privacy-section-{{ $loop->index }}">{{ $section['heading'] }}</h2>

                    @foreach ($section['paragraphs'] ?? [] as $paragraph)
                        <p class="mt-4 text-sm leading-7 text-stone-600">{{ $paragraph }}</p>
                    @endforeach

                    @if (filled($section['items'] ?? []))
                        <ul class="mt-5 grid gap-3 text-sm leading-7 text-stone-600">
                            @foreach ($section['items'] as $item)
                                <li class="flex gap-3">
                                    <span class="mt-2 size-1.5 shrink-0 rounded-full bg-[#0C1C39]" aria-hidden="true"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach

            <section class="rounded-3xl border border-[#0C1C39] bg-[#0C1C39] p-6 text-white shadow-sm sm:p-8" aria-labelledby="privacy-contact-heading">
                <h2 class="font-display text-2xl font-semibold sm:text-3xl" id="privacy-contact-heading">{{ __('privacy.contact.heading') }}</h2>
                <p class="mt-4 text-sm leading-7 text-[#DDE6F0]">{{ __('privacy.contact.body') }}</p>
                <p class="mt-5 text-sm font-semibold">
                    {{ __('privacy.contact.email_label') }}:
                    <a class="underline decoration-white/40 underline-offset-4 transition hover:decoration-white" href="mailto:{{ __('privacy.contact.email') }}">{{ __('privacy.contact.email') }}</a>
                </p>
            </section>
        </div>
    </div>
</x-layouts.app>
