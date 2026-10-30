import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import { t } from './i18n';

const LEVELS = ['province', 'regency', 'district', 'village'];

// Keep a dropdown's wheel/touch scrolling from chaining to the page behind it.
const containDropdownScroll = (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const dropdown = event.target.closest('.ts-dropdown');

    if (!dropdown) {
        return;
    }

    const content = dropdown.querySelector('.ts-dropdown-content');

    if (!content) {
        event.preventDefault();

        return;
    }

    const atTop = content.scrollTop <= 0;
    const atBottom = Math.ceil(content.scrollTop + content.clientHeight) >= content.scrollHeight;
    const scrollingUp = event.deltaY < 0;
    const scrollingDown = event.deltaY > 0;

    if ((scrollingUp && atTop) || (scrollingDown && atBottom)) {
        event.preventDefault();
    }
};

document.addEventListener('wheel', containDropdownScroll, { passive: false });

const PLACEHOLDERS = {
    province: t('region.select_province'),
    regency: t('region.select_regency'),
    district: t('region.select_district'),
    village: t('region.select_village'),
};

const initializeRegionForm = (form) => {
    const selects = {};
    const controls = {};
    const names = {};
    const controllers = {};

    LEVELS.forEach((level) => {
        const select = form.querySelector(`select[name="${level}_code"]`);

        if (!select) {
            return;
        }

        selects[level] = select;
        names[level] = new Map(
            Array.from(select.options)
                .filter((option) => option.value !== '')
                .map((option) => [option.value, option.textContent.trim()]),
        );
    });

    if (!selects.province) {
        return;
    }

    const feedbackFor = (level) => {
        const select = selects[level];

        return select?.closest('div')?.querySelector('[data-region-feedback]') ?? null;
    };

    const showFeedback = (level, message, isError = false) => {
        const feedback = feedbackFor(level);

        if (!feedback) {
            return;
        }

        feedback.textContent = message;
        feedback.classList.remove('hidden');
        feedback.classList.toggle('text-[#385273]', isError);
        feedback.classList.toggle('text-stone-500', !isError);
    };

    const clearFeedback = (level) => {
        const feedback = feedbackFor(level);

        if (!feedback) {
            return;
        }

        feedback.textContent = '';
        feedback.classList.add('hidden');
    };

    const setHiddenName = (level, value) => {
        const select = selects[level];
        const hidden = form.querySelector(`input[data-region-name="${select.name}"]`);

        if (!hidden) {
            return;
        }

        hidden.value = value === '' ? '' : (names[level].get(value) ?? '');
    };

    const setDisabled = (level, disabled) => {
        const control = controls[level];

        if (!control) {
            return;
        }

        if (disabled) {
            control.disable();
            selects[level].disabled = true;
        } else {
            control.enable();
            selects[level].disabled = false;
        }
    };

    const resetLevel = (level) => {
        if (!selects[level] || !controls[level]) {
            return;
        }

        controls[level].clear(true);
        setHiddenName(level, '');
        setDisabled(level, true);
        clearFeedback(level);
    };

    const resetChildren = (level) => {
        const index = LEVELS.indexOf(level);

        LEVELS.slice(index + 1).forEach(resetLevel);
    };

    const fillOptions = (level, options, selectedValue = null) => {
        const control = controls[level];
        const select = selects[level];

        if (!control || !select) {
            return;
        }

        control.clearOptions();
        names[level] = new Map();

        options.forEach((option) => {
            if (!option || typeof option.code !== 'string' || typeof option.name !== 'string') {
                return;
            }

            names[level].set(option.code, option.name);
            control.addOption({ value: option.code, text: option.name });
        });

        if (selectedValue && names[level].has(selectedValue)) {
            control.setValue(selectedValue, true);
            setHiddenName(level, selectedValue);
        } else {
            control.setValue('', true);
            setHiddenName(level, '');
        }

        const hasOptions = options.length > 0;
        setDisabled(level, !hasOptions);

        if (!hasOptions) {
            showFeedback(level, t('region.unavailable'), true);
        } else {
            clearFeedback(level);
        }
    };

    const loadOptions = async (level, url, selectedValue = null) => {
        if (!selects[level] || !controls[level] || !url) {
            return false;
        }

        controllers[level]?.abort();
        controllers[level] = new AbortController();

        showFeedback(level, t('region.loading'));
        setDisabled(level, true);

        try {
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: controllers[level].signal,
            });

            if (!response.ok) {
                let message = t('region.load_failed');

                try {
                    const payload = await response.json();
                    message = payload.message ?? message;
                } catch {
                    // Keep the generic message when the response is not JSON.
                }

                throw new Error(message);
            }

            const payload = await response.json();
            fillOptions(level, Array.isArray(payload.data) ? payload.data : [], selectedValue);

            return true;
        } catch (error) {
            if (error.name === 'AbortError') {
                return false;
            }

            showFeedback(level, error.message || t('region.load_failed'), true);
            setDisabled(level, true);

            return false;
        }
    };

    const childUrl = (level, value) => {
        const template = selects[level]?.dataset.childUrl;

        return template ? template.replace('__CODE__', encodeURIComponent(value)) : null;
    };

    const handleChange = (level, value) => {
        setHiddenName(level, value);
        clearFeedback(level);
        resetChildren(level);

        form.dispatchEvent(new CustomEvent('region:change', {
            bubbles: true,
            detail: {
                level,
                value,
                name: value === '' ? '' : (names[level].get(value) ?? ''),
            },
        }));

        if (value === '') {
            return;
        }

        const nextLevel = LEVELS[LEVELS.indexOf(level) + 1];

        if (!nextLevel || !selects[nextLevel]) {
            return;
        }

        const url = childUrl(level, value);

        if (url) {
            void loadOptions(nextLevel, url);
        }
    };

    LEVELS.forEach((level) => {
        const select = selects[level];

        if (!select) {
            return;
        }

        const control = new TomSelect(select, {
            allowEmptyOption: true,
            create: false,
            maxItems: 1,
            placeholder: PLACEHOLDERS[level],
            sortField: { field: 'text', direction: 'asc' },
            onChange: (value) => handleChange(level, value),
        });

        // The empty <option> exists as a no-JS fallback, but it should never
        // appear as a selectable row inside the Tom Select dropdown.
        control.removeOption('', true);

        controls[level] = control;
    });

    const bootstrap = async () => {
        const province = selects.province.value;
        const regency = selects.regency?.value ?? '';
        const district = selects.district?.value ?? '';
        const village = selects.village?.value ?? '';

        if (!province) {
            return;
        }

        const regenciesLoaded = await loadOptions('regency', childUrl('province', province), regency);

        if (!regenciesLoaded || !regency) {
            return;
        }

        const districtsLoaded = await loadOptions('district', childUrl('regency', regency), district);

        if (!districtsLoaded || !district) {
            return;
        }

        await loadOptions('village', childUrl('district', district), village);
    };

    void bootstrap();

    form.addEventListener('submit', (event) => {
        const missing = LEVELS.find((level) => selects[level] && selects[level].value === '');

        if (!missing) {
            return;
        }

        event.preventDefault();
        showFeedback(missing, t('region.complete'), true);
        controls[missing]?.focus();
    });
};

const boot = () => {
    document.querySelectorAll('[data-region-form]').forEach(initializeRegionForm);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
