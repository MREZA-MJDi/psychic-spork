import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    if (window.__JANAN_APP_INITIALIZED__) return;
    window.__JANAN_APP_INITIALIZED__ = true;

    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const storeNav = document.querySelector('.store-nav');
    const searchToggle = document.querySelector('[data-search-toggle]');
    const searchPanel = document.querySelector('[data-search-panel]');

    menuToggle?.addEventListener('click', () => {
        if (mobileMenu) {
            const open = mobileMenu.classList.toggle('is-open');
            mobileMenu.setAttribute('aria-hidden', String(!open));
            menuToggle.setAttribute('aria-expanded', String(open));
            return;
        }

        const open = storeNav?.classList.toggle('is-open') ?? false;
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
        const open = !searchPanel?.classList.contains('is-open');
        setSearchOpen(open);
    });

    document.addEventListener('click', (event) => {
        if (
            searchPanel?.classList.contains('is-open') &&
            !searchPanel.contains(event.target) &&
            !searchToggle?.contains(event.target)
        ) {
            setSearchOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && searchPanel?.classList.contains('is-open')) {
            setSearchOpen(false);
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

    const demoFill = document.querySelector('[data-demo-fill]');

    if (demoFill) {
        demoFill.addEventListener('click', () => {
            const email = document.querySelector('[data-demo-email]')?.textContent?.trim();
            const password = document.querySelector('[data-demo-password]')?.textContent?.trim();
            const identifier = document.querySelector('input[name="identifier"]');
            const passwordInput = document.querySelector('input[name="password"]');

            if (identifier && email) identifier.value = email;
            if (passwordInput && password) passwordInput.value = password;
            identifier?.focus();
        });
    }

    document.querySelectorAll('[data-copy-target]').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = button.dataset.copyTarget;
            const selector = target === 'email'
                ? '[data-demo-email]'
                : '[data-demo-password]';
            const value = document.querySelector(selector)?.textContent?.trim();

            if (!value || !navigator.clipboard) return;

            try {
                await navigator.clipboard.writeText(value);
                const original = button.textContent;
                button.textContent = 'کپی شد';
                window.setTimeout(() => {
                    button.textContent = original;
                }, 1200);
            } catch {
                // Clipboard access may be unavailable on non-secure local contexts.
            }
        });
    });

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const wrapper = toggle.closest('.auth-password-wrap');
            const input = wrapper?.querySelector('input[data-password-input]');

            if (!input) return;

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            toggle.setAttribute('aria-pressed', String(!showing));
            toggle.setAttribute(
                'aria-label',
                showing ? 'نمایش رمز عبور' : 'مخفی کردن رمز عبور'
            );
        });
    });

    const passwordSource = document.querySelector('[data-password-meter-source]');
    const passwordMeter = document.querySelector('[data-password-meter]');

    if (passwordSource && passwordMeter) {
        const updatePasswordMeter = () => {
            const value = passwordSource.value || '';
            let strength = 0;

            if (value.length >= 8) strength += 25;
            if (value.length >= 12) strength += 20;
            if (/[a-z]/.test(value)) strength += 15;
            if (/[A-Z]/.test(value)) strength += 15;
            if (/\d/.test(value)) strength += 10;
            if (/[^a-zA-Z\d]/.test(value)) strength += 15;

            passwordMeter.style.width = Math.min(strength, 100) + '%';
        };

        passwordSource.addEventListener('input', updatePasswordMeter);
        updatePasswordMeter();
    }

    const revealItems = [...document.querySelectorAll('.reveal-up')];

    if ('IntersectionObserver' in window && revealItems.length) {
        const observer = new IntersectionObserver((entries, instance) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                instance.unobserve(entry.target);
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -7% 0px',
        });

        revealItems.forEach((element) => observer.observe(element));
    } else {
        revealItems.forEach((element) => element.classList.add('is-visible'));
    }

    const replaceBrokenStoreImage = (image) => {
        if (!(image instanceof HTMLImageElement)) return;
        if (image.dataset.storeImageFallbackApplied) return;

        const fallbackClass = image.dataset.storeImageFallbackClass;
        const fallbackTag = ['div', 'span'].includes(image.dataset.storeImageFallbackTag)
            ? image.dataset.storeImageFallbackTag
            : 'div';

        if (!fallbackClass || !image.parentElement) return;

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
            event.target instanceof HTMLImageElement
            && event.target.matches('[data-store-image-fallback]')
        ) {
            replaceBrokenStoreImage(event.target);
        }
    }, true);

    document
        .querySelectorAll('img[data-store-image-fallback]')
        .forEach((image) => {
            if (image.complete && image.naturalWidth === 0) {
                replaceBrokenStoreImage(image);
            }
        });

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
});

