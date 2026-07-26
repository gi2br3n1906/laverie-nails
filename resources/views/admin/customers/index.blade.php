<x-layouts.app title="Kelola Pelanggan">
    <x-admin.navigation />
    <section class="rounded-[2rem] border border-[#92A1B5]/40 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="customers-heading">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#60738C]">Customer intelligence</p>
        <h1 class="mt-2 font-display text-4xl text-[#0C1C39]" id="customers-heading">Kelola Pelanggan</h1>
        <p class="mt-3 text-sm text-stone-600">Ringkasan akun terdaftar dan nilai transaksi yang sudah dibayar.</p>
        <div class="mt-8 overflow-x-auto rounded-2xl border border-[#92A1B5]/40">
            <table class="min-w-full divide-y divide-[#92A1B5]/40 text-left text-sm">
                <thead class="bg-[#EAF0F6] text-xs uppercase tracking-[0.1em] text-[#385273]"><tr><th class="px-5 py-4">Pelanggan</th><th class="px-5 py-4">Terdaftar</th><th class="px-5 py-4">Total Pesanan</th><th class="px-5 py-4">Total Belanja</th></tr></thead>
                <tbody class="divide-y divide-[#92A1B5]/30 bg-white">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-5 py-4"><p class="font-semibold text-[#0C1C39]">{{ $customer->name }}</p><p class="mt-1 text-xs text-stone-500">{{ $customer->email }}</p></td>
                            <td class="whitespace-nowrap px-5 py-4 text-stone-600">{{ $customer->created_at->translatedFormat('d M Y') }}</td>
                            <td class="whitespace-nowrap px-5 py-4 font-semibold">{{ $customer->orders_count }} pesanan</td>
                            <td class="whitespace-nowrap px-5 py-4 font-bold text-[#0C1C39]">Rp {{ number_format((int) ($customer->total_spent ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-10 text-center text-stone-500" colspan="4">Belum ada pelanggan terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $customers->links() }}</div>
    </section>
</x-layouts.app>