import { initMoneyInputs } from './money-input.js';

initMoneyInputs();

(() => {
    const revealItems = document.querySelectorAll('.reveal-up');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, current) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    current.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });
        revealItems.forEach((item) => observer.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.closest('.auth-password-wrap')?.querySelector('[data-password-input]');
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
        });
    });

    const password = document.querySelector('[data-password-meter-source]');
    const meter = document.querySelector('[data-password-meter]');
    const updateMeter = () => {
        if (!password || !meter) return;
        const value = password.value;
        const score = [value.length >= 8, /[a-z]/i.test(value), /\d/.test(value), /[^a-z\d]/i.test(value)].filter(Boolean).length;
        meter.style.width = `${score * 25}%`;
        meter.dataset.strength = String(score);
    };
    password?.addEventListener('input', updateMeter);
    updateMeter();

    document.querySelectorAll('[data-demo-fill]').forEach((button) => {
        button.addEventListener('click', () => {
            const phone = document.querySelector('[name="phone"]');
            const password = document.querySelector('[name="password"]');
            if (phone) phone.value = button.dataset.demoPhone || '';
            if (password) password.value = button.dataset.demoPassword || '';
        });
    });
})();
