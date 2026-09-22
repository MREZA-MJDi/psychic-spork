document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-editorial-hero]');

    if (!root) {
        return;
    }

    const dataNode = root.querySelector('[data-hero-data]');
    const cards = [...root.querySelectorAll('[data-hero-card]')];
    const prev = root.querySelector('[data-hero-prev]');
    const next = root.querySelector('[data-hero-next]');
    const eyebrow = root.querySelector('[data-hero-eyebrow]');
    const heading = root.querySelector('[data-hero-heading]');
    const description = root.querySelector('[data-hero-description]');
    const button = root.querySelector('[data-hero-button]');
    const buttonText = root.querySelector('[data-hero-button-text]');
    const current = root.querySelector('[data-hero-current]');
    const total = root.querySelector('[data-hero-total]');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!dataNode || cards.length !== 5) {
        return;
    }

    let slides;

    try {
        slides = JSON.parse(dataNode.textContent.trim());
    } catch {
        slides = [];
    }

    if (!Array.isArray(slides) || slides.length === 0) {
        return;
    }

    const autoplayDelay = Number(root.dataset.autoplay || 6500);
    const slotClassNames = [0, 1, 2, 3, 4].map(
        (slot) => `editorial-hero__card--slot-${slot}`
    );

    let activeIndex = 0;
    let busy = false;
    let timer = null;
    let touchStartX = 0;

    const slotToSlide = (slot) => {
        if (slides.length === 1) {
            return 0;
        }

        if (slot === 0) {
            return (activeIndex - 1 + slides.length) % slides.length;
        }

        if (slot === 1) {
            return (activeIndex + 2) % slides.length;
        }

        if (slot === 2) {
            return (activeIndex + 1) % slides.length;
        }

        if (slot === 3) {
            return (activeIndex - 2 + slides.length * 2) % slides.length;
        }

        return activeIndex;
    };

    const setCardSlot = (card, slot) => {
        slotClassNames.forEach((className) => card.classList.remove(className));

        card.classList.add(`editorial-hero__card--slot-${slot}`);
        card.dataset.heroSlot = String(slot);
        card.classList.toggle('is-hero', slot === 4);

        card.setAttribute('aria-hidden', slot === 4 ? 'false' : 'true');
    };

    const updateCardContent = (card, slideIndex) => {
        const slide = slides[slideIndex];

        if (!slide) {
            return;
        }

        const image = card.querySelector('[data-card-image]');
        const mobileSource = card.querySelector('[data-card-mobile-source]');
        const title = card.querySelector('[data-card-title]');

        if (image && slide.image && image.src !== slide.image) {
            image.style.opacity = '0';

            window.setTimeout(() => {
                image.src = slide.image;

                const reveal = () => {
                    image.style.opacity = '1';
                };

                image.addEventListener('load', reveal, { once: true });

                if (image.complete) {
                    reveal();
                }
            }, 90);
        }

        if (mobileSource && slide.mobile_image) {
            mobileSource.srcset = slide.mobile_image;
        }

        if (title) {
            title.textContent = slide.title || 'کالکشن منتخب';
        }

        card.dataset.slideIndex = String(slideIndex);
    };

    const syncAllCards = () => {
        cards.forEach((card) => {
            const slot = Number(card.dataset.heroSlot);
            updateCardContent(card, slotToSlide(slot));
        });
    };

    const syncHeroText = () => {
        const slide = slides[activeIndex];

        if (!slide) {
            return;
        }

        eyebrow.textContent = slide.eyebrow || 'NEW COLLECTION';
        heading.textContent = slide.title || 'کالکشن منتخب جانان';
        description.textContent =
            slide.subtitle ||
            'کالکشنی برای استایل روزمره؛ ظریف، راحت و با جزئیاتی که حس بهتری می‌سازند.';

        const hasButton = Boolean(slide.button_text || slide.button_url);

        button.hidden = !hasButton;

        if (hasButton) {
            button.href = slide.button_url || '#';
            buttonText.textContent = slide.button_text || 'مشاهده کالکشن';
        }

        current.textContent = String(activeIndex + 1).padStart(2, '0');
        total.textContent = String(slides.length).padStart(2, '0');
    };

    const initialize = () => {
        cards.forEach((card, index) => {
            setCardSlot(card, index);
        });

        syncAllCards();
        syncHeroText();
    };

    const finish = () => {
        cards.forEach((card) => {
            card.classList.remove('is-moving', 'is-becoming-main');
        });

        syncAllCards();
        busy = false;
    };

    const go = (direction) => {
        if (busy || slides.length < 2) {
            return;
        }

        busy = true;

        cards.forEach((card) => {
            card.classList.add('is-moving');
        });

        if (direction > 0) {
            const incoming = cards.find(
                (card) => Number(card.dataset.heroSlot) === 2
            );

            cards.forEach((card) => {
                const slot = Number(card.dataset.heroSlot);

                if (slot === 0) setCardSlot(card, 1);
                if (slot === 1) setCardSlot(card, 3);
                if (slot === 2) setCardSlot(card, 4);
                if (slot === 3) setCardSlot(card, 2);
                if (slot === 4) setCardSlot(card, 0);
            });

            incoming?.classList.add('is-becoming-main');
            activeIndex = (activeIndex + 1) % slides.length;
        } else {
            const incoming = cards.find(
                (card) => Number(card.dataset.heroSlot) === 0
            );

            cards.forEach((card) => {
                const slot = Number(card.dataset.heroSlot);

                if (slot === 0) setCardSlot(card, 4);
                if (slot === 1) setCardSlot(card, 0);
                if (slot === 2) setCardSlot(card, 1);
                if (slot === 3) setCardSlot(card, 2);
                if (slot === 4) setCardSlot(card, 3);
            });

            incoming?.classList.add('is-becoming-main');
            activeIndex = (activeIndex - 1 + slides.length) % slides.length;
        }

        syncHeroText();

        window.setTimeout(
            finish,
            reduceMotion ? 80 : 940
        );
    };

    const stopAutoplay = () => {
        if (timer) {
            window.clearTimeout(timer);
            timer = null;
        }
    };

    const startAutoplay = () => {
        stopAutoplay();

        if (reduceMotion || slides.length < 2) {
            return;
        }

        timer = window.setTimeout(() => {
            go(1);
        }, autoplayDelay);
    };

    const nextSlide = () => {
        go(1);
        startAutoplay();
    };

    const previousSlide = () => {
        go(-1);
        startAutoplay();
    };

    initialize();

    next?.addEventListener('click', nextSlide);
    prev?.addEventListener('click', previousSlide);

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            if (busy || card.classList.contains('is-hero')) {
                return;
            }

            const slot = Number(card.dataset.heroSlot);

            if (slot === 0 || slot === 3) {
                previousSlide();
                return;
            }

            nextSlide();
        });
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            nextSlide();
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            previousSlide();
        }
    });

    root.addEventListener('mouseenter', stopAutoplay);
    root.addEventListener('mouseleave', startAutoplay);

    root.addEventListener('focusin', stopAutoplay);
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) {
            startAutoplay();
        }
    });

    root.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.clientX || 0;
        stopAutoplay();
    }, { passive: true });

    root.addEventListener('touchend', (event) => {
        const endX = event.changedTouches[0]?.clientX || 0;
        const delta = endX - touchStartX;

        if (Math.abs(delta) >= 45) {
            delta < 0 ? nextSlide() : previousSlide();
            return;
        }

        startAutoplay();
    }, { passive: true });

    startAutoplay();
});
