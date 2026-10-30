<x-layouts.auth :title="__('ui.auth.register_title')">
    <x-auth-card :title="__('ui.auth.create_title')" :subtitle="__('ui.auth.register_subtitle')">
        @if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')))
            <x-auth.google-button :label="__('ui.auth.signup_google')" />

            <div class="relative my-6" aria-hidden="true">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#DDE3EB]"></div></div>
                <div class="relative flex justify-center"><span class="bg-white px-3 text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-[#60738C]">{{ __('ui.auth.or') }}</span></div>
            </div>
        @endif

        <form class="space-y-5" method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="name">{{ __('ui.auth.full_name') }}</label>
                <input
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                >
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="email">{{ __('ui.auth.email') }}</label>
                <input
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                >
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="password">{{ __('ui.auth.password') }}</label>
                <input
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                >
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="password_confirmation">{{ __('ui.auth.confirm_password') }}</label>
                <input
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                >
            </div>

            <button class="w-full rounded-xl bg-stone-900 px-4 py-3 font-semibold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-300" type="submit">
                {{ __('ui.auth.register') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            {{ __('ui.auth.already_registered') }}
            <a class="font-semibold text-stone-900 hover:text-stone-800" href="{{ route('login') }}">{{ __('ui.auth.login') }}</a>
        </p>
    </x-auth-card>
</x-layouts.auth>