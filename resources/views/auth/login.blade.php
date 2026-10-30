<x-layouts.auth :title="__('ui.auth.login_title')">
    <x-auth-card :title="__('ui.auth.welcome_back')" :subtitle="__('ui.auth.login_subtitle')">
        @if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')))
            <x-auth.google-button />

            <div class="relative my-6" aria-hidden="true">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#DDE3EB]"></div></div>
                <div class="relative flex justify-center"><span class="bg-white px-3 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-[#60738C]">{{ __('ui.auth.or') }}</span></div>
            </div>
        @endif

        <form class="space-y-5" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#0C1C39]" for="email">{{ __('ui.auth.email') }}</label>
                <input
                    class="w-full rounded-xl border border-[#92A1B5]/60 bg-white px-4 py-3 text-[#0C1C39] outline-none transition focus:border-[#92A1B5] focus:ring-4 focus:ring-[#92A1B5]/20"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                >
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#0C1C39]" for="password">{{ __('ui.auth.password') }}</label>
                <input
                    class="w-full rounded-xl border border-[#92A1B5]/60 bg-white px-4 py-3 text-[#0C1C39] outline-none transition focus:border-[#92A1B5] focus:ring-4 focus:ring-[#92A1B5]/20"
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                >
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <label class="flex items-center gap-3 text-sm text-stone-600">
                <input class="size-4 rounded border-[#92A1B5] text-[#0C1C39] focus:ring-[#92A1B5]" name="remember" type="checkbox" value="1">
                {{ __('ui.auth.remember') }}
            </label>

            <button class="w-full rounded-xl bg-[#0C1C39] px-4 py-3 font-semibold text-white transition hover:bg-[#192B48] focus:outline-none focus:ring-4 focus:ring-[#92A1B5]/40" type="submit">
                {{ __('ui.auth.login') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            {{ __('ui.auth.new_to') }}
            <a class="font-semibold text-[#0C1C39] hover:text-[#344761]" href="{{ route('register') }}">{{ __('ui.auth.create_account') }}</a>
        </p>
    </x-auth-card>
</x-layouts.auth>