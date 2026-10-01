(() => {
    if (window.__JANAN_PRODUCT_SHOW_INITIALIZED__) return;
    window.__JANAN_PRODUCT_SHOW_INITIALIZED__ = true;

    const root = document.querySelector('[data-product-purchase]');
    if (!root) return;

    const gallery = document.querySelector('[data-product-gallery]');
    const galleryMain = gallery?.querySelector('[data-gallery-main]');
    const galleryThumbs = [...(gallery?.querySelectorAll('[data-gallery-thumb]') || [])];
    const galleryCurrent = gallery?.querySelector('[data-gallery-current]');
    const galleryMode = gallery?.querySelector('[data-gallery-mode]');
    const galleryTotal = gallery?.querySelector('[data-gallery-total]');


    const variants = [...root.querySelectorAll('[data-product-variant]')];
    const form = root.querySelector('.product-purchase-form');
    const quantityInput = root.querySelector('[data-product-quantity-input]');
    const decrease = root.querySelector('[data-product-quantity="decrease"]');
    const increase = root.querySelector('[data-product-quantity="increase"]');

    const price = root.querySelector('[data-product-price]');
    const regularPrice = root.querySelector('[data-product-regular-price]');
    const wholesalePrice = root.querySelector('[data-product-wholesale-price]');
    const stock = root.querySelector('[data-product-stock]');
    const skuNodes = [...document.querySelectorAll('[data-product-sku]')];
    const variantLabel = root.querySelector('[data-product-variant-label]');
    const addButton = root.querySelector('[data-product-add-button]');
    const addLabel = root.querySelector('[data-product-add-label]');

    const formatMoney = (value) => (
        new Intl.NumberFormat('fa-IR').format(Number(value || 0))
    );

    const clampQuantity = () => {
        if (!quantityInput) return 1;

        const max = Math.max(1, Number(quantityInput.max || 1));
        const value = Math.max(1, Math.min(max, Number(quantityInput.value || 1)));

        quantityInput.value = String(value);
        return value;
    };

    const applyVariant = (button) => {
        if (!button) return;

        variants.forEach((item) => {
            const selected = item === button;
            item.classList.toggle('is-selected', selected);
            item.setAttribute('aria-pressed', String(selected));
        });

        const variantStock = Number(button.dataset.variantStock || 0);
        const isLowStock = button.dataset.variantLowStock === '1';
        const onSale = button.dataset.variantSale === '1';
        const variantPrice = Number(button.dataset.variantPrice || 0);
        const variantWholesale = button.dataset.variantWholesale || '';
        const regular = Number(button.dataset.variantRegular || 0);
        const action = button.dataset.variantAction || '';
        const label = button.dataset.variantLabel || '';
        const variantSku = button.dataset.variantSku || '';
        const variantImage = button.dataset.variantImage || '';
        const variantImageAlt = button.dataset.variantImageAlt || '';


        if (form && action) {
            form.action = action;
        }

        if (price) {
            price.textContent = formatMoney(variantPrice);
        }

        if (regularPrice) {
            regularPrice.textContent = formatMoney(regular);
            regularPrice.hidden = !onSale;
        }

        if (wholesalePrice) {
            wholesalePrice.textContent = variantWholesale
                ? `عمده: ${formatMoney(variantWholesale)} تومان`
                : '';
            wholesalePrice.hidden = !variantWholesale;
        }

        if (variantLabel) {
            variantLabel.textContent = label || 'انتخاب نشده';
        }

        skuNodes.forEach((node) => {
            node.textContent = variantSku || '—';
        });

        if (galleryMain && variantImage) {
            galleryMain.src = variantImage;
            galleryMain.alt = variantImageAlt || galleryMain.alt || '';
            galleryThumbs.forEach((item) => {
                item.classList.remove('is-active');
                item.setAttribute('aria-pressed', 'false');
            });

            if (galleryMode) {
                galleryMode.textContent = 'VARIANT';
            }

            if (galleryCurrent) {
                galleryCurrent.textContent = String(button.dataset.variantIndex || '01').padStart(2, '0');
            }

            if (galleryTotal) {
                galleryTotal.textContent = String(variants.length).padStart(2, '0');
            }

            if (form) {
                form.dataset.productImage = variantImage;
            }
        }

        if (stock) {
            stock.classList.toggle('is-out', variantStock < 1);
            stock.classList.toggle('is-low', variantStock > 0 && isLowStock);

            stock.textContent = variantStock < 1
                ? 'فعلاً ناموجود'
                : (isLowStock ? 'موجودی محدود' : 'موجود و آماده سفارش');
        }

        if (quantityInput) {
            quantityInput.max = String(Math.max(1, variantStock));
            clampQuantity();
        }

        if (addButton) {
            addButton.disabled = variantStock < 1;
        }

        if (addLabel) {
            addLabel.textContent = variantStock > 0
                ? 'افزودن به سبد خرید'
                : 'ناموجود';
        }
    };

    variants.forEach((button) => {
        button.addEventListener('click', () => applyVariant(button));
    });

    decrease?.addEventListener('click', () => {
        if (!quantityInput) return;
        quantityInput.value = String(Math.max(1, Number(quantityInput.value || 1) - 1));
        clampQuantity();
    });

    increase?.addEventListener('click', () => {
        if (!quantityInput) return;
        quantityInput.value = String(Number(quantityInput.value || 1) + 1);
        clampQuantity();
    });

    quantityInput?.addEventListener('input', clampQuantity);

    galleryThumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (!galleryMain) return;

            const src = thumb.dataset.gallerySrc;
            if (!src) return;

            galleryMain.src = src;
            galleryMain.alt = thumb.dataset.galleryAlt || galleryMain.alt;

            galleryThumbs.forEach((item) => {
                const active = item === thumb;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });

            if (galleryMode) {
                galleryMode.textContent = 'PRODUCT';
            }

            if (galleryCurrent) {
                galleryCurrent.textContent = thumb.dataset.galleryIndex || '01';
            }

            if (galleryTotal) {
                galleryTotal.textContent = String(galleryThumbs.length).padStart(2, '0');
            }

            if (form && !variants.some((item) => item.classList.contains('is-selected') && item.dataset.variantImage)) {
                form.dataset.productImage = thumb.dataset.gallerySrc || '';
            }
        });
    });

    const initial = variants.find((button) => button.classList.contains('is-selected'));
    if (initial) {
        applyVariant(initial);
    }

    clampQuantity();
})();
