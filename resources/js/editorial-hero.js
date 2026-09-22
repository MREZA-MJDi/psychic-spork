document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-editorial-hero]').forEach((root) => {
        const dataNode = root.querySelector('[data-hero-data]');
        const mainCard = root.querySelector('[data-hero-main-card]');
        const mainImage = root.querySelector('[data-hero-main-image]');
        const prevImage = root.querySelector('[data-hero-side-prev]');
        const nextImage = root.querySelector('[data-hero-side-next]');
        const prev = root.querySelector('[data-hero-prev]');
        const next = root.querySelector('[data-hero-next]');
        const prevCard = root.querySelector('[data-hero-prev-card]');
        const nextCard = root.querySelector('[data-hero-next-card]');
        const eyebrow = root.querySelector('[data-hero-eyebrow]');
        const heading = root.querySelector('[data-hero-heading]');
        const description = root.querySelector('[data-hero-description]');
        const button = root.querySelector('[data-hero-button]');
        const buttonText = root.querySelector('[data-hero-button-text]');
        const brandMeta = root.querySelector('[data-hero-brand]');
        const mainTitle = root.querySelector('[data-hero-main-title]');
        const mainBrand = root.querySelector('[data-hero-main-brand]');
        const counters = root.querySelectorAll('[data-hero-current]');
        const totals = root.querySelectorAll('[data-hero-total]');

        if (!dataNode || !mainCard || !mainImage) return;

        let slides = [];

        try {
            slides = JSON.parse(dataNode.textContent.trim());
        } catch {
            slides = [];
        }

        if (!Array.isArray(slides) || slides.length === 0) return;

        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const autoplayDelay = Math.max(3000, Number(root.dataset.autoplay || 5600));
        let activeIndex = 0;
        let timer = null;
        let locked = false;

        const slideAt = (index) => slides[(index + slides.length) % slides.length];

        const setImage = (element, slide) => {
            if (!element || !slide?.image) return;

            const nextSrc = String(slide.image);

            if (element.getAttribute('src') === nextSrc) return;

            if (reduceMotion) {
                element.src = nextSrc;
                return;
            }

            element.style.opacity = '0';

            const reveal = () => {
                element.style.opacity = '1';
            };

            element.onload = reveal;
            element.src = nextSrc;

            if (element.complete) {
                requestAnimationFrame(reveal);
            }
        };

        const render = () => {
            const current = slideAt(activeIndex);
            const previous = slideAt(activeIndex - 1);
            const following = slideAt(activeIndex + 1);

            eyebrow && (eyebrow.textContent = current.eyebrow || 'NEW COLLECTION');
            heading && (heading.textContent = current.title || 'کالکشن منتخب');
            description && (description.textContent = current.subtitle || 'انتخابی آرام، دقیق و شخصی برای هر روز.');
            brandMeta && (brandMeta.textContent = current.brand || 'JANAN');
            mainTitle && (mainTitle.textContent = current.title || 'کالکشن منتخب جانان');
            mainBrand && (mainBrand.textContent = current.brand || 'JANAN');

            if (button) {
                const hasUrl = Boolean(current.button_url);
                button.hidden = !hasUrl;

                if (hasUrl) {
                    button.href = current.button_url;
                    buttonText && (buttonText.textContent = current.button_text || 'مشاهده کالکشن');
                }
            }

            const currentText = String(activeIndex + 1).padStart(2, '0');
            const totalText = String(slides.length).padStart(2, '0');

            counters.forEach((node) => { node.textContent = currentText; });
            totals.forEach((node) => { node.textContent = totalText; });

            if (current.image) {
                if (reduceMotion) {
                    mainImage.src = current.image;
                } else {
                    mainCard.classList.add('is-swapping');
                    window.setTimeout(() => {
                        setImage(mainImage, current);
                        mainCard.classList.remove('is-swapping');
                    }, 80);
                }
            }

            setImage(prevImage, previous);
            setImage(nextImage, following);
        };

        const stop = () => {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
        };

        const start = () => {
            stop();

            if (reduceMotion || slides.length < 2) return;

            timer = window.setTimeout(() => {
                move(1);
            }, autoplayDelay);
        };

        const move = (direction) => {
            if (locked || slides.length < 2) return;

            locked = true;
            activeIndex = (activeIndex + direction + slides.length) % slides.length;
            render();

            window.setTimeout(() => {
                locked = false;
            }, reduceMotion ? 20 : 260);

            start();
        };

        prev?.addEventListener('click', () => move(-1));
        next?.addEventListener('click', () => move(1));
        prevCard?.addEventListener('click', () => move(-1));
        nextCard?.addEventListener('click', () => move(1));

        let touchStartX = null;

        root.addEventListener('touchstart', (event) => {
            touchStartX = event.changedTouches[0]?.clientX ?? null;
            stop();
        }, { passive: true });

        root.addEventListener('touchend', (event) => {
            if (touchStartX === null) {
                start();
                return;
            }

            const endX = event.changedTouches[0]?.clientX ?? touchStartX;
            const delta = endX - touchStartX;

            if (Math.abs(delta) > 45) {
                move(delta < 0 ? 1 : -1);
            } else {
                start();
            }

            touchStartX = null;
        }, { passive: true });

        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop);
        root.addEventListener('focusout', (event) => {
            if (!root.contains(event.relatedTarget)) start();
        });

        root.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') move(-1);
            if (event.key === 'ArrowRight') move(1);
        });

        render();
        start();
    });
});
