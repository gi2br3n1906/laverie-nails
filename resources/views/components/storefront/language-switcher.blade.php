@props(['overlay' => false])

<div
    @class([
        'inline-flex items-center rounded-full border p-0.5 text-[0.6rem] font-bold uppercase tracking-[0.08em]',
        'border-white/30' => $overlay,
        'border-[#92A1B5]/50 bg-white' => ! $overlay,
    ])
    role="group"
    aria-label="{{ __('ui.language.label') }}"
    data-language-switcher
>
    <a
        @class([
            'rounded-full px-1.5 py-1.5 transition sm:px-2',
            'bg-white text-[#0C1C39]' => $overlay && app()->getLocale() === 'en',
            'text-white/70 hover:text-white' => $overlay && app()->getLocale() !== 'en',
            'bg-[#0C1C39] text-white' => ! $overlay && app()->getLocale() === 'en',
            'text-stone-500 hover:text-[#0C1C39]' => ! $overlay && app()->getLocale() !== 'en',
        ])
        href="{{ route('locale.switch', 'en') }}"
        hreflang="en"
        aria-label="{{ __('ui.language.switch_to_english') }}"
        @if (app()->getLocale() === 'en') aria-current="true" @endif
    >EN</a>
    <a
        @class([
            'rounded-full px-1.5 py-1.5 transition sm:px-2',
            'bg-white text-[#0C1C39]' => $overlay && app()->getLocale() === 'id',
            'text-white/70 hover:text-white' => $overlay && app()->getLocale() !== 'id',
            'bg-[#0C1C39] text-white' => ! $overlay && app()->getLocale() === 'id',
            'text-stone-500 hover:text-[#0C1C39]' => ! $overlay && app()->getLocale() !== 'id',
        ])
        href="{{ route('locale.switch', 'id') }}"
        hreflang="id"
        aria-label="{{ __('ui.language.switch_to_indonesian') }}"
        @if (app()->getLocale() === 'id') aria-current="true" @endif
    >ID</a>
</div>
