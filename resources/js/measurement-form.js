const setupMeasurementForm = (root) => {
    const form = root.querySelector('[data-measurement-form-element]');
    const toggle = root.querySelector('[data-hand-toggle]');
    const leftPanel = root.querySelector('[data-left-hand-panel]');
    const leftInputs = [...leftPanel.querySelectorAll('[data-measurement-input]')];
    const summary = root.querySelector('[data-validation-summary]');

    if (!form || !toggle || !leftPanel || !summary) return;

    const syncLeftHand = () => {
        const isVisible = toggle.checked;
        leftPanel.classList.toggle('grid-rows-[1fr]', isVisible);
        leftPanel.classList.toggle('grid-rows-[0fr]', !isVisible);
        leftPanel.classList.toggle('opacity-100', isVisible);
        leftPanel.classList.toggle('opacity-0', !isVisible);
        leftPanel.setAttribute('aria-hidden', String(!isVisible));
        leftInputs.forEach((input) => {
            input.required = isVisible;
            input.disabled = !isVisible;
            if (!isVisible) input.setCustomValidity('');
        });
    };

    const validateInput = (input) => {
        input.setCustomValidity('');
        if (input.value === '' && input.required) input.setCustomValidity('Nilai pengukuran wajib diisi.');
        if (input.value !== '' && (Number(input.value) < 0 || Number(input.value) > 25)) input.setCustomValidity('Masukkan angka antara 0 dan 25 mm.');
        return input.checkValidity();
    };

    toggle.addEventListener('change', syncLeftHand);
    form.addEventListener('input', (event) => {
        if (event.target.matches('[data-measurement-input]')) validateInput(event.target);
    });
    form.addEventListener('submit', (event) => {
        const inputs = [...form.querySelectorAll('[data-measurement-input]:not(:disabled)')];
        const isValid = inputs.every(validateInput);
        summary.hidden = isValid;
        summary.textContent = isValid ? '' : 'Periksa kembali semua ukuran. Setiap nilai harus berada pada rentang 0–25 mm.';
        if (!isValid) {
            event.preventDefault();
            form.querySelector(':invalid')?.focus();
        }
    });

    syncLeftHand();
};

document.querySelectorAll('[data-measurement-form]').forEach(setupMeasurementForm);