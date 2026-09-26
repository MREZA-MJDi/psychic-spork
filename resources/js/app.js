import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
