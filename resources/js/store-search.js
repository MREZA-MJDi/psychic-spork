(() => {
    const forms = [...document.querySelectorAll('[data-store-search]')];
    if (!forms.length) return;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatMoney = (value) => (
        new Intl.NumberFormat('fa-IR').format(Number(value || 0))
    );

    forms.forEach((form) => {
        const input = form.querySelector('[data-search-input]');
        const clear = form.querySelector('[data-search-clear]');
        const results = form.parentElement?.querySelector('[data-search-results]');
        const list = results?.querySelector('[data-search-results-list]');
        const status = results?.querySelector('[data-search-status]');
        const endpoint = form.dataset.suggestionsUrl;

        if (!input || !endpoint || !results || !list) return;

        let timer = null;
        let controller = null;

        const setVisible = (visible) => {
            results.hidden = !visible;
        };

        const setStatus = (text) => {
            if (status) status.textContent = text;
        };

        const render = (items, query) => {
            list.innerHTML = '';

            if (!items.length) {
                list.innerHTML = `
                    <div class="search-suggestions__empty">
                        <span>⌕</span>
                        <strong>نتیجه‌ای برای «${escapeHtml(query)}» پیدا نشد.</strong>
                        <small>عبارت کوتاه‌تر یا نام برند را امتحان کن.</small>
                    </div>
                `;

                setVisible(true);
                setStatus('نتیجه‌ای پیدا نشد');
                return;
            }

            items.forEach((item) => {
                const anchor = document.createElement('a');
                anchor.className = 'search-suggestion';
                anchor.href = item.url;

                const media = document.createElement('span');
                media.className = 'search-suggestion__media';

                if (item.image) {
                    const img = document.createElement('img');
                    img.src = item.image;
                    img.alt = '';
                    img.loading = 'lazy';
                    img.decoding = 'async';
                    media.appendChild(img);
                } else {
                    media.textContent = 'J';
                }

                const copy = document.createElement('span');
                copy.className = 'search-suggestion__copy';

                const title = document.createElement('strong');
                title.textContent = item.name;

                const meta = document.createElement('small');
                meta.textContent = [item.brand, item.category]
                    .filter(Boolean)
                    .join(' · ') || 'محصول جانان';

                copy.append(title, meta);

                const price = document.createElement('span');
                price.className = 'search-suggestion__price';
                price.textContent = item.price
                    ? formatMoney(item.price) + ' تومان'
                    : 'مشاهده محصول';

                anchor.append(media, copy, price);
                list.appendChild(anchor);
            });

            setVisible(true);
            setStatus(`${new Intl.NumberFormat('fa-IR').format(items.length)} نتیجه سریع`);
        };

        const search = async () => {
            const query = input.value.trim();
            clear?.toggleAttribute('hidden', !query);

            if (query.length < 2) {
                list.innerHTML = '';
                setStatus('حداقل ۲ حرف وارد کن');
                setVisible(false);
                return;
            }

            controller?.abort();
            controller = new AbortController();

            setVisible(true);
            setStatus('در حال جستجو…');
            list.innerHTML = `
                <div class="search-suggestions__loading">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;

            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('q', query);

                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                const payload = await response.json();

                if (!response.ok) {
                    throw new Error(payload.message || 'جستجو انجام نشد.');
                }

                render(Array.isArray(payload.items) ? payload.items : [], query);
            } catch (error) {
                if (error.name === 'AbortError') return;

                list.innerHTML = `
                    <div class="search-suggestions__empty">
                        <strong>جستجو در حال حاضر در دسترس نیست.</strong>
                        <small>Enter بزن تا جستجوی کامل سرور اجرا شود.</small>
                    </div>
                `;
                setVisible(true);
                setStatus('خطا در دریافت پیشنهادها');
            }
        };

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(search, 220);
        });

        clear?.addEventListener('click', () => {
            input.value = '';
            input.focus();
            list.innerHTML = '';
            setVisible(false);
            setStatus('نام محصول یا برند را وارد کن');
            clear.setAttribute('hidden', '');
        });

        form.addEventListener('submit', () => {
            window.clearTimeout(timer);
        });

        document.addEventListener('click', (event) => {
            if (!form.contains(event.target) && !results.contains(event.target)) {
                setVisible(false);
            }
        });
    });
})();
