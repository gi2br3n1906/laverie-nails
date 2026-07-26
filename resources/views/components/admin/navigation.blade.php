<nav class="border-b border-stone-200 bg-white" aria-label="Navigasi admin" data-admin-navigation>
    <div class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:px-6 lg:px-8">
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.dashboard') }}">Dashboard</a>
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.orders.index') }}">Kelola Pesanan</a>
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.categories.index') }}">Kelola Kategori</a>
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.products.index') }}">Kelola Produk</a>
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.banners.index') }}">Hero Banners</a>
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.catalogs.index') }}">Katalog Size</a>
        <a class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-stone-100" href="{{ route('admin.size-standards.index') }}">Standar Ukuran</a>
    </div>
</nav>