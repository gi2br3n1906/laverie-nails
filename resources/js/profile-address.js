const replaceProfileCities = (select, label, cities = []) => {
    select.replaceChildren();
    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = label;
    select.append(placeholder);

    cities.forEach((city) => {
        const option = document.createElement('option');
        option.value = city.id;
        option.textContent = city.name;
        select.append(option);
    });
};

const initProfileAddress = (form) => {
    const province = form.querySelector('[data-profile-province]');
    const city = form.querySelector('[data-profile-city]');
    if (!province || !city) return;

    const loadCities = async () => {
        city.disabled = true;
        replaceProfileCities(city, province.value ? 'Memuat kota…' : 'Pilih provinsi terlebih dahulu');
        if (!province.value) return;

        try {
            const url = new URL(form.dataset.citiesUrl, window.location.origin);
            url.searchParams.set('province_id', province.value);
            const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error('City lookup failed.');
            const payload = await response.json();
            replaceProfileCities(city, 'Pilih kota / kabupaten', Array.isArray(payload.data) ? payload.data : []);
            city.disabled = false;
            if (city.dataset.oldValue) city.value = city.dataset.oldValue;
        } catch {
            replaceProfileCities(city, 'Kota gagal dimuat');
        }
    };

    province.addEventListener('change', () => {
        city.dataset.oldValue = '';
        loadCities();
    });
    if (province.value) loadCities();
};

document.querySelectorAll('[data-profile-address-form]').forEach(initProfileAddress);