<x-layouts.app :title="__('ui.errors.invalid_filter_title')">
    <div class="mx-auto max-w-xl rounded-3xl border border-stone-200 bg-white p-8 text-center shadow-xl shadow-stone-200/50">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-stone-600">422 • {{ __('ui.errors.invalid_data') }}</p>
        <h1 class="mt-4 font-serif text-4xl font-semibold">{{ __('ui.errors.invalid_filter_heading') }}</h1>
        <p class="mt-4 leading-7 text-stone-600">{{ __('ui.errors.invalid_filter_copy') }}</p>
        <a class="mt-7 inline-flex rounded-full bg-stone-900 px-6 py-3 font-semibold text-white hover:bg-stone-800" href="{{ route('products.index') }}">{{ __('ui.errors.view_all_products') }}</a>
    </div>
</x-layouts.app>