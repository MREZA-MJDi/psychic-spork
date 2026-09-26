<aside
    class="cart-drawer"
    data-cart-drawer
    aria-hidden="true"
    aria-labelledby="cart-drawer-title"
>
    <button
        class="cart-drawer__backdrop"
        type="button"
        data-cart-close
        aria-label="بستن سبد خرید"
    ></button>

    <section class="cart-drawer__panel" role="dialog" aria-modal="true">
        <header class="cart-drawer__head">
            <div>
                <span class="eyebrow">JANAN / BAG</span>
                <h2 id="cart-drawer-title">سبد خرید</h2>
            </div>

            <button
                class="icon-button cart-drawer__close"
                type="button"
                data-cart-close
                aria-label="بستن"
            >
                ×
            </button>
        </header>

        <div class="cart-drawer__body" data-cart-body>
            <div class="cart-drawer__loading">
                در حال دریافت سبد خرید...
            </div>
        </div>

        <footer class="cart-drawer__foot">
            <div class="cart-drawer__total">
                <span>جمع سفارش</span>
                <strong data-cart-total>۰ تومان</strong>
            </div>

            <a
                href="{{ route('checkout') }}"
                class="button button--dark cart-drawer__checkout"
                data-cart-checkout
            >
                ادامه و پرداخت
                <span aria-hidden="true">←</span>
            </a>

            <a
                href="{{ route('cart') }}"
                class="cart-drawer__full-link"
            >
                مشاهده سبد کامل
            </a>
        </footer>
    </section>
</aside>
