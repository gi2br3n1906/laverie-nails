<div class="fixed inset-0 z-[80] hidden" id="cart-drawer" data-cart-drawer data-state-url="{{ route('cart.state') }}" data-store-url="{{ route('cart.store') }}" aria-hidden="true">
    <button class="absolute inset-0 cursor-default bg-[#081329]/45 opacity-0 backdrop-blur-sm transition-opacity duration-300" type="button" aria-label="Tutup keranjang" data-cart-drawer-close data-cart-drawer-backdrop></button>
    <aside class="absolute inset-y-0 right-0 flex w-full max-w-md translate-x-full flex-col bg-white text-[#0C1C39] shadow-2xl transition-transform duration-300 ease-out sm:max-w-lg" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" tabindex="-1" data-cart-drawer-panel>
        <header class="flex items-center justify-between border-b border-stone-200 px-5 py-5 sm:px-7">
            <h2 class="font-display text-2xl" id="cart-drawer-title">Cart (<span data-cart-drawer-quantity>0</span> items)</h2>
            <button class="grid size-10 place-items-center rounded-full transition hover:bg-stone-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0C1C39]" type="button" aria-label="Tutup keranjang" data-cart-drawer-close>
                <svg class="size-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
            </button>
        </header>

        <p class="hidden bg-stone-100 px-5 py-3 text-sm text-stone-700 sm:px-7" role="status" data-cart-drawer-status></p>
        <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-7">
            <div class="space-y-6" data-cart-drawer-items></div>
            <div class="grid min-h-52 place-items-center text-center" data-cart-drawer-empty>
                <div><p class="font-display text-2xl">Your cart is empty</p><a class="mt-4 inline-flex text-sm font-semibold underline underline-offset-4" href="{{ route('products.index') }}">Explore the collection</a></div>
            </div>

            <details class="mt-8 border-y border-stone-200 py-4" data-cart-drawer-notes>
                <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold uppercase tracking-[0.12em]">Add order notes <span aria-hidden="true">+</span></summary>
                <label class="sr-only" for="cart-order-note">Order notes</label>
                <textarea class="mt-4 min-h-24 w-full rounded-2xl border border-stone-300 bg-stone-50 px-4 py-3 text-sm outline-none focus:border-[#0C1C39] focus:ring-4 focus:ring-[#92A1B5]/20" id="cart-order-note" maxlength="500" placeholder="Special requests for your order" data-cart-order-note></textarea>
            </details>
        </div>

        <footer class="border-t border-stone-200 bg-white p-5 sm:p-7" data-cart-drawer-footer><div class="mb-4 flex items-center justify-between text-sm font-semibold"><span>Subtotal</span><span data-cart-drawer-subtotal>Rp 0</span></div>
            <a class="flex min-h-13 w-full items-center justify-center rounded-full bg-[#0C1C39] px-6 text-center text-xs font-semibold uppercase tracking-[0.14em] text-white transition hover:bg-[#192B48]" href="{{ route('checkout.create') }}" data-cart-drawer-checkout>CHECKOUT • <span class="ml-1" data-cart-drawer-total>Rp 0</span></a>
        </footer>
    </aside>
</div>