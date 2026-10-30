const overlayNavbar = document.querySelector('[data-overlay-navigation="true"]');

if (overlayNavbar) {
    const synchronizeNavbar = () => {
        overlayNavbar.dataset.navbarScrolled = window.scrollY > 0 ? 'true' : 'false';
    };

    synchronizeNavbar();
    window.addEventListener('scroll', synchronizeNavbar, { passive: true });
}

const searchForm = document.querySelector('[data-navbar-search-form]');

if (searchForm) {
    const panel = searchForm.querySelector('[data-navbar-search-panel]');
    const trigger = searchForm.querySelector('[data-navbar-search-button]');
    const input = searchForm.querySelector('[data-navbar-search-input]');

    const setSearchOpen = (open) => {
        panel?.classList.toggle('hidden', !open);
        trigger?.setAttribute('aria-expanded', String(open));

        if (open) {
            input?.focus();
        }
    };

    trigger?.addEventListener('click', () => {
        const catalogInput = document.querySelector('[data-products-search] input[name="search"]');

        if (catalogInput) {
            catalogInput.scrollIntoView({ behavior: 'auto', block: 'center' });
            catalogInput.focus({ preventScroll: true });
            setSearchOpen(false);

            return;
        }

        setSearchOpen(panel?.classList.contains('hidden') ?? false);
    });

    document.addEventListener('click', (event) => {
        if (!searchForm.contains(event.target)) {
            setSearchOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setSearchOpen(false);
        }
    });
}