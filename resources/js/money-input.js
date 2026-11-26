import { persianDigits } from './persian-digits.js';

export const normalizeMoney = (value) => String(value ?? '')
    .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
    .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)))
    .replace(/[٬,،\s]/g, '')
    .replace(/٫/g, '.');

export const formatMoney = (value) => {
    const raw = normalizeMoney(value);
    if (!/^-?\d*(?:\.\d*)?$/.test(raw)) return value;
    const [integer, fraction] = raw.split('.');
    const grouped = integer.replace(/\B(?=(\d{3})+(?!\d))/g, '٬');
    return persianDigits(fraction === undefined ? grouped : grouped + '٫' + fraction);
};

export function initMoneyInputs(root = document) {
    root.querySelectorAll('[data-money-input]').forEach((input) => {
        if (input.dataset.moneyReady === 'true') return;
        input.dataset.moneyReady = 'true';
        input.type = 'text';
        input.inputMode = 'decimal';
        input.autocomplete = 'off';

        const format = () => {
            const cursor = input.selectionStart ?? input.value.length;
            const charactersBeforeCursor = normalizeMoney(input.value.slice(0, cursor)).length;
            input.value = formatMoney(input.value);
            let nextCursor = 0;
            while (nextCursor < input.value.length
                && normalizeMoney(input.value.slice(0, nextCursor)).length < charactersBeforeCursor) {
                nextCursor += 1;
            }
            input.setSelectionRange(nextCursor, nextCursor);
        };

        input.addEventListener('input', format);
        input.addEventListener('blur', format);
        format();
    });
}

document.addEventListener('submit', (event) => {
    event.target.querySelectorAll('[data-money-input]').forEach((input) => {
        input.value = normalizeMoney(input.value);
    });
}, true);
