<x-layouts.auth title="Login">
    <x-auth-card title="Welcome back" subtitle="Sign in to access your saved Laverie Nails account.">
        <form class="space-y-5" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-[#0C1C39]" for="email">Email address</label>
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
                <label class="mb-2 block text-sm font-semibold text-[#0C1C39]" for="password">Password</label>
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
                Remember me
            </label>

            <button class="w-full rounded-xl bg-[#0C1C39] px-4 py-3 font-semibold text-white transition hover:bg-[#192B48] focus:outline-none focus:ring-4 focus:ring-[#92A1B5]/40" type="submit">
                Login
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            New to Laverie?
            <a class="font-semibold text-[#0C1C39] hover:text-[#344761]" href="{{ route('register') }}">Create an account</a>
        </p>
    </x-auth-card>
</x-layouts.auth>