const initCheckout = (form) => {
    const submitButton = form.querySelector('[data-place-order-button]');
    const orderNotes = form.querySelector('[name="order_notes"]');

    if (orderNotes && orderNotes.value === '') {
        orderNotes.value = window.localStorage.getItem('laverie.cart.note') ?? '';
    }

    form.addEventListener('submit', () => {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Memproses pesanan…';
    });
};

document.querySelectorAll('[data-checkout-form]').forEach(initCheckout);