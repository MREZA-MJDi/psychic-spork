@extends('layouts.store')

@section('title', 'سبد خرید — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">JANAN / BAG</span><h1>سبد خرید</h1><p>سبد خرید فعلی شما.</p></div></section>

<section class="section-block">
<div class="container">
    <div class="customer-stepper" aria-label="مراحل خرید">
        <div class="customer-stepper__item" aria-current="step">
            <span class="customer-stepper__index">۰۱</span>
            <div><strong>سبد خرید</strong><span>مرور انتخاب‌ها</span></div>
        </div>
        <div class="customer-stepper__item">
            <span class="customer-stepper__index">۰۲</span>
            <div><strong>تکمیل سفارش</strong><span>اطلاعات ارسال</span></div>
        </div>
        <div class="customer-stepper__item">
            <span class="customer-stepper__index">۰۳</span>
            <div><strong>پرداخت</strong><span>تأیید نهایی</span></div>
        </div>
    </div>
@if($items->isEmpty() && $packLines->isEmpty())
    <div class="cart-empty reveal-up"><div class="cart-empty__ring">۰</div><span class="eyebrow">YOUR BAG / 00 ITEMS</span><h2>سبد خرید خالی است.</h2><a class="button button--primary" href="{{ route('products.index') }}">مشاهده کالکشن</a></div>
@else
<div class="cart-layout">
<div class="cart-items">
@if($packLines->isNotEmpty())
    <section class="cart-wholesale-packs">
        <div class="section-head">
            <div>
                <span class="eyebrow">WHOLESALE PACKS</span>
                <h2>پک‌های عمدهٔ انتخاب‌شده</h2>
            </div>
        </div>
        @foreach($packLines as $line)
            @php
                $pack = $line->pack;
                $firstVariant = $pack?->items->first()?->variant;
                $packImage = $pack?->image_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($pack->image_path)
                    : ($firstVariant?->primaryGalleryMedia?->url ?? $firstVariant?->product?->primaryGalleryMedia?->url);
            @endphp
            <article class="cart-item cart-item--wholesale-pack">
                <div class="cart-item__media">
                    <x-store.image :src="$packImage" :alt="$pack?->name ?? 'پک عمده'" fallback-class="product-image-placeholder" fallback="JANAN" />
                </div>
                <div class="cart-item__body">
                    <span class="eyebrow">پک آمادهٔ عمده</span>
                    <strong class="cart-item__title">{{ $pack?->name ?? 'پک حذف‌شده' }}</strong>
                    <small>{{ \App\Support\PersianNumber::format((int) ($pack?->pack_quantity ?? 0)) }} عدد در هر پک</small>
                    <div class="cart-item__meta">
                        <span>{{ \App\Support\PersianNumber::format((float) ($pack?->pack_price ?? 0)) }} تومان برای هر پک</span>
                        <form method="POST" action="{{ route('cart.wholesale-packs.update', $line) }}" class="cart-quantity-form">
                            @csrf @method('PUT')
                            <input type="number" name="quantity" min="1" max="99" value="{{ $line->quantity }}" inputmode="numeric" aria-label="تعداد پک {{ $pack?->name }}">
                            <button type="submit">به‌روزرسانی</button>
                        </form>
                    </div>
                    <strong class="cart-item__total">{{ \App\Support\PersianNumber::format((float) ($pack?->pack_price ?? 0) * $line->quantity) }} تومان</strong>
                    @if($pack?->items)
                        <small>{{ $pack->items->map(fn ($item) => ($item->variant?->product?->name ?? 'محصول') . ' × ' . $item->quantity)->implode('، ') }}</small>
                    @endif
                </div>
                <form method="POST" action="{{ route('cart.wholesale-packs.remove', $line) }}">
                    @csrf @method('DELETE')
                    <button class="cart-item__remove" type="submit">حذف</button>
                </form>
            </article>
        @endforeach
    </section>
@endif

@foreach($items as $item)
    <article class="cart-item reveal-up">
        <a href="{{ route('products.show',$item['product']) }}" class="cart-item__media">
            <x-store.image
                :src="$item['image']"
                :alt="$item['product']->name"
                fallback-class="product-image-placeholder"
                :fallback="$item['product']->name"
            />
        </a>
        <div class="cart-item__body">
            <span class="eyebrow">{{ $item['product']->category?->name ?: 'JANAN' }}</span>
            <a href="{{ route('products.show',$item['product']) }}" class="cart-item__title">{{ $item['product']->name }}</a>
            <small>{{ $item['variant']->sku }}</small>
            <div class="cart-item__meta">
                <span>{{ \App\Support\PersianNumber::format($item['unit_price']) }} تومان</span>
                <form method="POST" action="{{ route('cart.update',$item['item']) }}" class="cart-quantity-form">
                    @csrf @method('PUT')
                    <input type="number" name="quantity" min="0" max="{{ $item['variant']->stock }}" value="{{ $item['quantity'] }}" inputmode="numeric">
                    <button type="submit">به‌روزرسانی</button>
                </form>
            </div>
            <strong class="cart-item__total">{{ \App\Support\PersianNumber::format($item['line_total']) }} تومان</strong>
        </div>
        <form method="POST" action="{{ route('cart.remove',$item['item']) }}">@csrf @method('DELETE')<button class="cart-item__remove" type="submit">حذف</button></form>
    </article>
@endforeach
</div>
<aside class="cart-summary reveal-up">
<span class="eyebrow">ORDER SUMMARY</span><h2>جمع سبد</h2>
<div class="cart-summary__row"><span>تعداد اقلام</span><strong>{{ \App\Support\PersianNumber::format($items->sum('quantity') + $packLines->sum(fn ($line) => (int) ($line->pack?->pack_quantity ?? 0) * (int) $line->quantity)) }}</strong></div>
<div class="cart-summary__row"><span>مبلغ قابل پرداخت (با احتساب پک‌ها)</span><strong>{{ \App\Support\PersianNumber::format($total) }} تومان</strong></div>
<div class="cart-summary__actions">
    <a class="button button--ghost" href="{{ route('products.index') }}">ادامه خرید <span aria-hidden="true">↗</span></a>
    <a class="button button--primary" href="{{ route('checkout') }}">ثبت سفارش <span aria-hidden="true">←</span></a>
</div>
</aside>
</div>
@endif
</div>
</section>
@endsection
