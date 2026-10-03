@extends('layouts.store')

@section('title', 'تکمیل سفارش — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
@php
    $user = auth()->user();
    $isCustomer = $user?->isCustomer() ?? false;
    $chequePermission = $user?->chequePermission;
    $chequeApproved = $chequeEnabled;
    $chequePending = $chequePermission?->isPending() ?? false;
@endphp

<section class="page-hero page-hero--motion page-hero--checkout">
    <div class="container">
        <span class="eyebrow">JANAN / CHECKOUT</span>
        <h1>پرداخت و ثبت سفارش</h1>
        <p>نوع خرید و روش پرداخت را انتخاب کن، اطلاعات ارسال را بررسی کن و سفارش را ثبت کن.</p>
    </div>
</section>

<section class="section-block checkout-section">
    <div class="container checkout-layout">

        <section class="checkout-card">
            <div class="section-head">
                <div>
                    <span class="eyebrow">ORDER & PAYMENT</span>
                    <h2>نوع خرید و پرداخت</h2>
                </div>
            </div>

            @if(session('success'))
                <div class="checkout-notice checkout-notice--success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="checkout-notice checkout-notice--error">
                    {{ session('error') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('checkout.store') }}"
                class="checkout-form"
                enctype="multipart/form-data"
            >
                @csrf

                <fieldset class="checkout-choice-group">
                    <legend>
                        <span class="eyebrow">ORDER TYPE</span>
                        <strong>نوع خرید</strong>
                    </legend>

                    <div class="checkout-choice-grid">
                        <label class="checkout-choice">
                            <input
                                type="radio"
                                name="order_type"
                                value="retail"
                                @checked(old('order_type', 'retail') === 'retail')
                            >
                            <span>
                                <strong>خرید عادی</strong>
                                <small>قیمت و شرایط فروش عادی جانان</small>
                            </span>
                        </label>

                        <label class="checkout-choice {{ ! $canSelectWholesale ? 'is-disabled' : '' }}">
                            <input
                                type="radio"
                                name="order_type"
                                value="wholesale"
                                @checked(old('order_type') === 'wholesale')
                                @disabled(! $canSelectWholesale)
                            >
                            <span>
                                <strong>خرید عمده</strong>
                                <small>
                                    بدون نیاز به تأیید حساب؛ فقط پرداخت چکی مجوز مدیریت می‌خواهد.
                                </small>
                            </span>
                        </label>
                    </div>

                    @guest
                        <p class="checkout-choice-note">
                            خرید عمده با پرداخت آنلاین به حساب کاربری نیاز ندارد؛ ورود و تأیید مدیر فقط برای پرداخت چکی لازم است.
                        </p>
                    @endguest
                </fieldset>

                <fieldset class="checkout-choice-group" id="payment-options">
                    <legend>
                        <span class="eyebrow">PAYMENT</span>
                        <strong>روش پرداخت</strong>
                    </legend>

                    <div class="checkout-choice-grid">
                        <label class="checkout-choice">
                            <input
                                type="radio"
                                name="payment_method"
                                value="online"
                                @checked(old('payment_method', 'online') === 'online')
                            >
                            <span>
                                <strong>پرداخت آنلاین</strong>
                                <small>پس از ثبت سفارش به درگاه امن پرداخت منتقل می‌شوی.</small>
                            </span>
                        </label>

                        @if($chequeApproved)
                            <label class="checkout-choice checkout-choice--cheque">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="cheque"
                                    @checked(old('payment_method') === 'cheque')
                                >
                                <span>
                                    <strong>پرداخت با چک</strong>
                                    <small>
                                        فقط برای سفارش عمده و تا سقف
                                        {{ $chequePermission->max_order_amount !== null ? number_format((float) $chequePermission->max_order_amount) . ' تومان' : 'بدون سقف تعیین‌شده' }}
                                    </small>
                                </span>
                            </label>
                        @else
                            <div class="checkout-payment-request">
                                <div>
                                    <span class="checkout-payment-request__badge">مجوز پرداخت چکی</span>
                                    @if($chequePending)
                                        <strong>درخواست چک در انتظار بررسی است.</strong>
                                        <p>بعد از تأیید مدیر، روش پرداخت چکی برای سفارش عمده فعال می‌شود.</p>
                                    @elseif($isCustomer)
                                        <strong>برای پرداخت چکی درخواست مجوز بده.</strong>
                                        <p>درخواست برای حساب مشتری ثبت می‌شود و مدیر سقف مجاز هر سفارش را تعیین می‌کند.</p>
                                    @elseif($user)
                                        <strong>این روش پرداخت برای حساب مدیریت در دسترس نیست.</strong>
                                        <p>پرداخت چکی فقط برای حساب مشتری و سفارش عمده فعال می‌شود.</p>
                                    @else
                                        <strong>پرداخت چکی نیاز به حساب مشتری و تأیید مدیر دارد.</strong>
                                        <p>می‌توانی بدون ورود، خرید عمده را آنلاین پرداخت کنی.</p>
                                    @endif
                                </div>

                                @if($isCustomer && ! $chequePending)
                                    <button
                                        type="submit"
                                        form="cheque-permission-request"
                                        class="button button--ghost"
                                    >
                                        درخواست مجوز چک
                                    </button>
                                @elseif(! $user)
                                    <div class="customer-action-strip__actions">
                                        <a class="button button--primary" href="{{ route('login', ['continue' => 'cheque']) }}">ورود</a>
                                        <a class="button button--ghost" href="{{ route('register', ['continue' => 'cheque']) }}">ساخت حساب</a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </fieldset>

                @if($chequeApproved)
                    <div
                        class="checkout-cheque-fields"
                        data-cheque-fields
                        hidden
                    >
                        <div class="section-head">
                            <div>
                                <span class="eyebrow">CHEQUE DETAILS</span>
                                <h3>اطلاعات چک</h3>
                            </div>
                        </div>

                        <div class="form-grid">
                            <label>
                                شناسه صیادی *
                                <input
                                    name="sayad_id"
                                    value="{{ old('sayad_id') }}"
                                    inputmode="numeric"
                                    maxlength="16"
                                >
                            </label>

                            <label>
                                شماره چک *
                                <input
                                    name="cheque_number"
                                    value="{{ old('cheque_number') }}"
                                >
                            </label>

                            <label>
                                بانک *
                                <input
                                    name="bank_name"
                                    value="{{ old('bank_name') }}"
                                >
                            </label>

                            <label>
                                صاحب حساب
                                <input
                                    name="account_holder"
                                    value="{{ old('account_holder') }}"
                                >
                            </label>

                            <label>
                                تاریخ سررسید *
                                <input
                                    type="date"
                                    name="due_date"
                                    value="{{ old('due_date') }}"
                                >
                            </label>

                            <label>
                                تصویر چک *
                                <input
                                    type="file"
                                    name="cheque_image"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                >
                            </label>
                        </div>
                    </div>
                @endif

                <div class="checkout-delivery-divider">
                    <div>
                        <span class="eyebrow">DELIVERY DETAILS</span>
                        <h2>اطلاعات گیرنده</h2>
                    </div>
                </div>

                <div class="form-grid">
                    <label>
                        نام و نام خانوادگی
                        <input
                            name="customer_name"
                            value="{{ old('customer_name', $user?->name) }}"
                            required
                        >
                    </label>

                    <label>
                        شماره موبایل
                        <input
                            name="customer_phone"
                            value="{{ old('customer_phone', $user?->phone) }}"
                            inputmode="tel"
                            required
                        >
                    </label>

                    <label>
                        ایمیل
                        <input
                            type="email"
                            name="customer_email"
                            value="{{ old('customer_email', $user?->email) }}"
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

                <button
                    class="button button--primary checkout-submit"
                    type="submit"
                >
                    ادامه و ثبت سفارش
                </button>
            </form>

            @if($isCustomer && ! $chequeApproved && ! $chequePending)
                <form
                    id="cheque-permission-request"
                    method="POST"
                action="{{ route('wholesale.cheque.request') }}"
                    hidden
                >
                    @csrf
                </form>
            @endif
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
                            <x-store.image
                                :src="$item['image']"
                                :alt="$item['product']->name"
                                fallback-class="product-image-placeholder"
                                fallback="JANAN"
                            />
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
                <span>مبلغ فعلی سبد</span>
                <strong>
                    {{ number_format($total) }} تومان
                </strong>
            </div>

            <p class="checkout-summary-note">
                در سفارش عمده، قیمت عمده هنگام ثبت نهایی سفارش محاسبه می‌شود.
            </p>

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
