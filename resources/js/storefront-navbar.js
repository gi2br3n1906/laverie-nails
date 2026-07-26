const overlayNavbar = document.querySelector('[data-overlay-navigation="true"]');

if (overlayNavbar) {
    const synchronizeNavbar = () => {
        overlayNavbar.dataset.navbarScrolled = window.scrollY > 24 ? 'true' : 'false';
    };

    synchronizeNavbar();
    window.addEventListener('scroll', synchronizeNavbar, { passive: true });
}