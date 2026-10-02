(() => {
    const boot = () => {
        if (window.__JANAN_APP_INITIALIZED__) return;
        window.__JANAN_APP_INITIALIZED__ = true;

        const runReveal = () => {
            const items = [...document.querySelectorAll('.reveal-up')];

            if (!items.length) return;

            if (!('IntersectionObserver' in window)) {
                items.forEach((item) => item.classList.add('is-visible'));
                return;
            }

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

            items.forEach((item) => observer.observe(item));
        };

        const demoFill = document.querySelector('[data-demo-fill]');

        demoFill?.addEventListener('click', () => {
            const email = document.querySelector('[data-demo-email]')?.textContent?.trim();
            const password = document.querySelector('[data-demo-password]')?.textContent?.trim();
            const identifier = document.querySelector('input[name="identifier"]');
            const passwordInput = document.querySelector('input[name="password"]');

            if (identifier && email) identifier.value = email;
            if (passwordInput && password) passwordInput.value = password;
            identifier?.focus();
        });

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
                    // Clipboard is optional on local HTTP development.
                }
            });
        });

        document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const input = toggle.closest('.auth-password-wrap')?.querySelector(
                    'input[data-password-input]'
                );

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

        const updatePasswordMeter = () => {
            if (!passwordSource || !passwordMeter) return;

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

        passwordSource?.addEventListener('input', updatePasswordMeter);
        updatePasswordMeter();

        runReveal();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
