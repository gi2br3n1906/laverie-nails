const form = document.querySelector('[data-add-to-cart-form]');

if (form) {
    const panels = {
        standard: form.querySelector('[data-size-panel="standard"]'),
        custom: form.querySelector('[data-size-panel="custom"]'),
    };

    const toggle = (selected) => {
        Object.entries(panels).forEach(([sizeType, panel]) => {
            if (!panel) {
                return;
            }

            const active = sizeType === selected;
            panel.hidden = !active;
            panel.querySelectorAll('input').forEach((input) => {
                input.disabled = !active;
            });
        });
    };

    const current = form.querySelector('input[name="size_type"]:checked')?.value ?? 'standard';

    toggle(current);

    form.querySelectorAll('input[name="size_type"]').forEach((radio) => {
        radio.addEventListener('change', () => toggle(radio.value));
    });
}
