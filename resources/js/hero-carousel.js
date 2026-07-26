const initializeHeroCarousel = (carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-hero-slide]'));
    const indicators = Array.from(carousel.querySelectorAll('[data-hero-indicator]'));

    if (slides.length < 2 || slides.length !== indicators.length) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let activeIndex = 0;
    let autoplayTimer = null;

    const showSlide = (nextIndex) => {
        activeIndex = (nextIndex + slides.length) % slides.length;

        slides.forEach((slide, index) => {
            const isActive = index === activeIndex;
            slide.classList.toggle('opacity-100', isActive);
            slide.classList.toggle('opacity-0', ! isActive);
            slide.classList.toggle('pointer-events-none', ! isActive);
            slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
        });

        indicators.forEach((indicator, index) => {
            const isActive = index === activeIndex;
            indicator.classList.toggle('bg-white', isActive);
            indicator.classList.toggle('bg-white/30', ! isActive);
            indicator.setAttribute('aria-current', isActive ? 'true' : 'false');
        });
    };

    const stopAutoplay = () => {
        window.clearInterval(autoplayTimer);
        autoplayTimer = null;
    };

    const startAutoplay = () => {
        if (reduceMotion || autoplayTimer !== null) {
            return;
        }

        autoplayTimer = window.setInterval(() => showSlide(activeIndex + 1), 6000);
    };

    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            showSlide(index);
            stopAutoplay();
            startAutoplay();
        });
    });

    carousel.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
            return;
        }

        event.preventDefault();
        showSlide(activeIndex + (event.key === 'ArrowRight' ? 1 : -1));
    });

    carousel.addEventListener('mouseenter', stopAutoplay);
    carousel.addEventListener('mouseleave', startAutoplay);
    carousel.addEventListener('focusin', stopAutoplay);
    carousel.addEventListener('focusout', startAutoplay);
    document.addEventListener('visibilitychange', () => document.hidden ? stopAutoplay() : startAutoplay());

    showSlide(0);
    startAutoplay();
};

document.querySelectorAll('[data-hero-carousel]').forEach(initializeHeroCarousel);