@php($fingerLabels = ['jempol' => 'Thumb', 'telunjuk' => 'Index', 'tengah' => 'Middle', 'manis' => 'Ring', 'kelingking' => 'Pinky'])

<x-layouts.storefront :title="$product->name">
    <section class="mx-auto max-w-screen-2xl px-5 py-10 sm:px-8 sm:py-16 lg:px-12" aria-labelledby="product-heading">
        <a class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-stone-500 transition hover:text-[#0C1C39]" href="{{ url('/#collection') }}">
            <span aria-hidden="true">←</span> Kembali ke koleksi
        </a>

        <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="overflow-hidden rounded-[2rem] bg-[#EAF0F6]">
                <div class="aspect-[4/5]">
                    @if ($product->primaryImage)
                        <img class="size-full object-cover" src="{{ Storage::disk('public')->url($product->primaryImage->image_path) }}" alt="{{ $product->name }}">
                    @else
                        <div class="grid size-full place-items-center bg-gradient-to-br from-[#EAF0F6] via-white to-[#DDE6F0] text-xs font-semibold uppercase tracking-[0.14em] text-stone-400">No image</div>
                    @endif
                </div>
            </div>

            <div class="lg:py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">{{ $product->category->name }}</p>
                <h1 class="mt-3 font-display text-4xl leading-tight tracking-[-0.03em] text-[#0C1C39] sm:text-6xl" id="product-heading">{{ $product->name }}</h1>
                <p class="mt-5 text-xl font-semibold text-stone-800">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ $product->stock }} tersedia</p>
                <p class="mt-7 max-w-xl text-sm leading-7 text-stone-600 sm:text-base">{{ $product->description }}</p>

                @if ($errors->any())
                    <div class="mt-7 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm text-[#0C1C39]" role="alert">
                        <p class="font-semibold">Periksa kembali pilihan ukuran Anda.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-stone-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="mt-8 space-y-8" action="{{ route('cart.store') }}" method="POST">
                    @csrf
                    <input name="product_id" type="hidden" value="{{ $product->id }}">
                    <input name="quantity" type="hidden" value="1">

                    <fieldset>
                        <legend class="text-sm font-semibold uppercase tracking-[0.12em] text-[#0C1C39]">Pilih tipe ukuran</legend>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-[#92A1B5]/60 bg-white px-5 py-4 text-sm font-semibold transition has-checked:border-[#0C1C39] has-checked:bg-[#EAF0F6]">
                                <input class="size-4 accent-[#0C1C39]" name="size_type" type="radio" value="standard" @checked(old('size_type') === 'standard')>
                                Standard Size
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-[#92A1B5]/60 bg-white px-5 py-4 text-sm font-semibold transition has-checked:border-[#0C1C39] has-checked:bg-[#EAF0F6]">
                                <input class="size-4 accent-[#0C1C39]" name="size_type" type="radio" value="custom" @checked(old('size_type') === 'custom')>
                                Custom Measurements
                            </label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-sm font-semibold text-[#0C1C39]">Standard Size</legend>
                        <p class="mt-1 text-xs leading-5 text-stone-500">Pilih XS, S, M, atau L jika Anda sudah mengetahui ukuran Laverie.</p>
                        <div class="mt-4 grid grid-cols-4 gap-3">
                            @foreach ($standardSizes as $size)
                                <label class="grid cursor-pointer place-items-center rounded-xl border border-[#92A1B5]/60 bg-white px-3 py-3 text-sm font-semibold transition has-checked:border-[#0C1C39] has-checked:bg-[#0C1C39] has-checked:text-white">
                                    <input class="sr-only" name="standard_size" type="radio" value="{{ $size->value }}" @checked(old('standard_size') === $size->value)>
                                    {{ $size->value }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-sm font-semibold text-[#0C1C39]">Custom Measurements</legend>
                        <p class="mt-1 text-xs leading-5 text-stone-500">Masukkan lebar setiap kuku dalam milimeter (0–25 mm) untuk kedua tangan.</p>
                        @if ($savedMeasurements)
                            <p class="mt-2 text-xs font-semibold text-[#385273]">Ukuran tersimpan telah diisi otomatis.</p>
                        @endif
                        <div class="mt-5 grid gap-6 sm:grid-cols-2">
                            @foreach (['right_hand' => 'Right Hand', 'left_hand' => 'Left Hand'] as $hand => $handLabel)
                                <div class="rounded-2xl border border-[#92A1B5]/40 bg-white p-5">
                                    <h2 class="font-display text-2xl text-[#0C1C39]">{{ $handLabel }}</h2>
                                    <div class="mt-4 space-y-3">
                                        @foreach ($fingerLabels as $finger => $fingerLabel)
                                            <label class="grid grid-cols-[1fr_6.5rem] items-center gap-3 text-xs font-semibold uppercase tracking-[0.1em] text-stone-500">
                                                {{ $fingerLabel }}
                                                <span class="relative">
                                                    <input class="w-full rounded-xl border border-[#92A1B5]/60 bg-[#F8FAFC] px-3 py-2.5 pr-9 text-sm text-[#0C1C39] outline-none transition focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" name="custom_measurements[{{ $hand }}][{{ $finger }}]" type="number" min="0" max="25" step="0.1" value="{{ old("custom_measurements.{$hand}.{$finger}", data_get($savedMeasurements, "{$hand}.{$finger}")) }}" inputmode="decimal">
                                                    <span class="pointer-events-none absolute inset-y-0 right-3 grid place-items-center text-[0.65rem] font-medium normal-case tracking-normal text-stone-400">mm</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    <button class="inline-flex min-h-12 w-full items-center justify-center rounded-full bg-[#0C1C39] px-8 text-xs font-semibold uppercase tracking-[0.15em] text-white transition hover:bg-[#192B48] disabled:cursor-not-allowed disabled:bg-stone-300" type="submit" @disabled($product->stock < 1)>
                        {{ $product->stock > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' }}
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.storefront>