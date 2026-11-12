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

    // PWA: daftarkan service worker agar situs bisa dipasang di layar depan HP.
    if ('serviceWorker' in navigator && window.isSecureContext) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
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

    // Galeri: carousel scroll-snap — slide otomatis ke samping tiap ±4 detik
    // dan geser manual (swipe native di layar sentuh, drag mouse di desktop,
    // tombol panah, serta titik indikator). Autoplay dijeda bila pengguna
    // berinteraksi (dilanjutkan ±8 detik kemudian), saat hover, tab tersembunyi,
    // lightbox pratinjau terbuka, atau carousel di luar layar. Total mati
    // untuk preferensi prefers-reduced-motion — hanya navigasi manual.
    document.querySelectorAll('[data-gallery-carousel]').forEach((root) => {
        const track = root.querySelector('[data-gallery-track]');

        if (!track || track.children.length < 2) {
            return;
        }

        const items = Array.from(track.children);
        const prevButton = root.querySelector('[data-gallery-prev]');
        const nextButton = root.querySelector('[data-gallery-next]');
        const dotsHost = root.querySelector('[data-gallery-dots]');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const autoplayDelay = Number(root.dataset.galleryInterval) || 4000;
        const resumeDelay = 8000;
        const lastIndex = items.length - 1;
        let activeIndex = 0;
        let lastInteraction = 0;
        let inView = true;
        let hovered = false;
        let dragging = false;

        // Offset tiap slide dihitung saat dibutuhkan agar tetap benar ketika
        // jumlah kartu per layar berubah lewat media query (resize).
        const slideOffsets = () => items.map((item) => item.offsetLeft - items[0].offsetLeft);

        const nearestSlide = () => {
            let best = 0;
            let bestDistance = Infinity;

            slideOffsets().forEach((offset, index) => {
                const distance = Math.abs(offset - track.scrollLeft);

                if (distance < bestDistance) {
                    bestDistance = distance;
                    best = index;
                }
            });

            return best;
        };

        const updateControls = () => {
            if (prevButton) {
                prevButton.disabled = activeIndex === 0;
            }

            if (nextButton) {
                nextButton.disabled = activeIndex === lastIndex;
            }

            if (dotsHost) {
                dotsHost.querySelectorAll('[data-gallery-dot]').forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === activeIndex);
                });

                // Jaga titik aktif tetap terlihat saat jumlah foto banyak
                // (wadah titik dapat digulir secara horizontal). Gulir HANYA
                // wadah titiknya — scrollIntoView tidak dipakai karena ikut
                // menggulir halaman hingga galeri saat dibuka di layar HP.
                const activeDot = dotsHost.querySelector('[data-gallery-dot].is-active');

                if (activeDot && dotsHost.scrollWidth > dotsHost.clientWidth) {
                    const stripBox = dotsHost.getBoundingClientRect();
                    const dotBox = activeDot.getBoundingClientRect();
                    const fullyVisible = dotBox.left >= stripBox.left && dotBox.right <= stripBox.right;

                    if (!fullyVisible) {
                        const target = dotsHost.scrollLeft
                            + (dotBox.left - stripBox.left)
                            - (dotsHost.clientWidth - dotBox.width) / 2;

                        dotsHost.scrollTo({
                            left: Math.max(0, target),
                            behavior: reducedMotion ? 'auto' : 'smooth',
                        });
                    }
                }
            }
        };

        const goTo = (target, smooth = true) => {
            activeIndex = Math.max(0, Math.min(target, lastIndex));
            track.scrollTo({
                left: slideOffsets()[activeIndex],
                behavior: smooth && !reducedMotion ? 'smooth' : 'auto',
            });
        };

        const step = (direction) => {
            if (direction > 0 && activeIndex === lastIndex) {
                goTo(0, false); // putaran baru: kembali ke awal tanpa sapuan panjang
            } else {
                goTo(activeIndex + direction);
            }
        };

        const stepWithHold = (direction) => {
            lastInteraction = Date.now();
            step(direction);
        };

        if (prevButton) {
            prevButton.addEventListener('click', () => stepWithHold(-1));
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => stepWithHold(1));
        }

        if (dotsHost) {
            items.forEach((_, index) => {
                const dot = document.createElement('button');

                dot.type = 'button';
                dot.className = 'gallery-carousel__dot';
                dot.dataset.galleryDot = '';
                dot.setAttribute('aria-label', `Ke slide ${index + 1} dari ${items.length}`);
                dot.addEventListener('click', () => {
                    lastInteraction = Date.now();
                    goTo(index);
                });
                dotsHost.appendChild(dot);
            });
        }

        updateControls();

        track.addEventListener('scroll', () => {
            activeIndex = nearestSlide();
            updateControls();
        }, { passive: true });

        track.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                stepWithHold(-1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                stepWithHold(1);
            }
        });

        // Drag mouse di desktop: pointer hanya diambil setelah gerakan melewati
        // ambang kecil, sehingga klik biasa tetap sampai ke tombol lightbox.
        track.addEventListener('pointerdown', (event) => {
            if (event.pointerType !== 'mouse' || event.button !== 0) {
                return;
            }

            const startX = event.clientX;
            const startLeft = track.scrollLeft;
            let captured = false;
            let moved = 0;
            let ended = false;

            lastInteraction = Date.now();

            const onMove = (moveEvent) => {
                moved = Math.abs(moveEvent.clientX - startX);

                if (!captured && moved > 6) {
                    captured = true;
                    dragging = true;
                    track.setPointerCapture(event.pointerId);
                    track.style.scrollSnapType = 'none';
                }

                if (captured) {
                    track.scrollLeft = startLeft - (moveEvent.clientX - startX);
                    moveEvent.preventDefault();
                }
            };

            const onEnd = () => {
                if (ended) {
                    return;
                }

                ended = true;
                track.removeEventListener('pointermove', onMove);
                lastInteraction = Date.now();

                if (captured) {
                    dragging = false;
                    track.style.scrollSnapType = '';
                    goTo(nearestSlide());
                }

                if (moved > 6) {
                    // Tutup klik sisa drag agar lightbox tidak tiba-tiba terbuka.
                    track.addEventListener('click', (clickEvent) => {
                        clickEvent.preventDefault();
                        clickEvent.stopPropagation();
                    }, { capture: true, once: true });
                }
            };

            track.addEventListener('pointermove', onMove);
            track.addEventListener('pointerup', onEnd, { once: true });
            track.addEventListener('pointercancel', onEnd, { once: true });
        });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => {
                inView = entries[0].isIntersecting;
            }).observe(root);
        }

        root.addEventListener('mouseenter', () => {
            hovered = true;
        });
        root.addEventListener('mouseleave', () => {
            hovered = false;
        });
        root.addEventListener('focusin', () => {
            hovered = true;
        });
        root.addEventListener('focusout', () => {
            hovered = false;
        });

        if (!reducedMotion) {
            window.setInterval(() => {
                const lightboxOpen = document.body.classList.contains('lightbox-open');

                if (dragging || hovered || lightboxOpen || document.hidden || !inView) {
                    return;
                }

                if (Date.now() - lastInteraction < resumeDelay) {
                    return;
                }

                step(1);
            }, autoplayDelay);
        }
    });

    // Bingkai foto beranda & profil (split): slider transform — foto bergeser
    // satu frame tiap ±4 detik dan bisa digeser manual: drag/swipe di foto,
    // klik titik indikator, atau panah keyboard. Etika autoplay sama dengan
    // carousel galeri: dijeda saat interaksi (dilanjutkan ±8 detik), hover,
    // di luar layar, tab tersembunyi; otomatis mati untuk prefers-reduced-motion.
    // Tepian emas dan kartu kutipan tetap diam — hanya foto di baliknya yang
    // berpindah.
    document.querySelectorAll('[data-split-slider]').forEach((root) => {
        const track = root.querySelector('[data-split-track]');

        if (!track || track.children.length < 2) {
            return;
        }

        const slides = Array.from(track.children);
        const dotsHost = root.querySelector('[data-split-dots]');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const interval = Number(root.dataset.splitInterval) || 4000;
        const resumeDelay = 8000;
        const lastIndex = slides.length - 1;
        let activeIndex = 0;
        let lastInteraction = 0;
        let inView = true;
        let hovered = false;
        let dragging = false;

        // Foto tidak ikut drag image bawaan browser agar penggeseran dikendalikan slider.
        slides.forEach((slide) => slide.setAttribute('draggable', 'false'));

        const updateDots = () => {
            if (!dotsHost) {
                return;
            }

            dotsHost.querySelectorAll('[data-split-dot]').forEach((dot, index) => {
                dot.classList.toggle('is-active', index === activeIndex);
            });
        };

        const moveTo = (target, animate = true) => {
            activeIndex = Math.max(0, Math.min(target, lastIndex));
            track.style.transition = animate && !reducedMotion ? '' : 'none';
            track.style.transform = `translateX(-${activeIndex * 100}%)`;
            updateDots();
        };

        const step = (direction) => {
            if (direction > 0 && activeIndex === lastIndex) {
                moveTo(0, false); // putaran baru: kembali langsung ke awal
            } else {
                moveTo(activeIndex + direction);
            }
        };

        const stepWithHold = (direction) => {
            lastInteraction = Date.now();
            step(direction);
        };

        if (dotsHost) {
            slides.forEach((_, index) => {
                const dot = document.createElement('button');

                dot.type = 'button';
                dot.className = 'split-slider__dot';
                dot.dataset.splitDot = '';
                dot.setAttribute('aria-label', `Ke foto ${index + 1} dari ${slides.length}`);
                dot.addEventListener('click', () => {
                    lastInteraction = Date.now();
                    moveTo(index);
                });
                dotsHost.appendChild(dot);
            });
        }

        moveTo(0, false);

        root.addEventListener('mouseenter', () => {
            hovered = true;
        });
        root.addEventListener('mouseleave', () => {
            hovered = false;
        });
        root.addEventListener('focusin', () => {
            hovered = true;
        });
        root.addEventListener('focusout', () => {
            hovered = false;
        });

        root.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                stepWithHold(-1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                stepWithHold(1);
            }
        });

        // Geser manual (mouse & sentuh): pointer diambil bila gerakan mendatar
        // melewati ambang; seret mengikuti kursor, dilepas = pindah ke foto
        // terdekat (kembali bila kurang jauh). Sentuh vertikal tetap menjadi
        // gulir halaman (CSS touch-action: pan-y), bukan pindah foto.
        root.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            const startX = event.clientX;
            const width = root.getBoundingClientRect().width;
            let captured = false;
            let deltaX = 0;
            let ended = false;

            lastInteraction = Date.now();

            const onMove = (moveEvent) => {
                deltaX = moveEvent.clientX - startX;

                if (!captured && Math.abs(deltaX) > 8) {
                    captured = true;
                    dragging = true;
                    root.classList.add('is-dragging');
                    root.setPointerCapture(event.pointerId);
                    track.style.transition = 'none';
                }

                if (captured) {
                    moveEvent.preventDefault();
                    track.style.transform = `translateX(calc(${-activeIndex * 100}% + ${deltaX}px))`;
                }
            };

            const onEnd = () => {
                if (ended) {
                    return;
                }

                ended = true;
                root.removeEventListener('pointermove', onMove);
                root.classList.remove('is-dragging');
                dragging = false;
                lastInteraction = Date.now();

                if (captured) {
                    const advanced = Math.abs(deltaX) > width * 0.18;
                    const direction = deltaX < 0 ? 1 : -1;

                    stepWithHold(advanced ? direction : 0);
                } else {
                    moveTo(activeIndex, true); // snap kembali bila tak jadi geser
                }
            };

            root.addEventListener('pointermove', onMove);
            root.addEventListener('pointerup', onEnd, { once: true });
            root.addEventListener('pointercancel', onEnd, { once: true });
        });

        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => {
                inView = entries[0].isIntersecting;
            }).observe(root);
        }

        if (!reducedMotion) {
            window.setInterval(() => {
                if (dragging || hovered || document.hidden || !inView) {
                    return;
                }

                if (Date.now() - lastInteraction < resumeDelay) {
                    return;
                }

                step(1);
            }, interval);
        }
    });

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
