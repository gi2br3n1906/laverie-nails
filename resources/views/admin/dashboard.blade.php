<x-layouts.app title="Dashboard Admin">
    <section class="rounded-3xl border border-stone-200 bg-white p-8 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-stone-600">Admin area</p>
        <h1 class="font-serif mt-3 text-3xl font-bold tracking-tight">Authorized administrator</h1>
        <p class="mt-4 text-stone-600">This route is protected by authentication and the admin JSON role boundary.</p>
        <a class="mt-6 inline-flex rounded-xl bg-stone-900 px-5 py-3 font-semibold text-white transition hover:bg-stone-800" href="{{ route('admin.size-standards.index') }}">
            Manage size standards
        </a>
    </section>
    <div class="mt-6 flex flex-wrap gap-3"><a class="inline-flex rounded-full bg-stone-900 px-6 py-3 font-semibold text-white" href="{{ route('admin.categories.index') }}">Kelola Kategori</a><a class="inline-flex rounded-full bg-stone-900 px-6 py-3 font-semibold text-white" href="{{ route('admin.products.index') }}">Kelola Produk</a><a class="inline-flex rounded-full bg-stone-900 px-6 py-3 font-semibold text-white" href="{{ route('admin.banners.index') }}">Kelola hero banners</a></div>
</x-layouts.app>