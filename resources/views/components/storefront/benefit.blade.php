@props(['title', 'icon'])

<div class="flex min-w-48 items-center justify-center gap-2.5 px-5 py-4 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-[#0C1C39] sm:px-4 lg:min-w-0 lg:text-xs">
    @if ($icon === 'shield')
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="M12 3.5 19 6v5.3c0 4.3-2.7 7.65-7 9.2-4.3-1.55-7-4.9-7-9.2V6l7-2.5Z" /><path stroke-linecap="round" d="m8.8 12 2.1 2.1 4.4-4.4" /></svg>
    @elseif ($icon === 'reuse')
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 8.5A7.5 7.5 0 0 0 6.1 6.1L4 8m1-4v4h4M5 15.5a7.5 7.5 0 0 0 12.9 2.4L20 16m-1 4v-4h-4" /></svg>
    @elseif ($icon === 'star')
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="m12 3 2.3 5.2L20 9l-4.1 3.9 1 5.6-4.9-2.8-4.9 2.8 1-5.6L4 9l5.7-.8L12 3Z" /></svg>
    @elseif ($icon === 'value')
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8.5" /><path stroke-linecap="round" d="M14.75 8.75c-.62-.58-1.56-.9-2.65-.9-1.46 0-2.6.72-2.6 1.84 0 2.9 5.55 1.15 5.55 4.2 0 1.27-1.22 2.17-2.95 2.17-1.21 0-2.29-.4-3.02-1.08M12 6.25v11.5" /></svg>
    @else
        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.5 4.25h7l1 3.5 3.25 1.5-1.5 3.25.5 3.75-3.75.5L12 19.5l-3-2.75-3.75-.5.5-3.75-1.5-3.25 3.25-1.5 1-3.5Z" /><path stroke-linecap="round" d="m9.1 12 1.8 1.8 4-4" /></svg>
    @endif
    <span>{{ $title }}</span>
</div>