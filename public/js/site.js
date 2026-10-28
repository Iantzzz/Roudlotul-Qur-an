(() => {
    const header = document.querySelector('[data-header]');
    const nav = document.querySelector('[data-nav]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const firstNavLink = nav ? nav.querySelector('a') : null;
    const desktopQuery = window.matchMedia('(min-width: 981px)');

    const setNavState = (isOpen, returnFocus = false) => {
        if (!nav || !toggle) {
            return;
        }

        nav.classList.toggle('is-open', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
        document.body.classList.toggle('nav-open', isOpen);

        if (isOpen && firstNavLink) {
            window.setTimeout(() => firstNavLink.focus(), 120);
        }

        if (!isOpen && returnFocus) {
            toggle.focus();
        }
    };

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
            setNavState(!isOpen);
        });

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => setNavState(false));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && nav.classList.contains('is-open')) {
                setNavState(false, true);
            }
        });

        const handleDesktopChange = (event) => {
            if (event.matches) {
                setNavState(false);
            }
        };

        if (typeof desktopQuery.addEventListener === 'function') {
            desktopQuery.addEventListener('change', handleDesktopChange);
        } else {
            desktopQuery.addListener(handleDesktopChange);
        }
    }

    if (header) {
        const updateHeader = () => {
            header.classList.toggle('is-scrolled', window.scrollY > 14);
        };

        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });
    }

    const lightbox = document.querySelector('[data-lightbox]');

    if (lightbox) {
        const lightboxImage = lightbox.querySelector('[data-lightbox-image]');
        const lightboxCaption = lightbox.querySelector('[data-lightbox-caption]');
        const lightboxClose = lightbox.querySelector('[data-lightbox-close]');
        const triggers = document.querySelectorAll('[data-lightbox-trigger]');
        const supportsDialog = typeof lightbox.showModal === 'function';
        let activeTrigger = null;

        const restoreTriggerFocus = () => {
            if (activeTrigger) {
                activeTrigger.focus();
                activeTrigger = null;
            }
        };

        const closeLightbox = () => {
            if (lightbox.open && typeof lightbox.close === 'function') {
                lightbox.close();
            } else {
                lightbox.removeAttribute('open');
                document.body.classList.remove('lightbox-open');
                restoreTriggerFocus();
            }
        };

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                activeTrigger = trigger;
                lightboxImage.src = trigger.dataset.lightboxSrc || '';
                lightboxImage.alt = trigger.dataset.lightboxAlt || '';
                lightboxCaption.textContent = trigger.dataset.lightboxCaption || '';

                if (!lightbox.open) {
                    if (supportsDialog) {
                        lightbox.showModal();
                    } else {
                        lightbox.setAttribute('open', '');
                    }
                }

                document.body.classList.add('lightbox-open');
                lightboxClose.focus();
            });
        });

        lightboxClose.addEventListener('click', closeLightbox);

        lightbox.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        lightbox.addEventListener('close', () => {
            document.body.classList.remove('lightbox-open');
            restoreTriggerFocus();
        });
    }

    const revealItems = document.querySelectorAll('[data-reveal]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!revealItems.length) {
        return;
    }

    if (reducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0.12,
    });

    revealItems.forEach((item) => observer.observe(item));
})();
