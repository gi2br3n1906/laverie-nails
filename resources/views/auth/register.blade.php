<x-layouts.auth title="Register">
    <x-auth-card title="Create your account" subtitle="Register to securely save your future manual nail measurements.">
        <form class="space-y-5" method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="name">Full name</label>
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
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="email">Email address</label>
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
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="password">Password</label>
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
                <label class="mb-2 block text-sm font-semibold text-stone-700" for="password_confirmation">Confirm password</label>
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
                Register
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-stone-600">
            Already registered?
            <a class="font-semibold text-stone-900 hover:text-stone-800" href="{{ route('login') }}">Login</a>
        </p>
    </x-auth-card>
</x-layouts.auth>