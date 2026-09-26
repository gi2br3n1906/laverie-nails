const drawer = document.querySelector('[data-cart-drawer]');

if (drawer) {
    const panel = drawer.querySelector('[data-cart-drawer-panel]');
    const backdrop = drawer.querySelector('[data-cart-drawer-backdrop]');
    const itemsContainer = drawer.querySelector('[data-cart-drawer-items]');
    const emptyState = drawer.querySelector('[data-cart-drawer-empty]');
    const quantityLabel = drawer.querySelector('[data-cart-drawer-quantity]');
    const totalLabel = drawer.querySelector('[data-cart-drawer-total]');
    const subtotalLabel = drawer.querySelector('[data-cart-drawer-subtotal]');
    const checkout = drawer.querySelector('[data-cart-drawer-checkout]');
    const status = drawer.querySelector('[data-cart-drawer-status]');
    const note = drawer.querySelector('[data-cart-order-note]');
    const couponForm = drawer.querySelector('[data-cart-coupon-form]');
    const couponInputRow = drawer.querySelector('[data-cart-coupon-input-row]');
    const couponInput = drawer.querySelector('[data-cart-coupon-code]');
    const couponApplied = drawer.querySelector('[data-cart-coupon-applied]');
    const couponLabel = drawer.querySelector('[data-cart-coupon-label]');
    const couponPercent = drawer.querySelector('[data-cart-coupon-percent]');
    const couponRemoveButton = drawer.querySelector('[data-cart-coupon-remove]');
    const discountRow = drawer.querySelector('[data-cart-drawer-discount-row]');
    const discountLabel = drawer.querySelector('[data-cart-drawer-discount]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let lastFocusedElement = null;

    const formatRupiah = (cents) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.round(cents / 100))}`;

    const element = (tag, classes, text = '') => {
        const node = document.createElement(tag);
        node.className = classes;
        node.textContent = text;
        return node;
    };

    const showMessage = (message, isError = false) => {
        status.textContent = message;
        status.classList.remove('hidden', 'bg-stone-100', 'text-stone-700', 'bg-[#EAF0F6]', 'text-[#0C1C39]');
        status.classList.add(isError ? 'bg-[#EAF0F6]' : 'bg-stone-100', isError ? 'text-[#0C1C39]' : 'text-stone-700');
    };

    const createItem = (item) => {
        const article = element('article', 'grid grid-cols-[5.5rem_1fr] gap-4 border-b border-stone-200 pb-6');
        const media = element('a', 'block aspect-[4/5] overflow-hidden rounded-2xl bg-[#EAF0F6]');
        media.href = item.product_url;

        if (item.image_url) {
            const image = document.createElement('img');
            image.className = 'size-full object-cover';
            image.src = item.image_url;
            image.alt = item.name;
            media.append(image);
        } else {
            media.append(element('span', 'grid size-full place-items-center text-[0.6rem] font-semibold uppercase tracking-[0.12em] text-stone-400', 'No image'));
        }

        const details = element('div', 'min-w-0');
        const titleRow = element('div', 'flex items-start gap-3');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'mt-1.5 size-4 shrink-0 accent-[#0C1C39]';
        checkbox.checked = item.is_selected !== false;
        checkbox.dataset.cartAction = 'selection';
        checkbox.dataset.url = item.selection_url;
        checkbox.setAttribute('aria-label', `Pilih ${item.name} untuk checkout`);
        const title = element('a', 'font-display text-lg leading-tight');
        title.href = item.product_url;
        title.textContent = item.name;
        titleRow.append(checkbox, title);

        const badges = element('div', 'mt-2 flex flex-wrap gap-1.5');
        badges.append(element('span', 'rounded-full bg-stone-100 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-stone-600', item.size_label));
        if (item.length_label) {
            badges.append(element('span', 'rounded-full bg-stone-100 px-2.5 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-stone-600', item.length_label));
        }
        details.append(titleRow, badges);
        details.append(element('p', 'mt-2 text-sm font-semibold', formatRupiah(item.unit_price)));

        const actions = element('div', 'mt-4 flex items-center justify-between gap-3');
        const selector = element('div', 'inline-grid grid-cols-3 items-center rounded-full border border-stone-300');

        ['decrease', 'quantity', 'increase'].forEach((action) => {
            if (action === 'quantity') {
                const count = element('span', 'min-w-8 text-center text-sm font-semibold', String(item.quantity));
                count.setAttribute('aria-live', 'polite');
                selector.append(count);
                return;
            }

            const button = element('button', 'grid size-9 place-items-center text-lg transition hover:bg-stone-100 disabled:text-stone-300', action === 'decrease' ? '−' : '+');
            button.type = 'button';
            button.dataset.cartAction = action;
            button.dataset.url = item.update_url;
            button.dataset.quantity = String(action === 'decrease' ? item.quantity - 1 : item.quantity + 1);
            button.disabled = action === 'decrease' ? item.quantity <= 1 : item.quantity >= item.max_quantity;
            button.setAttribute('aria-label', `${action === 'decrease' ? 'Kurangi' : 'Tambah'} jumlah ${item.name}`);
            selector.append(button);
        });

        const remove = element('button', 'text-xs font-semibold underline underline-offset-4', 'Remove');
        remove.type = 'button';
        remove.dataset.cartAction = 'remove';
        remove.dataset.url = item.remove_url;
        remove.setAttribute('aria-label', `Hapus ${item.name}`);
        actions.append(selector, remove);
        details.append(actions);
        article.append(media, details);

        return article;
    };

    const render = (state) => {
        itemsContainer.replaceChildren(...state.items.map(createItem));
        emptyState.classList.toggle('hidden', state.items.length > 0);
        itemsContainer.classList.toggle('hidden', state.items.length === 0);
        quantityLabel.textContent = String(state.quantity);
        totalLabel.textContent = formatRupiah(state.total);
        subtotalLabel.textContent = formatRupiah(state.subtotal ?? state.total);

        const hasCoupon = Boolean(state.coupon);
        discountRow.classList.toggle('hidden', !hasCoupon);
        discountRow.classList.toggle('flex', hasCoupon);
        if (hasCoupon) discountLabel.textContent = formatRupiah(state.discount);
        couponApplied.classList.toggle('hidden', !hasCoupon);
        couponApplied.classList.toggle('flex', hasCoupon);
        couponInputRow.classList.toggle('hidden', hasCoupon);
        if (hasCoupon) {
            couponLabel.textContent = state.coupon.code;
            couponPercent.textContent = state.coupon.discount_label;
        } else {
            couponInput.value = '';
        }
        checkout.classList.toggle('pointer-events-none', state.items.length === 0);
        checkout.classList.toggle('opacity-50', state.items.length === 0);
        checkout.setAttribute('aria-disabled', state.items.length === 0 ? 'true' : 'false');

        document.querySelectorAll('[data-cart-count]').forEach((badge) => {
            badge.textContent = String(state.quantity);
            badge.classList.toggle('hidden', state.quantity < 1);
            badge.classList.toggle('grid', state.quantity > 0);
        });
        document.querySelectorAll('[data-cart-count-label]').forEach((label) => {
            label.textContent = `${state.quantity} item`;
        });
    };

    const request = async (url, options = {}) => {
        const response = await fetch(url, {
            ...options,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers ?? {}),
            },
        });
        const payload = await response.json();

        if (!response.ok) {
            const validationMessage = Object.values(payload.errors ?? {}).flat()[0];
            throw new Error(validationMessage ?? payload.message ?? 'Keranjang tidak dapat diperbarui.');
        }

        render(payload.data);
        if (payload.message) showMessage(payload.message);
        return payload.data;
    };

    const open = async (trigger) => {
        lastFocusedElement = trigger;
        drawer.classList.remove('hidden');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            backdrop.classList.remove('opacity-0');
        });
        panel.focus();

        try {
            await request(drawer.dataset.stateUrl);
        } catch (error) {
            showMessage(error.message, true);
        }
    };

    const close = () => {
        panel.classList.add('translate-x-full');
        backdrop.classList.add('opacity-0');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        window.setTimeout(() => drawer.classList.add('hidden'), 300);
        lastFocusedElement?.focus();
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-cart-drawer-trigger]');
        if (!trigger) return;
        event.preventDefault();
        open(trigger);
    });

    drawer.querySelectorAll('[data-cart-drawer-close]').forEach((button) => button.addEventListener('click', close));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && drawer.getAttribute('aria-hidden') === 'false') close();
    });

    couponForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const code = couponInput.value.trim();
        if (!code) return;

        try {
            await request(drawer.dataset.couponApplyUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({code}),
            });
        } catch (error) {
            showMessage(error.message, true);
        }
    });

    couponRemoveButton.addEventListener('click', async () => {
        try {
            await request(drawer.dataset.couponRemoveUrl, {method: 'DELETE'});
        } catch (error) {
            showMessage(error.message, true);
        }
    });

    itemsContainer.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-cart-action]');
        if (!button) return;

        if (button.dataset.cartAction === 'selection') {
            try {
                await request(button.dataset.url, {
                    method: 'PATCH',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({is_selected: button.checked}),
                });
            } catch (error) {
                button.checked = !button.checked;
                showMessage(error.message, true);
            }
            return;
        }

        button.disabled = true;

        try {
            const remove = button.dataset.cartAction === 'remove';
            await request(button.dataset.url, {
                method: remove ? 'DELETE' : 'PATCH',
                headers: remove ? {} : {'Content-Type': 'application/json'},
                body: remove ? null : JSON.stringify({quantity: Number(button.dataset.quantity)}),
            });
        } catch (error) {
            showMessage(error.message, true);
            button.disabled = false;
        }
    });

    document.querySelectorAll('[data-add-to-cart-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submit = form.querySelector('[type="submit"]');
            submit.disabled = true;

            try {
                await request(form.action, {method: 'POST', body: new FormData(form)});
                await open(submit);
            } catch (error) {
                showMessage(error.message, true);
                await open(submit);
            } finally {
                submit.disabled = false;
            }
        });
    });

    if (note) {
        note.value = window.localStorage.getItem('laverie.cart.note') ?? '';
        note.addEventListener('input', () => window.localStorage.setItem('laverie.cart.note', note.value));
    }
}