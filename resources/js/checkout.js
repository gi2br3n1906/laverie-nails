const formatRupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value);

const replaceOptions = (select, placeholder, options = []) => {
    select.replaceChildren();
    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    select.append(placeholderOption);

    options.forEach((optionData) => {
        const option = document.createElement('option');
        option.value = optionData.id;
        option.textContent = optionData.name;
        select.append(option);
    });
};

const fetchData = async (url) => {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error('Gateway request failed.');
    }

    const payload = await response.json();

    return Array.isArray(payload.data) ? payload.data : [];
};

const initCheckout = (form) => {
    const provinceSelect = form.querySelector('[data-province-select]');
    const citySelect = form.querySelector('[data-city-select]');
    const shippingContainer = form.querySelector('[data-shipping-options]');
    const shippingTotal = form.querySelector('[data-shipping-total]');
    const grandTotal = form.querySelector('[data-grand-total]');
    const submitButton = form.querySelector('[data-place-order-button]');
    const shippingOptionInput = form.querySelector('[data-shipping-option-input]');
    const subtotal = Number.parseInt(form.dataset.subtotal ?? '0', 10);
    const oldShippingOption = shippingOptionInput?.value ?? '';
    const orderNotes = form.querySelector('[name="order_notes"]');

    if (orderNotes && orderNotes.value === '') {
        orderNotes.value = window.localStorage.getItem('laverie.cart.note') ?? '';
    }

    if (!provinceSelect || !citySelect || !shippingContainer || !shippingTotal || !grandTotal || !submitButton || !shippingOptionInput) {
        return;
    }

    const setTotals = (shippingCost = 0) => {
        shippingTotal.textContent = shippingCost > 0 ? formatRupiah(shippingCost) : 'Belum dipilih';
        grandTotal.textContent = formatRupiah(subtotal + shippingCost);
    };

    const showShippingMessage = (message) => {
        shippingContainer.replaceChildren();
        const paragraph = document.createElement('p');
        paragraph.className = 'rounded-2xl border border-dashed border-[#92A1B5]/60 px-5 py-6 text-sm text-stone-500';
        paragraph.textContent = message;
        shippingContainer.append(paragraph);
        shippingOptionInput.value = '';
        setTotals();
    };

    const renderShippingOptions = (options) => {
        shippingContainer.replaceChildren();

        if (options.length === 0) {
            showShippingMessage('Belum ada layanan pengiriman untuk tujuan ini.');
            return;
        }

        options.forEach((shippingOption, index) => {
            const label = document.createElement('label');
            label.className = 'flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-[#92A1B5]/50 bg-white px-5 py-4 transition hover:border-[#0C1C39] has-[:checked]:border-[#0C1C39] has-[:checked]:bg-[#EAF0F6]';

            const content = document.createElement('span');
            content.className = 'flex items-center gap-3';
            const input = document.createElement('input');
            input.className = 'size-4 accent-[#0C1C39]';
            input.type = 'radio';
            input.value = shippingOption.key;
            input.checked = shippingOption.key === oldShippingOption || (oldShippingOption === '' && index === 0);

            const description = document.createElement('span');
            const title = document.createElement('strong');
            title.className = 'block text-sm text-[#0C1C39]';
            title.textContent = `${shippingOption.courier_name} ${shippingOption.service}`;
            const details = document.createElement('span');
            details.className = 'mt-1 block text-xs text-stone-500';
            details.textContent = `${shippingOption.description} · Estimasi ${shippingOption.etd || '-'} hari`;
            description.append(title, details);

            const cost = document.createElement('strong');
            cost.className = 'shrink-0 text-sm text-[#0C1C39]';
            cost.textContent = formatRupiah(shippingOption.cost);
            content.append(input, description);
            label.append(content, cost);
            shippingContainer.append(label);

            input.addEventListener('change', () => {
                shippingOptionInput.value = shippingOption.key;
                setTotals(Number(shippingOption.cost));
            });

            if (input.checked) {
                shippingOptionInput.value = shippingOption.key;
                setTotals(Number(shippingOption.cost));
            }
        });
    };

    const loadShippingOptions = async () => {
        if (citySelect.value === '') {
            showShippingMessage('Pilih kota tujuan untuk melihat ongkos kirim.');
            return;
        }

        showShippingMessage('Menghitung pilihan pengiriman…');

        try {
            const url = new URL(form.dataset.shippingOptionsUrl, window.location.origin);
            url.searchParams.set('destination_id', citySelect.value);
            renderShippingOptions(await fetchData(url));
        } catch {
            showShippingMessage('Pilihan pengiriman belum dapat dimuat. Silakan coba lagi.');
        }
    };

    const loadCities = async () => {
        replaceOptions(citySelect, 'Memuat kota…');
        citySelect.disabled = true;
        showShippingMessage('Pilih kota tujuan untuk melihat ongkos kirim.');

        if (provinceSelect.value === '') {
            replaceOptions(citySelect, 'Pilih provinsi terlebih dahulu');
            return;
        }

        try {
            const url = new URL(form.dataset.citiesUrl, window.location.origin);
            url.searchParams.set('province_id', provinceSelect.value);
            replaceOptions(citySelect, 'Pilih kota / kabupaten', await fetchData(url));
            citySelect.disabled = false;

            if (citySelect.dataset.oldValue) {
                citySelect.value = citySelect.dataset.oldValue;
                citySelect.dataset.oldValue = '';
                await loadShippingOptions();
            }
        } catch {
            replaceOptions(citySelect, 'Kota gagal dimuat');
        }
    };

    provinceSelect.addEventListener('change', loadCities);
    citySelect.addEventListener('change', loadShippingOptions);
    form.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.textContent = 'Memproses pesanan…';
    });

    if (provinceSelect.value !== '') {
        loadCities();
    }
};

document.querySelectorAll('[data-checkout-form]').forEach(initCheckout);