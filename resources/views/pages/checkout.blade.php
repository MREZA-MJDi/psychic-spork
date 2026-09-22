@extends('layouts.store')

@section('title', 'تکمیل سفارش — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">JANAN / CHECKOUT</span><h1>تکمیل سفارش</h1></div></section>

<section class="section-block">
<div class="container checkout-layout">
<section class="checkout-card">
<div class="section-head"><div><span class="eyebrow">DELIVERY DETAILS</span><h2>اطلاعات گیرنده</h2></div></div>
<form method="POST" action="{{ route('checkout.store') }}" class="checkout-form">
@csrf
<div class="form-grid">
<label>نام و نام خانوادگی<input name="customer_name" value="{{ old('customer_name',auth()->user()?->name) }}" required></label>
<label>شماره موبایل<input name="customer_phone" value="{{ old('customer_phone',auth()->user()?->phone) }}" inputmode="tel" required></label>
<label>ایمیل<input type="email" name="customer_email" value="{{ old('customer_email',auth()->user()?->email) }}"></label>
<label>استان<input name="shipping_province" value="{{ old('shipping_province') }}"></label>
<label>شهر<input name="shipping_city" value="{{ old('shipping_city') }}"></label>
<label class="form-grid__full">آدرس کامل<textarea name="shipping_address" rows="5" required>{{ old('shipping_address') }}</textarea></label>
<label>کد پستی<input name="postal_code" value="{{ old('postal_code') }}"></label>
<label>یادداشت سفارش<input name="customer_note" value="{{ old('customer_note') }}"></label>
</div>
<div class="checkout-payment"><div><span class="eyebrow">PAYMENT</span><strong>پرداخت در محل</strong><p>وضعیت پرداخت سفارش تا اتصال درگاه «در انتظار» است.</p></div><span class="checkout-payment__badge">COD</span></div>
<button class="button button--primary checkout-submit" type="submit">ثبت نهایی سفارش</button>
</form>
</section>

<aside class="checkout-card checkout-card--summary">
<div class="section-head"><div><span class="eyebrow">YOUR BAG</span><h2>خلاصه سفارش</h2></div></div>
<div class="checkout-items">
@foreach($items as $item)
<div class="checkout-item">
<div class="checkout-item__media">@if($item['image'])<img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}">@else<div class="product-image-placeholder"></div>@endif</div>
<div><strong>{{ $item['product']->name }}</strong><span>{{ number_format($item['quantity']) }} × {{ number_format($item['unit_price']) }} تومان</span></div>
<b>{{ number_format($item['line_total']) }}</b>
</div>
@endforeach
</div>
<div class="checkout-total"><span>مبلغ سفارش</span><strong>{{ number_format($total) }} تومان</strong></div>
<a class="button button--ghost checkout-back" href="{{ route('cart') }}">بازگشت به سبد</a>
</aside>
</div>
</section>
@endsection
