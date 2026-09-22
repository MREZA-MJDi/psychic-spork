@extends('layouts.store')

@section('title', 'سبد خرید — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">JANAN / BAG</span><h1>سبد خرید</h1><p>سبد خرید فعلی شما.</p></div></section>

<section class="section-block">
<div class="container">
@if($items->isEmpty())
    <div class="cart-empty reveal-up"><div class="cart-empty__ring">۰</div><span class="eyebrow">YOUR BAG / 00 ITEMS</span><h2>سبد خرید خالی است.</h2><a class="button button--primary" href="{{ route('products.index') }}">مشاهده کالکشن</a></div>
@else
<div class="cart-layout">
<div class="cart-items">
@foreach($items as $item)
    <article class="cart-item reveal-up">
        <a href="{{ route('products.show',$item['product']) }}" class="cart-item__media">
            @if($item['image'])<img src="{{ $item['image'] }}" alt="{{ $item['product']->name }}" loading="lazy">@else<div class="product-image-placeholder"><span>{{ $item['product']->name }}</span></div>@endif
        </a>
        <div class="cart-item__body">
            <span class="eyebrow">{{ $item['product']->category?->name ?: 'JANAN' }}</span>
            <a href="{{ route('products.show',$item['product']) }}" class="cart-item__title">{{ $item['product']->name }}</a>
            <small>{{ $item['variant']->sku }}</small>
            <div class="cart-item__meta">
                <span>{{ number_format($item['unit_price']) }} تومان</span>
                <form method="POST" action="{{ route('cart.update',$item['item']) }}" class="cart-quantity-form">
                    @csrf @method('PUT')
                    <input type="number" name="quantity" min="0" max="{{ $item['variant']->stock }}" value="{{ $item['quantity'] }}">
                    <button type="submit">به‌روزرسانی</button>
                </form>
            </div>
            <strong class="cart-item__total">{{ number_format($item['line_total']) }} تومان</strong>
        </div>
        <form method="POST" action="{{ route('cart.remove',$item['item']) }}">@csrf @method('DELETE')<button class="cart-item__remove" type="submit">حذف</button></form>
    </article>
@endforeach
</div>
<aside class="cart-summary reveal-up">
<span class="eyebrow">ORDER SUMMARY</span><h2>جمع سبد</h2>
<div class="cart-summary__row"><span>تعداد اقلام</span><strong>{{ number_format($items->sum('quantity')) }}</strong></div>
<div class="cart-summary__row"><span>مبلغ قابل پرداخت</span><strong>{{ number_format($total) }} تومان</strong></div>
<div class="cart-summary__actions"><a class="button button--ghost" href="{{ route('products.index') }}">ادامه خرید</a><a class="button button--primary" href="{{ route('checkout') }}">ثبت سفارش</a></div>
</aside>
</div>
@endif
</div>
</section>
@endsection
