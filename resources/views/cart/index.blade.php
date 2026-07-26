@php($fingerLabels = ['jempol' => 'Jempol', 'telunjuk' => 'Telunjuk', 'tengah' => 'Tengah', 'manis' => 'Manis', 'kelingking' => 'Kelingking'])

<x-layouts.storefront title="Keranjang Belanja">
    <section class="mx-auto max-w-screen-xl px-5 py-12 sm:px-8 sm:py-16 lg:px-12" aria-labelledby="cart-heading">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Your curated edit</p>
            <h1 class="mt-3 font-display text-4xl tracking-[-0.03em] text-[#0C1C39] sm:text-6xl" id="cart-heading">Keranjang Belanja</h1>
            <p class="mt-4 text-sm leading-7 text-stone-600">Setiap desain disiapkan sesuai ukuran yang Anda pilih.</p>
        </div>

        @if (session('status'))
            <div class="mt-8 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm font-medium text-[#0C1C39]" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-8 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm text-[#0C1C39]" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($items->isEmpty())
            <div class="mt-10 rounded-[2rem] border border-dashed border-[#92A1B5]/60 bg-white px-6 py-16 text-center sm:px-10">
                <div class="mx-auto grid size-16 place-items-center rounded-full bg-[#EAF0F6] text-[#0C1C39]">
                    <svg class="size-7" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linejoin="round" d="M5.5 8.5h13l-1 11h-11l-1-11Z" /><path stroke-linecap="round" d="M9 9V6.75a3 3 0 0 1 6 0V9" /></svg>
                </div>
                <h2 class="mt-6 font-display text-3xl text-[#0C1C39]">Keranjang Anda masih kosong</h2>
                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-stone-500">Temukan set yang Anda sukai, lalu pilih standard size atau custom measurements.</p>
                <a class="mt-7 inline-flex min-h-11 items-center justify-center rounded-full bg-[#0C1C39] px-7 text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" href="{{ url('/#collection') }}">Kembali ke koleksi</a>
            </div>
        @else
            <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <div class="space-y-5">
                    @foreach ($items as $item)
                        <article class="rounded-[1.75rem] border border-[#92A1B5]/40 bg-white p-4 sm:p-5">
                            <div class="grid gap-5 sm:grid-cols-[9rem_minmax(0,1fr)]">
                                <a class="block overflow-hidden rounded-2xl bg-[#EAF0F6]" href="{{ route('storefront.products.show', $item->product) }}">
                                    <div class="aspect-[4/5]">
                                        @if ($item->product->primaryImage)
                                            <img class="size-full object-cover" src="{{ Storage::disk('public')->url($item->product->primaryImage->image_path) }}" alt="{{ $item->product->name }}">
                                        @else
                                            <span class="grid size-full place-items-center bg-gradient-to-br from-[#EAF0F6] via-white to-[#DDE6F0] text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-stone-400">No image</span>
                                        @endif
                                    </div>
                                </a>

                                <div class="min-w-0">
                                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-stone-400">{{ $item->product->category->name }}</p>
                                    <a class="mt-1 block font-display text-2xl leading-tight text-[#0C1C39] transition hover:text-[#60738C]" href="{{ route('storefront.products.show', $item->product) }}">{{ $item->product->name }}</a>

                                    @if ($item->size_type === \App\Enums\CartSizeType::Standard)
                                        <p class="mt-3 text-sm font-semibold text-stone-600">Size: {{ $item->size_payload['size'] }}</p>
                                    @else
                                        <div class="mt-3 text-sm text-stone-600">
                                            <p class="font-semibold">Size: Custom</p>
                                            <div class="mt-2 grid gap-2 text-xs leading-5 sm:grid-cols-2">
                                                @foreach (['right_hand' => 'Tangan kanan', 'left_hand' => 'Tangan kiri'] as $hand => $handLabel)
                                                    <p>
                                                        <span class="font-semibold text-[#0C1C39]">{{ $handLabel }}:</span>
                                                        @foreach ($fingerLabels as $finger => $fingerLabel)
                                                            {{ $fingerLabel }} {{ number_format((float) $item->size_payload[$hand][$finger], 1, ',', '.') }} mm{{ $loop->last ? '' : ',' }}
                                                        @endforeach
                                                    </p>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4 border-t border-[#92A1B5]/30 pt-4">
                                        <div>
                                            <p class="text-xs text-stone-400">Rp {{ number_format((float) $item->product->price, 0, ',', '.') }} / set</p>
                                            <p class="mt-1 text-sm font-semibold text-[#0C1C39]">Rp {{ number_format($item->subtotalInCents() / 100, 0, ',', '.') }}</p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('cart.update', $item) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input name="quantity" type="hidden" value="{{ max(1, $item->quantity - 1) }}">
                                                <button class="grid size-9 place-items-center rounded-full border border-[#92A1B5]/60 text-lg text-[#0C1C39] transition hover:bg-[#EAF0F6] disabled:cursor-not-allowed disabled:opacity-35" type="submit" aria-label="Kurangi jumlah {{ $item->product->name }}" @disabled($item->quantity <= 1)>−</button>
                                            </form>
                                            <span class="min-w-7 text-center text-sm font-semibold" aria-label="Jumlah {{ $item->product->name }}">{{ $item->quantity }}</span>
                                            <form action="{{ route('cart.update', $item) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input name="quantity" type="hidden" value="{{ $item->quantity + 1 }}">
                                                <button class="grid size-9 place-items-center rounded-full border border-[#92A1B5]/60 text-lg text-[#0C1C39] transition hover:bg-[#EAF0F6] disabled:cursor-not-allowed disabled:opacity-35" type="submit" aria-label="Tambah jumlah {{ $item->product->name }}" @disabled($item->quantity >= $item->product->stock)>+</button>
                                            </form>
                                            <form class="ml-2" action="{{ route('cart.destroy', $item) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="text-xs font-semibold uppercase tracking-[0.1em] text-stone-500 underline decoration-[#92A1B5] underline-offset-4 transition hover:text-[#0C1C39]" type="submit" aria-label="Hapus {{ $item->product->name }}">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <aside class="rounded-[1.75rem] bg-[#0C1C39] p-6 text-white lg:sticky lg:top-28" aria-label="Ringkasan keranjang">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#DDE6F0]">Order summary</p>
                    <div class="mt-6 flex items-center justify-between border-b border-white/20 pb-5 text-sm">
                        <span>{{ $items->sum('quantity') }} item</span>
                        <span>Rp {{ number_format($grandTotalInCents / 100, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-5 flex items-end justify-between gap-4">
                        <span class="font-display text-2xl">Grand Total</span>
                        <strong class="text-lg">Rp {{ number_format($grandTotalInCents / 100, 0, ',', '.') }}</strong>
                    </div>
                    <p class="mt-4 text-xs leading-5 text-[#DDE6F0]">Ongkos kirim dan detail pembayaran akan dikonfirmasi pada tahap checkout.</p>
                    <a class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-full bg-white px-6 text-xs font-semibold uppercase tracking-[0.14em] text-[#0C1C39] transition hover:bg-[#EAF0F6]" href="{{ route('checkout.create') }}">Lanjut ke Checkout</a>
                </aside>
            </div>
        @endif
    </section>
</x-layouts.storefront>