<x-layouts.app :title="__('ui.profile.title')">
    <section class="rounded-[2rem] bg-[#0C1C39] px-6 py-10 text-white sm:px-10" aria-labelledby="profile-heading">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B9C8DB]">{{ __('ui.profile.hero_eyebrow') }}</p>
        <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] sm:text-5xl" id="profile-heading">{{ __('ui.profile.title') }}</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-[#DDE6F0]">{{ __('ui.profile.intro') }}</p>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm font-semibold text-[#0C1C39]" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="information-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">{{ __('ui.profile.profile_eyebrow') }}</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="information-heading">{{ __('ui.profile.personal_info') }}</h2>
            <form class="mt-6 space-y-5" action="{{ route('profile.information.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.profile.name') }}<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required></label>
                @error('name')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.profile.email') }}<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required></label>
                @error('email')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.profile.phone') }}<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel"></label>
                @error('phone')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">{{ __('ui.profile.save_info') }}</button>
            </form>
        </section>

        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="password-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">{{ __('ui.profile.security') }}</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="password-heading">{{ $user->hasPassword() ? __('ui.profile.change_password') : __('ui.profile.create_password') }}</h2>
            <p class="mt-3 text-sm leading-6 text-stone-600">{{ $user->hasPassword() ? __('ui.profile.password_help_change') : __('ui.profile.password_help_create') }}</p>
            <form class="mt-6 space-y-5" action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                @if ($user->hasPassword())
                    <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.profile.current_password') }}<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="current_password" type="password" autocomplete="current-password" required></label>
                    @error('current_password')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                @endif
                <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.profile.new_password') }}<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="password" type="password" autocomplete="new-password" required></label>
                @error('password')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.profile.confirm_new_password') }}<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="password_confirmation" type="password" autocomplete="new-password" required></label>
                <button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">{{ __('ui.profile.update_password') }}</button>
            </form>
        </section>

        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8 lg:col-span-2" aria-labelledby="address-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">{{ __('ui.profile.default_checkout') }}</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="address-heading">{{ __('ui.profile.address_heading') }}</h2>
            <p class="mt-3 text-sm leading-6 text-stone-600">{{ __('ui.profile.address_copy') }}</p>
            <form class="mt-6 grid gap-5" action="{{ route('profile.address.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <x-storefront.region-fields
                    :provinces="$provinces"
                    :selected="$user->only([
                        'province_code',
                        'province_name',
                        'regency_code',
                        'regency_name',
                        'district_code',
                        'district_name',
                        'village_code',
                        'village_name',
                    ])"
                    id-prefix="profile-region"
                />
                <label class="block text-sm font-semibold text-[#0C1C39]">{{ __('ui.checkout.address') }}<textarea class="mt-2 min-h-32 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="address" maxlength="2000" autocomplete="street-address" placeholder="{{ __('ui.checkout.address_placeholder') }}" required>{{ old('address', $user->address) }}</textarea></label>
                @error('address')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <div><button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">{{ __('ui.profile.save_address') }}</button></div>
            </form>
        </section>
    </div>
</x-layouts.app>