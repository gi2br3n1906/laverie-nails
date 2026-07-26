<x-layouts.app title="Kelola Pesanan">
    <section aria-labelledby="orders-heading">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#60738C]">Order operations</p>
                <h1 class="mt-3 font-serif text-4xl font-semibold text-[#0C1C39] sm:text-5xl" id="orders-heading">Kelola Pesanan</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Pantau pembayaran dan progres pengerjaan setiap press-on nail set.</p>
            </div>

            <form class="flex w-full flex-col gap-3 rounded-2xl border border-[#92A1B5]/40 bg-white p-4 sm:w-auto sm:flex-row sm:items-end" action="{{ route('admin.orders.index') }}" method="GET">
                <div>
                    <label class="text-xs font-bold uppercase tracking-[0.12em] text-[#60738C]" for="payment_status">Payment status</label>
                    <select class="mt-2 block min-h-11 min-w-52 rounded-xl border border-[#92A1B5]/60 bg-white px-4 text-sm text-[#0C1C39]" id="payment_status" name="payment_status">
                        <option value="">Semua pembayaran</option>
                        @foreach ($paymentStatuses as $status)
                            <option value="{{ $status->value }}" @selected($selectedPaymentStatus === $status->value)>{{ ucfirst($status->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="min-h-11 rounded-full bg-[#0C1C39] px-6 text-xs font-bold uppercase tracking-[0.12em] text-white transition hover:bg-[#192B48]" type="submit">Filter</button>
            </form>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-[#92A1B5]/50 bg-[#EAF0F6] px-5 py-4 text-sm text-[#0C1C39]" role="alert">Filter pembayaran tidak valid.</div>
        @endif

        <div class="mt-9 overflow-hidden rounded-3xl border border-[#92A1B5]/35 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#92A1B5]/25 text-left text-sm">
                    <thead class="bg-[#EAF0F6] text-xs uppercase tracking-[0.1em] text-[#60738C]">
                        <tr>
                            <th class="px-5 py-4">Order ID</th>
                            <th class="px-5 py-4">Customer</th>
                            <th class="px-5 py-4">Grand Total</th>
                            <th class="px-5 py-4">Payment</th>
                            <th class="px-5 py-4">Fulfillment</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#92A1B5]/20">
                        @forelse ($orders as $order)
                            <tr class="transition hover:bg-[#F8FAFC]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-[#0C1C39]">{{ $order->id }}</p>
                                    <p class="mt-1 text-xs text-stone-400">{{ $order->created_at?->format('d M Y · H:i') }}</p>
                                </td>
                                <td class="px-5 py-4 font-medium text-stone-700">{{ $order->customer_name }}</td>
                                <td class="px-5 py-4 font-semibold text-[#0C1C39]">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-[#EAF0F6] px-3 py-1.5 text-xs font-bold uppercase tracking-[0.08em] text-[#0C1C39]">{{ $order->payment_status->value }}</span></td>
                                <td class="px-5 py-4"><span class="rounded-full border border-[#92A1B5]/50 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.08em] text-[#60738C]">{{ $order->fulfillment_status->value }}</span></td>
                                <td class="px-5 py-4"><a class="font-semibold text-[#0C1C39] underline decoration-[#92A1B5] underline-offset-4" href="{{ route('admin.orders.show', $order) }}">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-12 text-center text-stone-500" colspan="6">Belum ada pesanan dengan filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </section>
</x-layouts.app>