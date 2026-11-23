@props(['name', 'shape' => null, 'category' => null])

@php
    $slug = $category ?? \Illuminate\Support\Str::slug($name);
    $href = route('products.index', ['category' => $slug]);
@endphp

<a class="group flex min-w-0 flex-col items-center" href="{{ $href }}" aria-label="{{ $shape ? __('ui.home.shape_aria', ['name' => $name, 'shape' => $shape]) : $name }}" data-homepage-shape-card>
    <span class="flex h-28 w-full items-center justify-center bg-white sm:h-36 md:h-40">
        <img class="max-h-full w-auto object-contain transition-transform duration-200 group-hover:-translate-y-1" src="{{ asset('images/styles/'.$slug.'.webp') }}" alt="{{ __('ui.home.shape_image_alt', ['name' => $name]) }}" loading="lazy">
    </span>
    <span class="mt-3 text-center font-serif text-sm text-[#0C1C39] sm:text-base">{{ $name }}</span>
</a>