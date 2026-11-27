const dayMs = 86400000;
const formatter = new Intl.DateTimeFormat('en-US-u-ca-persian', {
    timeZone: 'UTC', year: 'numeric', month: 'numeric', day: 'numeric',
});
const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
const iso = date => date.toISOString().slice(0, 10);
const shift = (date, days) => new Date(date.getTime() + days * dayMs);
const fa = value => String(value).replace(/[0-9]/g, n => '۰۱۲۳۴۵۶۷۸۹'[Number(n)]);
const latin = value => value.replace(/[۰-۹٠-٩]/g, n => String('۰۱۲۳۴۵۶۷۸۹'.includes(n) ? '۰۱۲۳۴۵۶۷۸۹'.indexOf(n) : '٠١٢٣٤٥٦٧٨٩'.indexOf(n)));

export function persianParts(date) {
    const parts = Object.fromEntries(formatter.formatToParts(date).map(({type, value}) => [type, value]));
    return { year: Number(parts.year), month: Number(parts.month), day: Number(parts.day) };
}

export function parsePersianDate(value) {
    const match = latin(value.trim()).match(/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})$/);
    if (!match) return null;
    const [, y, m, d] = match.map(Number);
    if (y < 1300 || y > 1600 || m < 1 || m > 12 || d < 1 || d > 31) return null;
    // Locate the actual Persian date via ICU, rather than assuming a leap-year cycle.
    const start = new Date(Date.UTC(y + 621, 2, 18));
    for (let offset = 0; offset < 370; offset++) {
        const date = shift(start, offset);
        const p = persianParts(date);
        if (p.year === y && p.month === m && p.day === d) return iso(date);
    }
    return null;
}

function dateLabel(date) {
    const {year, month, day} = persianParts(date);
    return fa([year, String(month).padStart(2, '0'), String(day).padStart(2, '0')].join('/'));
}

export function initJalaliDates() {
    if (formatter.resolvedOptions().calendar !== 'persian') return;
    document.querySelectorAll('input[data-jalali-date]').forEach(native => {
        if (native.dataset.jalaliReady) return;
        const minimum = native.min;
        const initial = native.value;
        const label = native.closest('label');
        if (!label || !minimum) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'jalali-date';
        label.before(wrapper);
        wrapper.append(label);
        const input = document.createElement('input');
        input.type = 'text';
        input.id = native.id + '-display';
        input.required = native.required;
        input.placeholder = '۱۴۰۵/۰۹/۰۱';
        input.autocomplete = 'off';
        input.dir = 'ltr';
        input.setAttribute('aria-describedby', native.id + '-hint');
        input.maxLength = 10;
        native.type = 'hidden';
        native.required = false;
        label.htmlFor = input.id;
        label.append(input);
        const hint = document.createElement('small');
        hint.id = native.id + '-hint';
        hint.textContent = 'تاریخ شمسی (سال/ماه/روز)';
        label.append(hint);
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'jalali-date__toggle';
        toggle.textContent = 'انتخاب از تقویم شمسی';
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-controls', native.id + '-calendar');
        const panel = document.createElement('div');
        panel.id = native.id + '-calendar';
        panel.className = 'jalali-date__panel';
        panel.hidden = true;
        wrapper.append(toggle, panel);
        const minDate = new Date(minimum + 'T00:00:00Z');
        let cursor = initial && initial >= minimum ? new Date(initial + 'T00:00:00Z') : minDate;
        const validDate = value => value ? new Date(value + 'T00:00:00Z') : null;
        if (initial) input.value = dateLabel(validDate(initial));
        function sync() {
            const result = parsePersianDate(input.value);
            native.value = result && result >= minimum ? result : '';
            input.setCustomValidity(!input.value ? '' : !result ? 'تاریخ شمسی معتبر وارد کنید.' : result < minimum ? 'تاریخ سررسید نمی‌تواند قبل از امروز باشد.' : '');
        }
        function close(focus = false) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            if (focus) toggle.focus();
        }
        function render() {
            panel.replaceChildren();
            const p = persianParts(cursor);
            const first = shift(cursor, 1 - p.day);
            const previous = shift(first, -1);
            const head = document.createElement('div');
            head.className = 'jalali-date__head';
            const heading = document.createElement('strong');
            heading.textContent = months[p.month - 1] + ' ' + fa(p.year);
            heading.setAttribute('aria-live', 'polite');
            function button(text, label, action) {
                const element = document.createElement('button');
                element.type = 'button';
                element.textContent = text;
                element.setAttribute('aria-label', label);
                element.addEventListener('click', action);
                return element;
            }
            const prev = button('›', 'ماه قبل', () => { cursor = previous; render(); panel.querySelector('button:not(:disabled)')?.focus(); });
            prev.disabled = iso(previous) < minimum;
            const next = button('‹', 'ماه بعد', () => { cursor = shift(first, count); render(); panel.querySelector('.jalali-date__head button:last-child')?.focus(); });
            head.append(prev, heading, next);
            panel.append(head);
            const grid = document.createElement('div');
            grid.className = 'jalali-date__grid';
            for (const weekday of ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج']) {
                const cell = document.createElement('span');
                cell.textContent = weekday;
                grid.append(cell);
            }
            for (let i = 0; i < (first.getUTCDay() + 1) % 7; i++) grid.append(document.createElement('span'));
            let count = 0;
            for (; count < 31; count++) {
                const date = shift(first, count);
                if (persianParts(date).month !== p.month) break;
                const value = iso(date);
                const day = button(fa(count + 1), dateLabel(date), () => {
                    input.value = dateLabel(date);
                    sync();
                    native.dispatchEvent(new Event('change', {bubbles: true}));
                    close(true);
                });
                day.disabled = value < minimum;
                day.setAttribute('aria-pressed', String(value === native.value));
                if (value === minimum) day.setAttribute('aria-current', 'date');
                grid.append(day);
            }
            panel.append(grid);
        }
        input.addEventListener('input', sync);
        input.addEventListener('change', () => { sync(); if (native.value) input.value = dateLabel(validDate(native.value)); });
        toggle.addEventListener('click', () => {
            if (!panel.hidden) return close();
            cursor = validDate(native.value) || minDate;
            render();
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
        });
        wrapper.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !panel.hidden) { event.preventDefault(); close(true); }
        });
        document.addEventListener('click', event => { if (!wrapper.contains(event.target)) close(); });
        native.dataset.jalaliReady = 'true';
        sync();
    });
}
