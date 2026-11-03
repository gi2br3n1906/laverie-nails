<x-layouts.app title="Edit Promo">
    <a class="text-sm font-semibold" href="{{ route('admin.coupons.index') }}">← Kelola Promo</a><h1 class="mt-4 font-serif text-5xl font-semibold">Edit Promo</h1><p class="mt-4 text-stone-600">Perubahan hanya berlaku untuk checkout berikutnya; detail diskon pesanan lama tetap tersimpan.</p>
    @if ($errors->any())<div class="mt-6 rounded-2xl bg-red-50 px-5 py-4 text-red-800" role="alert">Periksa kembali isian promo.</div>@endif
    <x-admin.coupon-form :coupon="$coupon" />
</x-layouts.app>
