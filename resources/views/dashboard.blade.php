@php($fingerLabels = ['jempol' => 'Jempol', 'telunjuk' => 'Telunjuk', 'tengah' => 'Tengah', 'manis' => 'Manis', 'kelingking' => 'Kelingking'])

<x-layouts.app title="Portal Pelanggan">
    <section class="overflow-hidden rounded-[2rem] bg-[#0C1C39] px-6 py-10 text-white sm:px-10" aria-labelledby="portal-heading">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#B9C8DB]">Customer portal</p>
        <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] sm:text-5xl" id="portal-heading">Halo, {{ $user->name }}</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-[#DDE6F0]">Pantau pesanan dan simpan ukuran kuku agar custom set berikutnya terisi otomatis.</p>
    </section>

    @if (session('status'))
        <div class="mt-6 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm font-semibold text-[#0C1C39]" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid items-start gap-8 xl:grid-cols-[1.25fr_0.75fr]">
        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="orders-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Riwayat transaksi</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="orders-heading">Pesanan Saya</h2>

            <div class="mt-6 space-y-4">
                @forelse ($orders as $order)
                    <article class="rounded-2xl border border-[#92A1B5]/40 bg-[#F8FAFC] p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="font-mono text-sm font-semibold text-[#0C1C39]">{{ $order->id }}</p>
                                <p class="mt-1 text-xs text-stone-500">{{ $order->created_at->translatedFormat('d M Y, H.i') }}</p>
                            </div>
                            <p class="text-base font-bold text-[#0C1C39]">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                        </div>
                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                            <div><dt class="text-xs uppercase tracking-[0.1em] text-stone-500">Pembayaran</dt><dd class="mt-1 font-semibold capitalize">{{ $order->payment_status->value }}</dd></div>
                            <div><dt class="text-xs uppercase tracking-[0.1em] text-stone-500">Pengiriman</dt><dd class="mt-1 font-semibold capitalize">{{ $order->fulfillment_status->value }}</dd></div>
                            <div><dt class="text-xs uppercase tracking-[0.1em] text-stone-500">Nomor resi</dt><dd class="mt-1 font-semibold">{{ $order->tracking_number ?: 'Belum tersedia' }}</dd></div>
                        </dl>
                        <a class="mt-5 inline-flex min-h-10 items-center justify-center rounded-full border border-[#0C1C39] px-5 text-xs font-semibold uppercase tracking-[0.12em] transition hover:bg-[#0C1C39] hover:text-white" href="{{ route('dashboard.orders.show', $order) }}">Lihat item</a>
                    </article>
                @empty
                    <div class="rounded-2xl bg-[#EAF0F6] p-8 text-center">
                        <p class="font-semibold text-[#0C1C39]">Belum ada pesanan pada akun ini.</p>
                        <a class="mt-4 inline-flex text-sm font-semibold underline underline-offset-4" href="{{ route('home') }}#collection">Jelajahi koleksi</a>
                    </div>
                @endforelse
            </div>
            <div class="mt-6">{{ $orders->links() }}</div>
        </section>

        <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="measurements-heading">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Profil ukuran</p>
            <h2 class="mt-2 font-display text-3xl text-[#0C1C39]" id="measurements-heading">Ukuran 10 Jari</h2>
            <p class="mt-3 text-sm leading-6 text-stone-600">Masukkan lebar kuku dalam milimeter (0–25 mm).</p>
            <form class="mt-6 space-y-6" action="{{ route('dashboard.measurements.update') }}" method="POST">
                @csrf
                @method('PATCH')
                @foreach (['right_hand' => 'Tangan Kanan', 'left_hand' => 'Tangan Kiri'] as $hand => $handLabel)
                    <fieldset class="rounded-2xl border border-[#92A1B5]/40 bg-[#F8FAFC] p-5">
                        <legend class="px-2 font-semibold text-[#0C1C39]">{{ $handLabel }}</legend>
                        <div class="mt-2 space-y-3">
                            @foreach ($fingerLabels as $finger => $fingerLabel)
                                <label class="grid grid-cols-[1fr_7rem] items-center gap-3 text-sm font-medium text-stone-600">
                                    {{ $fingerLabel }}
                                    <input class="rounded-xl border border-[#92A1B5]/60 bg-white px-3 py-2 text-[#0C1C39] outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="custom_measurements[{{ $hand }}][{{ $finger }}]" type="number" min="0" max="25" step="0.1" inputmode="decimal" value="{{ old("custom_measurements.{$hand}.{$finger}", data_get($user->default_size_payload, "{$hand}.{$finger}")) }}" required>
                                </label>
                                @error("custom_measurements.{$hand}.{$finger}")
                                    <p class="text-xs font-semibold text-[#385273]">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
                <button class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-[#0C1C39] px-6 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" type="submit">Simpan profil ukuran</button>
            </form>
        </section>
    </div>
</x-layouts.app>