<x-layouts.app title="Pengaturan Akun">
    <section class="rounded-[2rem] bg-[#0C1C39] px-6 py-10 text-white sm:px-10" aria-labelledby="profile-heading">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B9C8DB]">Account settings</p>
        <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] sm:text-5xl" id="profile-heading">Pengaturan Akun</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-[#DDE6F0]">Kelola identitas, keamanan, dan alamat utama untuk mempercepat pengalaman belanja Anda.</p>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm font-semibold text-[#0C1C39]" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="information-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Profil</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="information-heading">Informasi Pribadi</h2>
            <form class="mt-6 space-y-5" action="{{ route('profile.information.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-semibold text-[#0C1C39]">Nama lengkap<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required></label>
                @error('name')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">Email<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required></label>
                @error('email')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">Nomor telepon<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel"></label>
                @error('phone')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">Simpan informasi</button>
            </form>
        </section>

        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="password-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Keamanan</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="password-heading">Ganti Password</h2>
            <p class="mt-3 text-sm leading-6 text-stone-600">Konfirmasi password saat ini sebelum membuat password baru.</p>
            <form class="mt-6 space-y-5" action="{{ route('profile.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                <label class="block text-sm font-semibold text-[#0C1C39]">Password saat ini<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="current_password" type="password" autocomplete="current-password" required></label>
                @error('current_password')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">Password baru<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="password" type="password" autocomplete="new-password" required></label>
                @error('password')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39]">Konfirmasi password baru<input class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="password_confirmation" type="password" autocomplete="new-password" required></label>
                <button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">Perbarui password</button>
            </form>
        </section>

        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8 lg:col-span-2" aria-labelledby="address-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Default checkout</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="address-heading">Alamat Pengiriman</h2>
            <form class="mt-6 grid gap-5 md:grid-cols-2" action="{{ route('profile.address.update') }}" method="POST" data-profile-address-form data-cities-url="{{ route('profile.logistics.cities') }}">
                @csrf
                @method('PATCH')
                <label class="block text-sm font-semibold text-[#0C1C39]">Provinsi
                    <select class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="province_id" data-profile-province required>
                        <option value="">Pilih provinsi</option>
                        @foreach ($provinces as $province)<option value="{{ $province['id'] }}" @selected(old('province_id', $user->province_id) === $province['id'])>{{ $province['name'] }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm font-semibold text-[#0C1C39]">Kota / kabupaten
                    <select class="mt-2 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20 disabled:cursor-not-allowed disabled:bg-stone-100" name="city_id" data-profile-city data-old-value="{{ old('city_id', $user->city_id) }}" disabled required><option value="">Pilih provinsi terlebih dahulu</option></select>
                </label>
                @error('province_id')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                @error('city_id')<p class="text-sm font-semibold text-[#385273]">{{ $message }}</p>@enderror
                <label class="block text-sm font-semibold text-[#0C1C39] md:col-span-2">Alamat lengkap<textarea class="mt-2 min-h-32 w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-4 py-3 outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="address" maxlength="2000" autocomplete="street-address" required>{{ old('address', $user->address) }}</textarea></label>
                @error('address')<p class="text-sm font-semibold text-[#385273] md:col-span-2">{{ $message }}</p>@enderror
                <div class="md:col-span-2"><button class="inline-flex min-h-12 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">Simpan alamat</button></div>
            </form>
        </section>
    </div>
</x-layouts.app>