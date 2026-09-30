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

            @if(($paymentMethod ?? 'online') === 'cheque')
                <span class="eyebrow">
                    JANAN / CHEQUE SUBMITTED
                </span>

                <h1>
                    درخواست پرداخت چکی ثبت شد.
                </h1>

                <p>
                    اطلاعات چک شما ثبت شده و در انتظار بررسی مدیریت است.
                    تا زمان تأیید و تسویه چک، پرداخت قطعی محسوب نمی‌شود.
                </p>
            @else
                <span class="eyebrow">
                    JANAN / PAYMENT CONFIRMED
                </span>

                <h1>
                    پرداخت توسط درگاه تأیید شد.
                </h1>

                @if(($orderStatus ?? 'confirmed') === 'confirmed')
                    <p>
                        سفارش شما در جانان ثبت شده و پرداخت آن با موفقیت نهایی شده است.
                    </p>
                @else
                    <p>
                        پرداخت بانکی تأیید شده و اطلاعات سفارش شما ثبت شده است.
                        وضعیت نهایی‌سازی سفارش در سیستم در حال تکمیل است.
                    </p>
                @endif
            @endif

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
                وضعیت پرداخت:
                @if(($paymentMethod ?? 'online') === 'cheque')
                    در انتظار بررسی چک
                @else
                    {{ ($paymentStatus ?? 'paid') === 'paid' ? 'موفق' : 'در حال نهایی‌سازی' }}
                @endif
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
