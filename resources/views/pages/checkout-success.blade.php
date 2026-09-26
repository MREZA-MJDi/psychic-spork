@extends('layouts.store')

@section('title', 'پرداخت موفق — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="section-block checkout-success-section">
    <div class="container">
        <div class="checkout-success">
            <span
                class="checkout-success__icon"
                aria-hidden="true"
            >
                ✓
            </span>

            <span class="eyebrow">
                JANAN / PAYMENT CONFIRMED
            </span>

            <h1>
                پرداخت با موفقیت تأیید شد.
            </h1>

            <p>
                سفارش شما در جانان ثبت شده و پرداخت آن توسط درگاه تأیید شده است.
            </p>

            <div class="checkout-success__meta">
                <span>شماره سفارش</span>
                <strong>{{ $orderNumber }}</strong>
            </div>

            <div class="checkout-success__meta">
                <span>مبلغ پرداختی</span>
                <strong>
                    {{ number_format($total) }} تومان
                </strong>
            </div>

            <div class="checkout-success__payment">
                وضعیت پرداخت: موفق
            </div>

            <div class="checkout-success__actions">
                @auth
                    <a
                        class="button button--primary"
                        href="{{ route('account') }}"
                    >
                        حساب کاربری
                    </a>
                @endauth

                <a
                    class="button button--ghost"
                    href="{{ route('products.index') }}"
                >
                    ادامه خرید
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
