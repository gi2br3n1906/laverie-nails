<x-layouts.app title="Pengaturan Ongkir">
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">Checkout</p>
            <h1 class="mt-3 font-serif text-5xl font-semibold">Pengaturan Ongkir</h1>
            <p class="mt-4 max-w-2xl text-stone-600">Tarif ini dipakai untuk pesanan baru. Pesanan yang sudah dibuat menyimpan tarif saat checkout, sehingga perubahan tidak mengubah total lama.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-2xl bg-emerald-50 px-5 py-4 text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <form class="mt-8 max-w-2xl rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PATCH')

        <div class="grid gap-6 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-stone-900" for="java_cost">
                Pulau Jawa
                <span class="mt-1 block text-sm font-normal text-stone-500">DKI Jakarta, Jawa Barat, Jawa Tengah, DI Yogyakarta, Jawa Timur, Banten.</span>
                <span class="mt-3 flex items-center rounded-xl border border-stone-300 bg-stone-50 px-4 focus-within:border-stone-900 focus-within:ring-2 focus-within:ring-stone-200">
                    <span class="text-sm text-stone-500">Rp</span>
                    <input class="min-h-12 w-full bg-transparent px-2 py-3 text-lg font-semibold text-stone-900 outline-none" id="java_cost" name="java_cost" type="number" min="0" max="1000000" step="500" value="{{ old('java_cost', $javaCost) }}" required>
                </span>
                @error('java_cost')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm font-semibold text-stone-900" for="outside_java_cost">
                Luar Pulau Jawa
                <span class="mt-1 block text-sm font-normal text-stone-500">Seluruh provinsi lainnya, termasuk Sumatera, Kalimantan, Sulawesi, Bali, Nusa Tenggara, Maluku, dan Papua.</span>
                <span class="mt-3 flex items-center rounded-xl border border-stone-300 bg-stone-50 px-4 focus-within:border-stone-900 focus-within:ring-2 focus-within:ring-stone-200">
                    <span class="text-sm text-stone-500">Rp</span>
                    <input class="min-h-12 w-full bg-transparent px-2 py-3 text-lg font-semibold text-stone-900 outline-none" id="outside_java_cost" name="outside_java_cost" type="number" min="0" max="1000000" step="500" value="{{ old('outside_java_cost', $outsideJavaCost) }}" required>
                </span>
                @error('outside_java_cost')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
            </label>
        </div>

        <div class="mt-8 flex flex-wrap items-center gap-3">
            <button class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-stone-800" type="submit">Simpan tarif</button>
            <p class="text-sm text-stone-500">Tarif saat ini: Jawa Rp {{ number_format($javaCost, 0, ',', '.') }} · luar Jawa Rp {{ number_format($outsideJavaCost, 0, ',', '.') }}.</p>
        </div>
    </form>
</x-layouts.app>
