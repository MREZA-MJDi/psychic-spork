(() => {
    if (window.__JANAN_STORE_CART_INITIALIZED__) return;
    window.__JANAN_STORE_CART_INITIALIZED__ = true;

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content') || '';

const drawer = document.querySelector('[data-cart-drawer]');
const quickPreview = document.querySelector('[data-cart-quick-preview]');
const quickPreviewImage = quickPreview?.querySelector('[data-cart-preview-image]');
const quickPreviewName = quickPreview?.querySelector('[data-cart-preview-name]');
const quickPreviewMeta = quickPreview?.querySelector('[data-cart-preview-meta]');

let quickPreviewTimer = null;
let actionMessageTimer = null;
let cartTaskQueue = Promise.resolve();

const enqueueCartTask = (task) => {
    const run = cartTaskQueue.then(task, task);
    cartTaskQueue = run.catch(() => undefined);

    return run;
};

const showStoreMessage = (message, type = 'error') => {
    let region = document.querySelector('[data-store-action-message]');

    if (!region) {
        region = document.createElement('div');
        region.className = 'store-action-message';
        region.dataset.storeActionMessage = 'true';
        region.setAttribute('role', 'status');
        region.setAttribute('aria-live', 'polite');
        document.body.appendChild(region);
    }

    region.textContent = String(message || 'عملیات انجام نشد.');
    region.dataset.type = type;
    region.classList.add('is-visible');

    window.clearTimeout(actionMessageTimer);
    actionMessageTimer = window.setTimeout(() => {
        region.classList.remove('is-visible');
    }, 3200);
};

const cartState = {
    count: 0,
    total: 0,
    items: [],
};

const formatMoney = (value) => {
    return new Intl.NumberFormat('fa-IR').format(Number(value || 0)) + ' تومان';
};

const escapeText = (value) => String(value ?? '');

const setCartCount = (count) => {
    document.querySelectorAll('.cart-count, [data-cart-count]').forEach((node) => {
        node.textContent = count;
        node.hidden = count < 1;
    });
};

const showQuickPreview = (payload, fallbackForm = null) => {
    if (!quickPreview) return;

    const item = Array.isArray(payload.items) && payload.items.length
        ? payload.items[payload.items.length - 1]
        : null;

    const fallbackName = fallbackForm?.dataset.productName || 'محصول';
    const fallbackImage = fallbackForm?.dataset.productImage || '';

    if (quickPreviewImage) {
        quickPreviewImage.innerHTML = '';

        if (item?.image || fallbackImage) {
            const img = document.createElement('img');
            img.src = item?.image || fallbackImage;
            img.alt = '';
            quickPreviewImage.appendChild(img);
        } else {
            const placeholder = document.createElement('span');
            placeholder.textContent = 'JANAN';
            quickPreviewImage.appendChild(placeholder);
        }
    }

    if (quickPreviewName) {
        quickPreviewName.textContent = item?.name || fallbackName;
    }

    if (quickPreviewMeta) {
        const quantity = Number(item?.quantity || 1);
        quickPreviewMeta.textContent =
            quantity > 1
                ? quantity + ' عدد · ' + formatMoney(item?.line_total || 0)
                : '۱ عدد · ' + formatMoney(item?.line_total || 0);
    }

    quickPreview.hidden = false;
    quickPreview.classList.remove('is-visible');

    requestAnimationFrame(() => {
        quickPreview.classList.add('is-visible');
    });

    window.clearTimeout(quickPreviewTimer);
    quickPreviewTimer = window.setTimeout(() => {
        hideQuickPreview();
    }, 5200);
};

const animateProductToCart = (form = null) => {
    const target =
        document.querySelector('[data-cart-open]:not([hidden])') ||
        document.querySelector('[data-cart-open]');

    if (!target) return;

    const source =
        form?.closest('.product-card')?.querySelector('.product-card__media img') ||
        form?.closest('.product-detail-v2, .product-detail-page')?.querySelector('img') ||
        form?.querySelector('img') ||
        null;

    const sourceRect = source?.getBoundingClientRect?.() || form?.getBoundingClientRect?.();
    const targetRect = target.getBoundingClientRect();

    if (
        !sourceRect ||
        sourceRect.width < 1 ||
        sourceRect.height < 1 ||
        targetRect.width < 1 ||
        targetRect.height < 1
    ) {
        return;
    }

    const startX = sourceRect.left;
    const startY = sourceRect.top;
    const startWidth = Math.max(52, Math.min(96, sourceRect.width));
    const startHeight = Math.max(64, Math.min(116, sourceRect.height));

    const clone = document.createElement('div');
    clone.className = 'cart-fly-clone';
    Object.assign(clone.style, {
        left: startX + 'px',
        top: startY + 'px',
        width: startWidth + 'px',
        height: startHeight + 'px',
        position: 'fixed',
    });

    if (source?.currentSrc || source?.src) {
        const img = document.createElement('img');
        img.src = source.currentSrc || source.src;
        img.alt = '';
        clone.appendChild(img);
    } else {
        const placeholder = document.createElement('div');
        placeholder.textContent = 'JANAN';
        clone.appendChild(placeholder);
    }

    document.body.appendChild(clone);

    const endX =
        targetRect.left +
        (targetRect.width / 2) -
        (startX + startWidth / 2);

    const endY =
        targetRect.top +
        (targetRect.height / 2) -
        (startY + startHeight / 2);

    const pulseTarget = target.querySelector('svg') || target;
    const gsap = window.gsap;

    if (gsap) {
        gsap.killTweensOf(clone);
        gsap.fromTo(
            clone,
            {
                x: 0,
                y: 0,
                scale: 1,
                opacity: 1,
                rotate: 0,
            },
            {
                x: endX,
                y: endY,
                scale: 0.16,
                opacity: 0.2,
                rotate: 9,
                duration: 0.82,
                ease: 'power3.inOut',
                onComplete: () => clone.remove(),
            }
        );

        gsap.timeline()
            .to(pulseTarget, {
                scale: 1.18,
                duration: 0.12,
                ease: 'power2.out',
            })
            .to(pulseTarget, {
                scale: 1,
                duration: 0.24,
                ease: 'back.out(2)',
            });
        return;
    }

    clone.animate(
        [
            {
                transform: 'translate3d(0,0,0) scale(1)',
                opacity: 1,
            },
            {
                transform: `translate3d(${endX}px,${endY}px,0) scale(.16)`,
                opacity: .15,
            },
        ],
        {
            duration: 820,
            easing: 'cubic-bezier(.22,.75,.26,1)',
            fill: 'forwards',
        }
    ).onfinish = () => clone.remove();
};
const hideQuickPreview = () => {
    if (!quickPreview) return;

    quickPreview.classList.remove('is-visible');
    window.clearTimeout(quickPreviewTimer);

    window.setTimeout(() => {
        if (!quickPreview.classList.contains('is-visible')) {
            quickPreview.hidden = true;
        }
    }, 180);
};

const renderCart = (payload) => {
    cartState.count = Number(payload.count || 0);
    cartState.total = Number(payload.total || 0);
    cartState.items = Array.isArray(payload.items) ? payload.items : [];

    setCartCount(cartState.count);

    const body = drawer?.querySelector('[data-cart-body]');
    const total = drawer?.querySelector('[data-cart-total]');
    const checkout = drawer?.querySelector('[data-cart-checkout]');

    if (!body) return;

    if (!cartState.items.length) {
        body.innerHTML = '';
        const empty = document.createElement('div');
        empty.className = 'cart-drawer__empty';

        const title = document.createElement('strong');
        title.textContent = 'سبد خرید خالی است.';

        const link = document.createElement('a');
        link.className = 'button button--ghost';
        link.href = '/products';
        link.textContent = 'مشاهده محصولات';

        empty.append(title, link);
        body.appendChild(empty);

        if (checkout) {
            checkout.setAttribute('aria-disabled', 'true');
            checkout.dataset.disabled = 'true';
        }
    } else {
        body.innerHTML = '';
        const items = document.createElement('div');
        items.className = 'cart-drawer__items';

        cartState.items.forEach((item) => {
            const article = document.createElement('article');
            article.className = 'cart-drawer__item';

            if (item.image) {
                const img = document.createElement('img');
                img.src = item.image;
                img.alt = escapeText(item.name);
                article.appendChild(img);
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'cart-drawer__item-placeholder';
                placeholder.textContent = 'JANAN';
                article.appendChild(placeholder);
            }

            const copy = document.createElement('div');
            copy.className = 'cart-drawer__item-copy';

            const name = document.createElement('strong');
            name.textContent = escapeText(item.name);

            const variant = document.createElement('span');
            variant.textContent = escapeText(item.variant_name || '');

            const price = document.createElement('span');
            price.className = 'cart-drawer__item-price';
            price.textContent = formatMoney(item.line_total);

            const details = document.createElement('a');
            details.className = 'cart-drawer__item-detail';
            details.href = item.product_url || '/products';
            details.textContent = 'جزئیات محصول ↗';

            const qty = document.createElement('div');
            qty.className = 'cart-drawer__qty';

            const minus = document.createElement('button');
            minus.type = 'button';
            minus.textContent = '−';
            minus.dataset.cartQty = 'decrease';
            minus.dataset.itemId = item.id;

            const number = document.createElement('span');
            number.textContent = String(item.quantity);

            const plus = document.createElement('button');
            plus.type = 'button';
            plus.textContent = '+';
            plus.dataset.cartQty = 'increase';
            plus.dataset.itemId = item.id;
            plus.disabled = Number(item.quantity) >= Number(item.stock);

            qty.append(minus, number, plus);

            copy.append(name, variant, price, qty, details);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'cart-drawer__remove';
            remove.textContent = '×';
            remove.dataset.cartRemove = item.id;
            remove.setAttribute('aria-label', 'حذف محصول');

            article.append(copy, remove);
            items.appendChild(article);
        });

        body.appendChild(items);

        if (checkout) {
            checkout.removeAttribute('aria-disabled');
            checkout.dataset.disabled = 'false';
        }
    }

    if (total) total.textContent = formatMoney(cartState.total);
};

const fetchCart = async () => {
    const response = await fetch('/cart/summary', {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const payload = await response.json();

    if (!response.ok) {
        throw new Error(payload.message || 'خطا در دریافت سبد');
    }

    renderCart(payload);
};

const mutateCart = async (url, method = 'POST', body = null) => {
    const response = await fetch(url, {
        method,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            ...(body ? { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' } : {}),
        },
        body: body ? new URLSearchParams(body).toString() : null,
    });

    const payload = await response.json();

    if (!response.ok) {
        throw new Error(payload.message || 'عملیات سبد انجام نشد.');
    }

    renderCart(payload);
};

const openDrawer = async (refresh = true) => {
    if (!drawer) return;

    drawer.classList.add('is-open');
    drawer.setAttribute('aria-hidden', 'false');
    document.body.classList.add('cart-drawer-open');

    const body = drawer.querySelector('[data-cart-body]');
    if (body) {
        body.innerHTML = '<div class="cart-drawer__loading">در حال دریافت سبد خرید...</div>';
    }

    if (!refresh) return;

    try {
        await enqueueCartTask(() => fetchCart());
    } catch (error) {
        if (body) {
            body.textContent = error.message;
        }
    }
};



const closeDrawer = () => {
    if (!drawer) return;

    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('cart-drawer-open');
};

document.addEventListener('click', async (event) => {
    const openTrigger = event.target.closest('[data-cart-open]');
    const closeTrigger = event.target.closest('[data-cart-close]');
    const qtyTrigger = event.target.closest('[data-cart-qty]');
    const removeTrigger = event.target.closest('[data-cart-remove]');
    const checkoutTrigger = event.target.closest('[data-cart-checkout]');
    const previewCloseTrigger = event.target.closest('[data-cart-preview-close]');

    if (checkoutTrigger?.dataset.disabled === 'true') {
        event.preventDefault();
        return;
    }

    if (previewCloseTrigger) {
        event.preventDefault();
        hideQuickPreview();
        return;
    }

    if (openTrigger) {
        event.preventDefault();
        hideQuickPreview();
        await openDrawer();
        return;
    }

    if (closeTrigger) {
        event.preventDefault();
        closeDrawer();
        return;
    }

    if (qtyTrigger) {
        event.preventDefault();

        const item = cartState.items.find(
            (entry) => String(entry.id) === String(qtyTrigger.dataset.itemId)
        );

        if (!item) return;

        const delta = qtyTrigger.dataset.cartQty === 'increase' ? 1 : -1;
        const quantity = Math.max(0, Number(item.quantity) + delta);

        try {
            await enqueueCartTask(() =>
                mutateCart(
                    '/cart/' + item.id,
                    'PUT',
                    { quantity }
                )
            );
        } catch (error) {
            showStoreMessage(error.message);
        }

        return;
    }

    if (removeTrigger) {
        event.preventDefault();

        try {
            await enqueueCartTask(() =>
                mutateCart(
                    '/cart/' + removeTrigger.dataset.cartRemove,
                    'DELETE'
                )
            );
        } catch (error) {
            showStoreMessage(error.message);
        }
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('.quick-add-form, .product-purchase-form');

    if (!form) return;

    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    // بازخورد فوری قبل از برگشت پاسخ شبکه.
    showQuickPreview(
        {
            items: [
                {
                    name: form.dataset.productName || 'محصول',
                    image: form.dataset.productImage || '',
                    quantity: Number(formData.get('quantity') || 1),
                    line_total: 0,
                },
            ],
        },
        form
    );

    if (button) {
        button.disabled = true;
        button.dataset.originalText = button.textContent.trim();
        button.textContent = 'در حال افزودن...';
    }

    try {
        await enqueueCartTask(async () => {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message || 'افزودن به سبد انجام نشد.');
            }

            setCartCount(payload.count);

            // Only animate after the server confirms the real cart mutation.
            animateProductToCart(form);

            // نمایش فوری کنار آیکن سبد؛ رندر Drawer از همین پاسخ قطعی انجام می‌شود.
            showQuickPreview(payload, form);
            renderCart(payload);
        });
    } catch (error) {
        showStoreMessage(error.message);
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = button.dataset.originalText || 'افزودن به سبد';
        }
    }
});

document.addEventListener('click', (event) => {
    if (
        quickPreview &&
        !quickPreview.hidden &&
        !quickPreview.contains(event.target) &&
        !event.target.closest('[data-cart-open]')
    ) {
        hideQuickPreview();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        hideQuickPreview();
        closeDrawer();
    }
});

})();