@extends('layouts.store')

@section('title', 'سفارش ثبت شد — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="section-block checkout-success-section">
    <div class="container">
        <div class="checkout-success">
            <span class="checkout-success__icon" aria-hidden="true">✓</span>
            <span class="eyebrow">JANAN / ORDER CONFIRMED</span>
            <h1>سفارش با موفقیت ثبت شد.</h1>
            <p>سفارش شما در سیستم جانان ثبت شده و موجودی اقلام آن همزمان از انبار کسر شده است.</p>

            <div class="checkout-success__meta">
                <span>شماره سفارش</span>
                <strong>{{ $orderNumber }}</strong>
            </div>
            <div class="checkout-success__meta">
                <span>مبلغ سفارش</span>
                <strong>{{ number_format($total) }} تومان</strong>
            </div>
            <div class="checkout-success__payment">
                پرداخت در محل · وضعیت پرداخت: در انتظار
            </div>

            <div class="checkout-success__actions">
                @auth
                    <a class="button button--primary" href="{{ route('account') }}">حساب کاربری</a>
                @endauth
                <a class="button button--ghost" href="{{ route('products.index') }}">ادامه خرید</a>
            </div>
        </div>
    </div>
</section>
@endsection
