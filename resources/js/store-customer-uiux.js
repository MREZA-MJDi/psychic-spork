/* JANAN STORE / CUSTOMER UIUX — checkout interaction layer */
(() => {
    const syncCheckoutChoices = () => {
        const form = document.querySelector('.checkout-form');
        if (!form) return;

        const wholesale = form.querySelector('input[name="order_type"]:checked')?.value === 'wholesale';
        const cheque = form.querySelector('input[name="payment_method"][value="cheque"]');
        const online = form.querySelector('input[name="payment_method"][value="online"]');
        const chequeFields = form.querySelector('[data-cheque-fields]');

        if (cheque) {
            cheque.disabled = !wholesale;

            if (!wholesale && cheque.checked) {
                cheque.checked = false;
                online?.click();
            }
        }

        if (chequeFields) {
            chequeFields.hidden = !wholesale || !cheque?.checked;
        }
    };

    document.addEventListener('change', (event) => {
        if (
            event.target.matches('.checkout-form input[name="order_type"], .checkout-form input[name="payment_method"]')
        ) {
            syncCheckoutChoices();
        }
    });

    document.addEventListener('DOMContentLoaded', syncCheckoutChoices);
})();
