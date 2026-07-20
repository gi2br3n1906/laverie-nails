<x-layouts.auth title="Login">
    <x-auth-card title="Welcome back" subtitle="Sign in to access your saved Laverie Nails account.">
        <form class="space-y-5" method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="email">Email address</label>
                <input
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
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
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="password">Password</label>
                <input
                    class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-stone-600 focus:ring-4 focus:ring-stone-200"
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                >
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <label class="flex items-center gap-3 text-sm text-stone-600">
                <input class="size-4 rounded border-stone-300 text-stone-900 focus:ring-stone-600" name="remember" type="checkbox" value="1">
                Remember me
            </label>

            <button class="w-full rounded-xl bg-stone-900 px-4 py-3 font-semibold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-300" type="submit">
                Login
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            New to Laverie?
            <a class="font-semibold text-stone-900 hover:text-stone-800" href="{{ route('register') }}">Create an account</a>
        </p>
    </x-auth-card>
</x-layouts.auth>