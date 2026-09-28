(() => {
    const root = document.querySelector('[data-product-carousel]');
    if (!root) return;

    const viewport = root.querySelector('[data-product-viewport]');
    const track = root.querySelector('[data-product-track]');
    const cards = [...track.querySelectorAll('.home-product-slide')];
    const prev = root.querySelector('[data-product-prev]');
    const next = root.querySelector('[data-product-next]');
    const dots = root.querySelector('[data-product-status]');
    const autoplayMs = Number(root.dataset.autoplay || 4200);

    if (!viewport || !track || cards.length <= 1) return;

    let timer = null;
    let index = 0;

    const visibleCount = () => {
        if (window.innerWidth <= 560) return 1;
        if (window.innerWidth <= 900) return 2;
        return 4;
    };

    const metrics = () => {
        const visible = Math.min(visibleCount(), cards.length);
        const gap = parseFloat(getComputedStyle(track).gap || '0');
        const cardWidth = (viewport.clientWidth - gap * (visible - 1)) / visible;
        const step = cardWidth + gap;

        return { visible, step };
    };

    const update = (target, behavior = 'smooth') => {
        const { visible, step } = metrics();
        const maxIndex = Math.max(0, cards.length - visible);

        index = Math.max(0, Math.min(target, maxIndex));

        viewport.scrollTo({
            left: index * step,
            behavior,
        });

        if (dots) {
            dots.textContent = `${String(index + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        }

        prev?.toggleAttribute('disabled', cards.length <= visible);
        next?.toggleAttribute('disabled', cards.length <= visible);
    };

    const advance = () => {
        const { visible } = metrics();

        if (cards.length <= visible) return;

        const maxIndex = cards.length - visible;

        if (index >= maxIndex) {
            update(0, 'auto');
        } else {
            update(index + 1);
        }
    };

    const start = () => {
        if (cards.length <= visibleCount()) return;
        stop();
        timer = window.setInterval(advance, autoplayMs);
    };

    const stop = () => {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    prev?.addEventListener('click', () => {
        update(index - 1);
        start();
    });

    next?.addEventListener('click', () => {
        advance();
        start();
    });

    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);
    root.addEventListener('focusin', stop);
    root.addEventListener('focusout', start);

    let resizeTimer = null;
    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);

        resizeTimer = window.setTimeout(() => {
            update(index, 'auto');
            start();
        }, 120);
    });

    update(0, 'auto');
    start();
})();
