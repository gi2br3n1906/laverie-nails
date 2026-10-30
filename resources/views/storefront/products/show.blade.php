@php
    $fingerLabels = ['jempol' => __('ui.common.finger_thumb'), 'telunjuk' => __('ui.common.finger_index'), 'tengah' => __('ui.common.finger_middle'), 'manis' => __('ui.common.finger_ring'), 'kelingking' => __('ui.common.finger_pinky')];
    $whatsappNumber = (string) config('laverie.contact.whatsapp_number');
    $consultationUrl = filled($whatsappNumber) && $whatsappNumber !== '6280000000000'
        ? 'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode(__('ui.product.consult_message', ['product' => $product->name]))
        : null;
@endphp

<x-layouts.storefront :title="$product->name">
    <section class="mx-auto max-w-screen-2xl px-5 py-10 sm:px-8 sm:py-16 lg:px-12" aria-labelledby="product-heading">
        <a class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-stone-500 transition hover:text-[#0C1C39]" href="{{ url('/#collection') }}">
            <span aria-hidden="true">←</span> {{ __('ui.product.back_collection') }}
        </a>

        <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-16">
            <div class="overflow-hidden rounded-[2rem] bg-[#EAF0F6]">
                <div class="aspect-[4/5]">
                    @if ($product->primaryImage)
                        <img class="size-full object-cover" src="{{ Storage::disk('public')->url($product->primaryImage->image_path) }}" alt="{{ $product->name }}">
                    @else
                        <div class="grid size-full place-items-center bg-gradient-to-br from-[#EAF0F6] via-white to-[#DDE6F0] text-xs font-semibold uppercase tracking-[0.14em] text-stone-400">{{ __('ui.common.no_image') }}</div>
                    @endif
                </div>
            </div>

            <div class="lg:py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">{{ $product->category->name }}</p>
                <h1 class="mt-3 font-display text-4xl leading-tight tracking-[-0.03em] text-[#0C1C39] sm:text-6xl" id="product-heading">{{ $product->name }}</h1>
                <p class="mt-5 text-xl font-semibold text-stone-800">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</p>
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-stone-500">{{ __('ui.product.available', ['count' => $product->stock]) }}</p>
                <p class="mt-7 max-w-xl text-sm leading-7 text-stone-600 sm:text-base">{{ $product->description }}</p>

                @if ($consultationUrl)
                    <a class="mt-6 inline-flex items-center gap-3 rounded-full border border-[#92A1B5]/60 px-5 py-3 text-sm font-semibold text-[#0C1C39] transition hover:border-[#0C1C39] hover:bg-[#EAF0F6]" href="{{ $consultationUrl }}" target="_blank" rel="noopener noreferrer">
                        <svg class="size-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M20 11.5a8 8 0 0 1-11.8 7L4 20l1.4-4.1A8 8 0 1 1 20 11.5Z" /></svg>
                        {{ __('ui.product.consult_whatsapp') }}
                    </a>
                @endif

                @if ($errors->any())
                    <div class="mt-7 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm text-[#0C1C39]" role="alert">
                        <p class="font-semibold">{{ __('ui.product.error_heading') }}</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-stone-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="mt-8 space-y-8" action="{{ route('cart.store') }}" method="POST" data-add-to-cart-form>
                    @csrf
                    <input name="product_id" type="hidden" value="{{ $product->id }}">
                    <input name="quantity" type="hidden" value="1">

                    <fieldset>
                        <legend class="text-sm font-semibold uppercase tracking-[0.12em] text-[#0C1C39]">{{ __('ui.product.size_type') }}</legend>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-[#92A1B5]/60 bg-white px-5 py-4 text-sm font-semibold transition has-checked:border-[#0C1C39] has-checked:bg-[#EAF0F6]">
                                <input class="size-4 accent-[#0C1C39]" name="size_type" type="radio" value="standard" @checked(old('size_type', 'standard') === 'standard')>
                                {{ __('ui.product.standard_size') }}
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-[#92A1B5]/60 bg-white px-5 py-4 text-sm font-semibold transition has-checked:border-[#0C1C39] has-checked:bg-[#EAF0F6]">
                                <input class="size-4 accent-[#0C1C39]" name="size_type" type="radio" value="custom" @checked(old('size_type') === 'custom')>
                                {{ __('ui.product.custom_measurements') }}
                            </label>
                        </div>
                    </fieldset>

                    <fieldset data-size-panel="standard" @if (old('size_type') === 'custom') hidden @endif>
                        <legend class="text-sm font-semibold text-[#0C1C39]">{{ __('ui.product.standard_size') }}</legend>
                                                <p class="mt-1 text-xs leading-5 text-stone-500">{{ __('ui.product.standard_help') }}</p>
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
                        <legend class="text-sm font-semibold text-[#0C1C39]">{{ __('ui.product.nail_length') }}</legend>
                                                <p class="mt-1 text-xs leading-5 text-stone-500">{{ __('ui.product.length_help') }}</p>
                        <div class="mt-4 grid grid-cols-3 gap-3">
                            @foreach ($product->available_lengths ?? ['Short', 'Medium', 'Long'] as $length)
                                <label class="grid cursor-pointer place-items-center rounded-xl border border-[#92A1B5]/60 bg-white px-3 py-3 text-sm font-semibold transition has-checked:border-[#0C1C39] has-checked:bg-[#0C1C39] has-checked:text-white">
                                    <input class="sr-only" name="length" type="radio" value="{{ $length }}" @checked(old('length', 'Medium') === $length)>
                                    {{ __('ui.length.'.strtolower($length)) }}
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('length')" />
                    </fieldset>

                    <fieldset data-size-panel="custom" @if (old('size_type') !== 'custom') hidden @endif>
                        <legend class="text-sm font-semibold text-[#0C1C39]">{{ __('ui.product.custom_measurements') }}</legend>
                                                <p class="mt-1 text-xs leading-5 text-stone-500">{{ __('ui.product.custom_help') }}</p>
                        @if ($savedMeasurements)
                            <p class="mt-2 text-xs font-semibold text-[#385273]">{{ __('ui.product.saved_measurements') }}</p>
                        @endif
                        <div class="mt-5 grid gap-6 sm:grid-cols-2">
                            @foreach (['right_hand' => __('ui.common.right_hand'), 'left_hand' => __('ui.common.left_hand')] as $hand => $handLabel)
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
                        {{ $product->stock > 0 ? __('ui.product.add_to_cart') : __('ui.product.out_of_stock_button') }}
                    </button>
                </form>

                <section class="mt-12 border-t border-stone-200 pt-8" aria-labelledby="reviews-heading"><div class="flex items-center justify-between gap-4"><h2 class="font-display text-3xl" id="reviews-heading">{{ __('ui.product.reviews_heading') }}</h2><span class="text-sm font-semibold">{{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }} / 5</span></div>@if ($canReview)<form class="mt-6 space-y-4" method="POST" action="{{ route('storefront.products.reviews.store', $product) }}">@csrf<label class="block text-sm font-semibold" for="rating">{{ __('ui.product.rating') }}</label><select class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3" id="rating" name="rating" required>@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ __('ui.product.stars', ['count' => $rating]) }}</option>@endfor</select><label class="block text-sm font-semibold" for="comment">{{ __('ui.product.comment') }}</label><textarea class="mt-2 min-h-24 w-full rounded-xl border border-stone-300 px-4 py-3" id="comment" name="comment" maxlength="2000" required>{{ old('comment') }}</textarea><button class="rounded-full border border-[#0C1C39] px-5 py-2 text-sm font-semibold" type="submit">{{ __('ui.product.send_review') }}</button></form>@elseif (auth()->guest())<a class="mt-5 inline-flex text-sm font-semibold underline underline-offset-4" href="{{ route('login') }}">{{ __('ui.product.sign_in_review') }}</a>@endif<div class="mt-6 space-y-4">@forelse ($product->reviews as $review)<article class="rounded-2xl bg-stone-50 p-4"><div class="flex justify-between gap-3"><div class="flex flex-wrap items-center gap-2"><p class="font-semibold">{{ $review->user->name }}</p>@if ($review->is_demo)<span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800" data-review-demo>{{ __('ui.product.demo') }}</span>@endif</div><p class="text-amber-600">{{ str_repeat('★', $review->rating) }}</p></div><p class="mt-2 text-sm leading-6 text-stone-600">{{ $review->comment }}</p></article>@empty<p class="text-sm text-stone-500">{{ __('ui.product.no_reviews') }}</p>@endforelse</div></section>
            </div>
        </div>
    </section>
</x-layouts.storefront>