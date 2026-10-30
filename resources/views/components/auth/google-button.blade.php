@props(['label' => null])

<a
    {{ $attributes->merge(['class' => 'inline-flex min-h-12 w-full items-center justify-center gap-3 rounded-xl border border-[#92A1B5]/60 bg-white px-4 py-3 text-sm font-semibold text-[#0C1C39] transition hover:border-[#0C1C39] hover:bg-[#F8FAFC] focus:outline-none focus:ring-4 focus:ring-[#92A1B5]/30']) }}
    href="{{ route('auth.google.redirect') }}"
    data-google-auth-button
>
    <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24">
        <path fill="#4285F4" d="M21.6 12.227c0-.709-.064-1.39-.182-2.045H12v3.868h5.382a4.6 4.6 0 0 1-1.995 3.018v2.51h3.232c1.891-1.742 2.981-4.305 2.981-7.351Z" />
        <path fill="#34A853" d="M12 22c2.7 0 4.964-.895 6.619-2.422l-3.232-2.51c-.895.6-2.04.955-3.387.955-2.605 0-4.81-1.76-5.6-4.124H3.06v2.59A9.997 9.997 0 0 0 12 22Z" />
        <path fill="#FBBC05" d="M6.4 13.899a6.01 6.01 0 0 1 0-3.798V7.51H3.06a10.01 10.01 0 0 0 0 8.98l3.34-2.591Z" />
        <path fill="#EA4335" d="M12 5.977c1.468 0 2.786.505 3.823 1.496l2.868-2.868C16.96 2.99 14.695 2 12 2A9.997 9.997 0 0 0 3.06 7.51l3.34 2.591C7.19 7.737 9.395 5.977 12 5.977Z" />
    </svg>
    {{ $label ?? __('ui.auth.continue_google') }}
</a>
