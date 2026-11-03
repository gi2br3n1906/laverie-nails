@props(['coupon' => null])

@php($editing = $coupon !== null)
<form class="mt-8 max-w-3xl rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8" method="POST" action="{{ $editing ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif
    <div class="grid gap-6 sm:grid-cols-2">
        <label class="block text-sm font-semibold text-stone-900 sm:col-span-2" for="code">Kode promo
            <input class="mt-2 min-h-12 w-full rounded-xl border border-stone-300 bg-stone-50 px-4 uppercase outline-none focus:border-stone-900 focus:ring-2 focus:ring-stone-200" id="code" name="code" maxlength="50" value="{{ old('code', $coupon?->code) }}" placeholder="HEMAT10" required>
            @error('code')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-stone-900" for="discount_type">Jenis diskon
            <select class="mt-2 min-h-12 w-full rounded-xl border border-stone-300 bg-stone-50 px-4" id="discount_type" name="discount_type" required><option value="percentage" @selected(old('discount_type', $coupon?->discount_type ?? 'percentage') === 'percentage')>Persentase (%)</option><option value="fixed" @selected(old('discount_type', $coupon?->discount_type) === 'fixed')>Nominal tetap (Rp)</option></select>
            @error('discount_type')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-stone-900" for="discount_percentage">Diskon persen (%)
            <input class="mt-2 min-h-12 w-full rounded-xl border border-stone-300 bg-stone-50 px-4" id="discount_percentage" name="discount_percentage" type="number" min="1" max="100" value="{{ old('discount_percentage', $coupon?->discount_percentage ?: '') }}">
            @error('discount_percentage')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-stone-900" for="discount_amount">Diskon nominal (Rp)
            <input class="mt-2 min-h-12 w-full rounded-xl border border-stone-300 bg-stone-50 px-4" id="discount_amount" name="discount_amount" type="number" min="1" max="100000000" value="{{ old('discount_amount', $coupon?->discount_amount ?: '') }}">
            @error('discount_amount')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-stone-900" for="minimum_order_amount">Minimum belanja (Rp)
            <input class="mt-2 min-h-12 w-full rounded-xl border border-stone-300 bg-stone-50 px-4" id="minimum_order_amount" name="minimum_order_amount" type="number" min="0" max="100000000" value="{{ old('minimum_order_amount', $coupon?->minimum_order_amount ?? 0) }}" required>
            @error('minimum_order_amount')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </label>
        <label class="block text-sm font-semibold text-stone-900" for="expires_at">Berlaku sampai (opsional)
            <input class="mt-2 min-h-12 w-full rounded-xl border border-stone-300 bg-stone-50 px-4" id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at', $coupon?->expires_at?->format('Y-m-d\\TH:i')) }}">
            @error('expires_at')<span class="mt-2 block text-sm font-semibold text-red-700">{{ $message }}</span>@enderror
        </label>
        <input name="is_active" type="hidden" value="0"><label class="flex items-center gap-3 text-sm font-semibold text-stone-900 sm:col-span-2"><input class="h-5 w-5 rounded border-stone-300" name="is_active" type="checkbox" value="1" @checked(old('is_active', $coupon?->is_active ?? true))> Promo aktif</label>
    </div>
    <div class="mt-8 flex flex-wrap gap-3"><button class="rounded-full bg-stone-900 px-6 py-3 text-sm font-semibold text-white" type="submit">{{ $editing ? 'Simpan perubahan' : 'Buat promo' }}</button><a class="rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold" href="{{ route('admin.coupons.index') }}">Batal</a></div>
</form>
@push('scripts')
<script>
    (() => {
        const type = document.getElementById('discount_type');
        const percentage = document.getElementById('discount_percentage');
        const amount = document.getElementById('discount_amount');
        const sync = () => {
            const isPercentage = type.value === 'percentage';
            percentage.required = isPercentage;
            percentage.disabled = !isPercentage;
            amount.required = !isPercentage;
            amount.disabled = isPercentage;
        };
        type.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
