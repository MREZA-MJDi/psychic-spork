@extends('layouts.store')

@section('title', 'وضعیت سفارش — ' . ($siteBrandNameLatin ?? 'Janan'))

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

                <h1>درخواست پرداخت چکی ثبت شد.</h1>

                <p>
                    اطلاعات چک شما ثبت شده و در انتظار بررسی مدیریت است.
                    تا زمان تأیید و تسویه چک، پرداخت قطعی محسوب نمی‌شود.
                </p>
            @elseif(($paymentStatus ?? 'pending') !== 'paid')
                <span class="eyebrow">
                    JANAN / PAYMENT PENDING
                </span>

                <h1>وضعیت پرداخت در انتظار تأیید است.</h1>

                <p>
                    سفارش شما ثبت شده است، اما درگاه هنوز نتیجه قطعی پرداخت را اعلام نکرده است.
                    موجودی سفارش تا روشن شدن وضعیت پرداخت حفظ شده و سفارش دوباره لغو نمی‌شود.
                </p>

                <p>
                    اگر مبلغ از حساب شما کسر شده، از ثبت دوباره سفارش خودداری کنید و شماره سفارش را برای پیگیری نگه دارید.
                    در صورت تأیید مجدد درگاه، وضعیت سفارش به‌روزرسانی خواهد شد.
                </p>
            @else
                <span class="eyebrow">
                    JANAN / PAYMENT CONFIRMED
                </span>

                <h1>پرداخت توسط درگاه تأیید شد.</h1>

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
                    {{ ($paymentStatus ?? 'pending') === 'paid' ? 'موفق' : 'در انتظار تأیید درگاه' }}
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
