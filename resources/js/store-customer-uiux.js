/* JANAN STORE / CUSTOMER UIUX — cart flight motion */
(() => {
    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const flyToCart = (form) => {
        if (prefersReduced || !window.gsap) return;

        const source = form.closest('.product-card')?.querySelector('.product-card__image-link img')
            || document.querySelector('[data-product-gallery] img');

        const target = document.querySelector('[data-cart-open]');

        if (!source || !target) return;

        const rect = source.getBoundingClientRect();
        const targetRect = target.getBoundingClientRect();

        const clone = source.cloneNode(true);
        clone.className = 'janan-cart-flight';
        Object.assign(clone.style, {
            position: 'fixed',
            left: rect.left + 'px',
            top: rect.top + 'px',
            width: Math.min(rect.width, 180) + 'px',
            height: Math.min(rect.height, 220) + 'px',
            objectFit: 'cover',
            borderRadius: '18px',
            zIndex: '99999',
            pointerEvents: 'none',
            margin: '0',
            boxShadow: '0 24px 70px rgba(20,34,40,.25)'
        });

        document.body.appendChild(clone);

        const dx = targetRect.left + targetRect.width / 2 - (rect.left + Math.min(rect.width, 180) / 2);
        const dy = targetRect.top + targetRect.height / 2 - (rect.top + Math.min(rect.height, 220) / 2);

        window.gsap.timeline({
            onComplete: () => {
                clone.remove();
                window.gsap.fromTo(target,
                    { scale: 1 },
                    { scale: 1.16, duration: .14, yoyo: true, repeat: 1, ease: 'power2.out' }
                );
            }
        })
        .to(clone, {
            x: dx * .42,
            y: dy * .18 - 80,
            rotation: -4,
            scale: .82,
            duration: .32,
            ease: 'power2.out'
        })
        .to(clone, {
            x: dx,
            y: dy,
            rotation: 7,
            scale: .16,
            opacity: .2,
            duration: .52,
            ease: 'power3.in'
        });
    };

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('.quick-add-form, .product-purchase-form');
        if (form) flyToCart(form);
    }, true);
})();
