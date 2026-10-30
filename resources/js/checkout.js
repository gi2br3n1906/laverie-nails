import { t } from './i18n';

const initShippingEstimate = (form) => {
    const province = form.querySelector('[name="province_code"]');
    const costTarget = form.querySelector('[data-shipping-cost]');
    const totalTarget = form.querySelector('[data-order-total]');

    if (!province || !costTarget || !totalTarget) {
        return;
    }

    const base = Number(form.dataset.orderSubtotal ?? 0) - Number(form.dataset.orderDiscount ?? 0);
    const formatter = new Intl.NumberFormat('id-ID');

    let rates = {};

    try {
        rates = JSON.parse(form.dataset.shippingRates ?? '{}');
    } catch {
        rates = {};
    }

    let javaProvinces = new Set();

    try {
        javaProvinces = new Set(JSON.parse(form.dataset.javaProvinces ?? '[]'));
    } catch {
        javaProvinces = new Set();
    }

    const update = () => {
        const provinceCode = province.value;

        if (provinceCode === '') {
            costTarget.textContent = t('checkout.select_province');
            totalTarget.textContent = `Rp ${formatter.format(base)}`;

            return;
        }

        const cost = Number(javaProvinces.has(provinceCode) ? rates.java : rates.outside_java) || 0;

        costTarget.textContent = `Rp ${formatter.format(cost)}`;
        totalTarget.textContent = `Rp ${formatter.format(base + cost)}`;
    };

    form.addEventListener('region:change', (event) => {
        if (event.detail?.level === 'province') {
            update();
        }
    });

    update();
};

const initCheckout = (form) => {
    const submitButton = form.querySelector('[data-place-order-button]');
    const orderNotes = form.querySelector('[name="order_notes"]');

    if (orderNotes && orderNotes.value === '') {
        orderNotes.value = window.localStorage.getItem('laverie.cart.note') ?? '';
    }

    initShippingEstimate(form);

    form.addEventListener('submit', () => {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = t('checkout.processing');
    });
};

document.querySelectorAll('[data-checkout-form]').forEach(initCheckout);