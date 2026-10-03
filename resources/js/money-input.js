const toLatinDigits = (value) => value
    .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
    .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)));

const moneyDigits = (value) => toLatinDigits(value).replace(/\D/g, '');
const formatMoney = (digits) => digits.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

export function initMoneyInputs(root = document) {
    root.querySelectorAll('[data-money-input]').forEach((input) => {
        if (input.dataset.moneyReady === 'true') return;
        input.dataset.moneyReady = 'true';
        input.type = 'text';
        input.inputMode = 'numeric';
        input.autocomplete = 'off';

        const format = () => {
            const cursor = input.selectionStart ?? input.value.length;
            const digitsBeforeCursor = moneyDigits(input.value.slice(0, cursor)).length;
            const digits = moneyDigits(input.value);
            input.value = formatMoney(digits);
            let nextCursor = 0;
            let seenDigits = 0;
            while (nextCursor < input.value.length && seenDigits < digitsBeforeCursor) {
                if (/\d/.test(input.value[nextCursor])) seenDigits += 1;
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
        input.value = moneyDigits(input.value);
    });
}, true);
