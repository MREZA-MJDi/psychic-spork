const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content') || '';

const drawer = document.querySelector('[data-cart-drawer]');

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

            copy.append(name, variant, price, qty);

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
        await fetchCart();
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

    if (checkoutTrigger?.dataset.disabled === 'true') {
        event.preventDefault();
        return;
    }

    if (openTrigger) {
        event.preventDefault();
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
            await mutateCart(
                '/cart/' + item.id,
                'PUT',
                { quantity }
            );
        } catch (error) {
            window.alert(error.message);
        }

        return;
    }

    if (removeTrigger) {
        event.preventDefault();

        try {
            await mutateCart(
                '/cart/' + removeTrigger.dataset.cartRemove,
                'DELETE'
            );
        } catch (error) {
            window.alert(error.message);
        }
    }
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('.quick-add-form');

    if (!form) return;

    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    if (button) {
        button.disabled = true;
        button.dataset.originalText = button.textContent.trim();
        button.textContent = 'در حال افزودن...';
    }

    try {
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

        renderCart(payload);
        await openDrawer(false);
    } catch (error) {
        window.alert(error.message);
    } finally {
        if (button) {
            button.disabled = false;
            button.textContent = button.dataset.originalText || 'افزودن به سبد';
        }
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeDrawer();
    }
});
