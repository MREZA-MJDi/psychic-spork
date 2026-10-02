(() => {
    if (window.__JANAN_STORE_CUSTOMER_UI_INITIALIZED__) return;
    window.__JANAN_STORE_CUSTOMER_UI_INITIALIZED__ = true;

    const initStoreNavigation = () => {
        const menuToggle = document.querySelector('[data-menu-toggle]');
        const mobileMenu = document.querySelector('[data-mobile-menu]');
        const storeNav = document.querySelector('.store-nav');
        const searchToggle = document.querySelector('[data-search-toggle]');
        const searchPanel = document.querySelector('[data-search-panel]');

        menuToggle?.addEventListener('click', () => {
            const target = mobileMenu || storeNav;
            if (!target) return;

            const open = target.classList.toggle('is-open');
            target.setAttribute('aria-hidden', String(!open));
            menuToggle.setAttribute('aria-expanded', String(open));
        });

        const setSearchOpen = (open) => {
            if (!searchPanel || !searchToggle) return;

            searchPanel.classList.toggle('is-open', open);
            searchToggle.setAttribute('aria-expanded', String(open));

            if (open) {
                window.requestAnimationFrame(() => {
                    searchPanel.querySelector('input')?.focus();
                });
            }
        };

        searchToggle?.addEventListener('click', () => {
            setSearchOpen(!searchPanel?.classList.contains('is-open'));
        });

        document.addEventListener('click', (event) => {
            if (
                searchPanel?.classList.contains('is-open') &&
                !searchPanel.contains(event.target) &&
                !searchToggle?.contains(event.target)
            ) {
                setSearchOpen(false);
            }

            if (
                mobileMenu?.classList.contains('is-open') &&
                !mobileMenu.contains(event.target) &&
                !menuToggle?.contains(event.target)
            ) {
                mobileMenu.classList.remove('is-open');
                mobileMenu.setAttribute('aria-hidden', 'true');
                menuToggle?.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setSearchOpen(false);

                if (mobileMenu?.classList.contains('is-open')) {
                    mobileMenu.classList.remove('is-open');
                    mobileMenu.setAttribute('aria-hidden', 'true');
                    menuToggle?.setAttribute('aria-expanded', 'false');
                }
            }

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {
                event.preventDefault();
                setSearchOpen(true);
            }
        });

        mobileMenu?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('is-open');
                mobileMenu.setAttribute('aria-hidden', 'true');
                menuToggle?.setAttribute('aria-expanded', 'false');
            });
        });

        storeNav?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                storeNav.classList.remove('is-open');
                menuToggle?.setAttribute('aria-expanded', 'false');
            });
        });

        const desktopQuery = window.matchMedia('(min-width: 901px)');
        const syncDesktopNavigation = (event) => {
            if (!event.matches) return;
            mobileMenu?.classList.remove('is-open');
            mobileMenu?.setAttribute('aria-hidden', 'true');
            storeNav?.classList.remove('is-open');
            menuToggle?.setAttribute('aria-expanded', 'false');
        };

        syncDesktopNavigation(desktopQuery);
        desktopQuery.addEventListener?.('change', syncDesktopNavigation);
    };

    const initStoreImages = () => {
        const replaceBrokenStoreImage = (image) => {
            if (!(image instanceof HTMLImageElement)) return;
            if (image.dataset.storeImageFallbackApplied) return;
            const fallbackClass = image.dataset.storeImageFallbackClass;
            if (!fallbackClass || !image.parentElement) return;

            const fallbackTag = ['div', 'span'].includes(
                image.dataset.storeImageFallbackTag
            ) ? image.dataset.storeImageFallbackTag : 'div';

            const fallback = document.createElement(fallbackTag);
            fallback.className = fallbackClass;
            fallback.setAttribute('aria-hidden', 'true');
            fallback.dataset.storeImageFallbackApplied = '1';

            const label = document.createElement('span');
            label.textContent = image.dataset.storeImageFallback || 'JANAN';
            fallback.appendChild(label);
            image.replaceWith(fallback);
        };

        document.addEventListener('error', (event) => {
            if (
                event.target instanceof HTMLImageElement &&
                event.target.matches('[data-store-image-fallback]')
            ) {
                replaceBrokenStoreImage(event.target);
            }
        }, true);

        document.querySelectorAll('img[data-store-image-fallback]').forEach((image) => {
            if (image.complete && image.naturalWidth === 0) {
                replaceBrokenStoreImage(image);
            }
        });
    };

    const initHeroNavbar = () => {
        const hero = document.querySelector('[data-immersive-gallery]');
        const nav = document.querySelector('.mobile-bottom-nav--store');

        if (hero && nav && 'IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                ([entry]) => {
                    nav.classList.toggle(
                        'is-immersive-dimmed',
                        entry.isIntersecting && entry.intersectionRatio > 0.28
                    );
                },
                { threshold: [0, 0.28, 0.7, 1] }
            );

            observer.observe(hero);
        }
    };

    const syncCheckoutChoices = () => {
        const form = document.querySelector('.checkout-form');
        if (!form) return;

        const wholesale = form.querySelector(
            'input[name="order_type"]:checked'
        )?.value === 'wholesale';

        const cheque = form.querySelector(
            'input[name="payment_method"][value="cheque"]'
        );

        const online = form.querySelector(
            'input[name="payment_method"][value="online"]'
        );

        const chequeFields = form.querySelector('[data-cheque-fields]');

        if (cheque) {
            cheque.disabled = !wholesale;

            if (!wholesale && cheque.checked) {
                cheque.checked = false;
                online?.click();
            }
        }

        if (chequeFields) {
            chequeFields.hidden = !wholesale || !cheque?.checked;
        }
    };

    const boot = () => {
        initStoreNavigation();
        initStoreImages();
        initHeroNavbar();
        syncCheckoutChoices();

        document.addEventListener('change', (event) => {
            if (
                event.target.matches(
                    '.checkout-form input[name="order_type"], .checkout-form input[name="payment_method"]'
                )
            ) {
                syncCheckoutChoices();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
