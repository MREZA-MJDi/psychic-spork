(() => {
    const root = document.querySelector('[data-product-carousel]');
    if (!root) return;

    const viewport = root.querySelector('[data-product-viewport]');
    const track = root.querySelector('[data-product-track]');
    const cards = [...(track?.querySelectorAll('.home-product-slide') ?? [])];
    const prev = root.querySelector('[data-product-prev]');
    const next = root.querySelector('[data-product-next]');
    const status = root.querySelector('[data-product-status]');
    const autoplayMs = Math.max(3000, Number(root.dataset.autoplay || 3500));

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
        const gap = parseFloat(getComputedStyle(track).gap || '0') || 0;
        const cardWidth = Math.max(
            1,
            (viewport.clientWidth - gap * (visible - 1)) / visible
        );

        cards.forEach((card) => {
            card.style.flexBasis = `${cardWidth}px`;
            card.style.width = `${cardWidth}px`;
            card.style.maxWidth = `${cardWidth}px`;
        });

        return {
            visible,
            maxIndex: Math.max(0, cards.length - visible),
        };
    };

    const updateStatus = () => {
        if (status) {
            status.textContent =
                `${String(index + 1).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        }
    };

    const scrollToCard = (card, behavior = 'smooth') => {
        if (!card) return;

        /*
         * Do not use Element.scrollIntoView() here.
         * It is allowed to move every scrollable ancestor, including the
         * document itself, which made clicking/autoplay jump the page back
         * to the Product section.
         */
        const left = Math.max(
            0,
            card.offsetLeft - parseFloat(getComputedStyle(viewport).paddingLeft || '0')
        );

        viewport.scrollTo({
            left,
            behavior,
        });
    };

    const update = (target, behavior = 'smooth') => {
        const { maxIndex } = metrics();

        index = Math.max(0, Math.min(target, maxIndex));

        scrollToCard(cards[index], behavior);

        updateStatus();

        const locked = cards.length <= metrics().visible;
        prev?.toggleAttribute('disabled', locked);
        next?.toggleAttribute('disabled', locked);
    };

    const advance = () => {
        const { maxIndex } = metrics();

        if (maxIndex <= 0) return;

        update(index >= maxIndex ? 0 : index + 1);
    };

    const stop = () => {
        if (timer) {
            window.clearInterval(timer);
            timer = null;
        }
    };

    const start = () => {
        stop();

        if (cards.length <= visibleCount()) return;

        timer = window.setInterval(advance, autoplayMs);
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
