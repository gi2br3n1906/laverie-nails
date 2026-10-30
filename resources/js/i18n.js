const translations = () => {
    if (window.LaverieI18n) {
        return window.LaverieI18n;
    }

    const meta = document.querySelector('meta[name="laverie-i18n"]');

    if (!meta) {
        return {};
    }

    try {
        window.LaverieI18n = JSON.parse(meta.content);

        return window.LaverieI18n;
    } catch {
        return {};
    }
};

const lookup = (key) => key
    .split('.')
    .reduce((value, segment) => (value && typeof value === 'object' ? value[segment] : undefined), translations());

export const t = (key, replacements = {}) => {
    const value = lookup(key);

    if (typeof value !== 'string') {
        return key;
    }

    return Object.entries(replacements).reduce(
        (text, [name, replacement]) => text.replaceAll(`{${name}}`, String(replacement)),
        value,
    );
};
