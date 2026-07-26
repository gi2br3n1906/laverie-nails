<x-layouts.app title="Edit Hero Banner">
    <div class="mx-auto max-w-3xl">
        <a class="text-sm font-semibold text-stone-700" href="{{ route('admin.banners.index') }}">← Hero Banners</a>
        <h1 class="mt-4 font-serif text-5xl font-semibold">Edit Hero Banner</h1>
        <form class="mt-8 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8" method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <x-admin.banner-form :banner="$banner" />
            <div class="mt-8 flex justify-end"><button class="rounded-full bg-stone-900 px-7 py-3 font-semibold text-white" type="submit">Update banner</button></div>
        </form>
    </div>
</x-layouts.app>