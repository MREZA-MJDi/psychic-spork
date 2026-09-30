@extends('layouts.store')

@section('title', 'تکمیل سفارش — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">JANAN / CHECKOUT</span>
        <h1>تکمیل سفارش</h1>
        <p>اطلاعات ارسال را وارد کن؛ سپس برای پرداخت آنلاین به درگاه منتقل می‌شوی.</p>
    </div>
</section>

<section class="section-block">
    <div class="container checkout-layout">

        <section class="checkout-card">
            <div class="customer-stepper" aria-label="مراحل خرید">
                <div class="customer-stepper__item">
                    <span class="customer-stepper__index">01</span>
                    <div><strong>سبد خرید</strong><span>انتخاب‌ها</span></div>
                </div>
                <div class="customer-stepper__item" aria-current="step">
                    <span class="customer-stepper__index">02</span>
                    <div><strong>اطلاعات سفارش</strong><span>ارسال و پرداخت</span></div>
                </div>
                <div class="customer-stepper__item">
                    <span class="customer-stepper__index">03</span>
                    <div><strong>تکمیل</strong><span>تأیید سفارش</span></div>
                </div>
            </div>

            <div class="section-head">
                <div>
                    <span class="eyebrow">DELIVERY DETAILS</span>
                    <h2>اطلاعات گیرنده</h2>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('checkout.store') }}"
                class="checkout-form"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="form-grid">
                    <label>
                        نام و نام خانوادگی
                        <input
                            name="customer_name"
                            value="{{ old('customer_name', auth()->user()?->name) }}"
                            required
                        >
                    </label>

                    <label>
                        شماره موبایل
                        <input
                            name="customer_phone"
                            value="{{ old('customer_phone', auth()->user()?->phone) }}"
                            inputmode="tel"
                            required
                        >
                    </label>

                    <label>
                        ایمیل
                        <input
                            type="email"
                            name="customer_email"
                            value="{{ old('customer_email', auth()->user()?->email) }}"
                        >
                    </label>

                    <label>
                        استان
                        <input
                            name="shipping_province"
                            value="{{ old('shipping_province') }}"
                        >
                    </label>

                    <label>
                        شهر
                        <input
                            name="shipping_city"
                            value="{{ old('shipping_city') }}"
                        >
                    </label>

                    <label class="form-grid__full">
                        آدرس کامل
                        <textarea
                            name="shipping_address"
                            rows="5"
                            required
                        >{{ old('shipping_address') }}</textarea>
                    </label>

                    <label>
                        کد پستی
                        <input
                            name="postal_code"
                            value="{{ old('postal_code') }}"
                            inputmode="numeric"
                        >
                    </label>

                    <label>
                        یادداشت سفارش
                        <input
                            name="customer_note"
                            value="{{ old('customer_note') }}"
                        >
                    </label>
                </div>

                <div class="checkout-payment" data-checkout-options>
                    <div class="checkout-option-group">
                        <span class="eyebrow">ORDER TYPE</span>
                        <strong>نوع خرید</strong>
                        <div class="form-grid checkout-option-grid">
                            <label>
                                <input
                                    type="radio"
                                    name="order_type"
                                    value="retail"
                                    @checked(old('order_type', 'retail') === 'retail')
                                    data-order-type="retail"
                                >
                                خرید عادی
                            </label>

                            <label>
                                <input
                                    type="radio"
                                    name="order_type"
                                    value="wholesale"
                                    @checked(old('order_type') === 'wholesale')
                                    data-order-type="wholesale"
                                >
                                خرید عمده
                            </label>
                            <small class="admin-help checkout-option-help">
                                سفارش عمده آنلاین برای همه باز است؛ فقط پرداخت چکی نیاز به مجوز مدیر دارد.
                            </small>
                        </div>
                    </div>

                    <div>
                        <span class="eyebrow">PAYMENT</span>
                        <strong>روش پرداخت</strong>
                        <div class="form-grid checkout-option-grid">
                            <label>
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="online"
                                    @checked(old('payment_method', 'online') === 'online')
                                    data-payment-method="online"
                                >
                                پرداخت آنلاین
                            </label>

                            @if($chequeEnabled)
                                <label data-cheque-method-option>
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="cheque"
                                        @checked(old('payment_method') === 'cheque')
                                        data-payment-method="cheque"
                                    >
                                    پرداخت چکی
                                </label>
                            @endif
                        </div>

                        <p data-online-help class="checkout-payment-help">
                            مبلغ نهایی پس از ثبت سفارش به درگاه امن پرداخت منتقل می‌شود.
                        </p>

                        @if($chequeEnabled)
                            <p data-cheque-help hidden class="checkout-payment-help">
                                مجوز پرداخت چکی این حساب فعال است و فقط برای همین حساب قابل استفاده است.
                                @if($chequeMaxOrderAmount !== null)
                                    سقف هر سفارش:
                                    {{ number_format((float) $chequeMaxOrderAmount) }}
                                    تومان.
                                @else
                                    سقف مبلغی برای این مجوز تعیین نشده است.
                                @endif
                            </p>
                        @endif
                    </div>

                    @if($chequeEnabled)
                        <div
                            data-cheque-fields
                            hidden
                            class="checkout-cheque-fields"
                        >
                            <div class="form-grid">
                                <label>
                                    شناسه صیادی *
                                    <input name="sayad_id" inputmode="numeric" maxlength="16" data-cheque-required>
                                </label>

                                <label>
                                    شماره چک *
                                    <input name="cheque_number" maxlength="100" data-cheque-required>
                                </label>

                                <label>
                                    نام بانک *
                                    <input name="bank_name" maxlength="120" data-cheque-required>
                                </label>

                                <label>
                                    صاحب حساب
                                    <input name="account_holder" maxlength="160">
                                </label>

                                <label>
                                    تاریخ سررسید *
                                    <input type="date" name="due_date" data-cheque-required>
                                </label>

                                <label>
                                    تصویر چک *
                                    <input type="file" name="cheque_image" accept="image/jpeg,image/png,image/webp" data-cheque-required>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <button
                    class="button button--primary checkout-submit"
                    type="submit"
                >
                    ادامه و پرداخت
                </button>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const orderTypeInputs = document.querySelectorAll('[data-order-type]');
                        const paymentInputs = document.querySelectorAll('[data-payment-method]');
                        const chequeOption = document.querySelector('[data-cheque-method-option]');
                        const chequePayment = document.querySelector('[data-payment-method="cheque"]');
                        const chequeFields = document.querySelector('[data-cheque-fields]');
                        const chequeRequired = document.querySelectorAll('[data-cheque-required]');
                        const onlineHelp = document.querySelector('[data-online-help]');
                        const chequeHelp = document.querySelector('[data-cheque-help]');

                        const syncCheckoutOptions = () => {
                            const orderType = document.querySelector('[data-order-type]:checked')?.value || 'retail';
                            const isWholesale = orderType === 'wholesale';

                            if (chequeOption) {
                                chequeOption.hidden = !isWholesale;
                            }

                            if (!isWholesale && chequePayment?.checked) {
                                const online = document.querySelector('[data-payment-method="online"]');
                                if (online) online.checked = true;
                            }

                            const paymentMethod = document.querySelector('[data-payment-method]:checked')?.value || 'online';
                            const isCheque = isWholesale && paymentMethod === 'cheque';

                            if (chequeFields) chequeFields.hidden = !isCheque;
                            chequeRequired.forEach((input) => {
                                input.required = isCheque;
                                input.disabled = !isCheque;
                            });

                            if (onlineHelp) onlineHelp.hidden = isCheque;
                            if (chequeHelp) chequeHelp.hidden = !isCheque;
                        };

                        orderTypeInputs.forEach((input) => {
                            input.addEventListener('change', syncCheckoutOptions);
                        });

                        paymentInputs.forEach((input) => {
                            input.addEventListener('change', syncCheckoutOptions);
                        });

                        syncCheckoutOptions();
                    });
                </script>
            </form>
        </section>

        <aside class="checkout-card checkout-card--summary">
            <div class="section-head">
                <div>
                    <span class="eyebrow">YOUR BAG</span>
                    <h2>خلاصه سفارش</h2>
                </div>
            </div>

            <div class="checkout-items">
                @foreach($items as $item)
                    <div class="checkout-item">
                        <div class="checkout-item__media">
                            @if($item['image'])
                                <img
                                    src="{{ $item['image'] }}"
                                    alt="{{ $item['product']->name }}"
                                >
                            @else
                                <div class="product-image-placeholder"></div>
                            @endif
                        </div>

                        <div>
                            <strong>
                                {{ $item['product']->name }}
                            </strong>

                            <span>
                                {{ number_format($item['quantity']) }}
                                ×
                                {{ number_format($item['unit_price']) }}
                                تومان
                            </span>
                        </div>

                        <b>
                            {{ number_format($item['line_total']) }}
                        </b>
                    </div>
                @endforeach
            </div>

            <div class="checkout-total">
                <span>مبلغ سفارش</span>
                <strong>
                    {{ number_format($total) }} تومان
                </strong>
            </div>

            <a
                class="button button--ghost checkout-back"
                href="{{ route('cart') }}"
            >
                بازگشت به سبد
            </a>
        </aside>

    </div>
</section>
@endsection
