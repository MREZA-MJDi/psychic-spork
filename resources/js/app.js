document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const searchToggle = document.querySelector('[data-search-toggle]');
    const searchPanel = document.querySelector('[data-search-panel]');

    menuToggle?.addEventListener('click', () => {
        const open = mobileMenu?.classList.toggle('is-open') ?? false;
        menuToggle.setAttribute('aria-expanded', String(open));
    });

    searchToggle?.addEventListener('click', () => {
        const open = searchPanel?.classList.toggle('is-open') ?? false;
        searchToggle.setAttribute('aria-expanded', String(open));

        if (open) {
            searchPanel?.querySelector('input')?.focus();
        }
    });

    mobileMenu?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('is-open');
            menuToggle?.setAttribute('aria-expanded', 'false');
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

    const hero = document.querySelector('[data-janan-hero]');

    if (hero) {
        const track = hero.querySelector('.janan-public-hero__track');
        const slides = [...hero.querySelectorAll('[data-hero-slide]')];
        const dots = [...hero.querySelectorAll('[data-hero-dot]')];
        const prev = hero.querySelector('[data-hero-prev]');
        const next = hero.querySelector('[data-hero-next]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        let index = 0;
        let timer = null;
        let touchStartY = 0;
        let touchStartX = 0;

        const render = (nextIndex, animate = true) => {
            if (!slides.length) return;

            index = (nextIndex + slides.length) % slides.length;

            track.style.transition = animate
                ? 'transform 900ms cubic-bezier(.22,.78,.24,1)'
                : 'none';

            track.style.transform = `translate3d(0, -${index * 100}%, 0)`;

            slides.forEach((slide, slideIndex) => {
                const active = slideIndex === index;
                const before = slideIndex === (index - 1 + slides.length) % slides.length;
                const after = slideIndex === (index + 1) % slides.length;

                slide.classList.toggle('is-active', active);
                slide.classList.toggle('is-before', before);
                slide.classList.toggle('is-after', after);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });

            dots.forEach((dot, dotIndex) => {
                const active = dotIndex === index;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });
        };

        const stop = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        const start = () => {
            stop();

            if (reduceMotion || slides.length < 2) {
                return;
            }

            timer = window.setInterval(() => render(index + 1), 5500);
        };

        next?.addEventListener('click', () => {
            render(index + 1);
            start();
        });

        prev?.addEventListener('click', () => {
            render(index - 1);
            start();
        });

        dots.forEach((dot, dotIndex) => {
            dot.addEventListener('click', () => {
                render(dotIndex);
                start();
            });
        });

        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', start);

        hero.addEventListener('focusin', stop);
        hero.addEventListener('focusout', (event) => {
            if (!hero.contains(event.relatedTarget)) {
                start();
            }
        });

        hero.addEventListener('touchstart', (event) => {
            const touch = event.changedTouches[0];
            touchStartY = touch?.clientY ?? 0;
            touchStartX = touch?.clientX ?? 0;
            stop();
        }, { passive: true });

        hero.addEventListener('touchend', (event) => {
            const touch = event.changedTouches[0];
            const deltaY = (touch?.clientY ?? 0) - touchStartY;
            const deltaX = (touch?.clientX ?? 0) - touchStartX;

            if (Math.abs(deltaY) > 45 && Math.abs(deltaY) > Math.abs(deltaX)) {
                render(deltaY < 0 ? index + 1 : index - 1);
            }

            start();
        }, { passive: true });

        hero.addEventListener('wheel', (event) => {
            if (Math.abs(event.deltaY) < 18) return;
            event.preventDefault();
            render(event.deltaY > 0 ? index + 1 : index - 1);
            start();
        }, { passive: false });

        render(0, false);
        start();
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

    window.addEventListener('resize', () => {
        if (window.innerWidth > 820) {
            mobileMenu?.classList.remove('is-open');
            menuToggle?.setAttribute('aria-expanded', 'false');
        }
    });
});
