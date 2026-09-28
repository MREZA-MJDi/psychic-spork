@extends('layouts.store')

@section('content')
@php
    $variant = $product->variants->first();
    $image = $product->galleryMedia->first()?->url;
@endphp

<div class="product-detail-page">

    <section class="page-hero page-hero--premium page-hero--product">
        <div class="container page-hero__layout">
            <div>
                <a
                    class="page-kicker"
                    href="{{ route('products.index') }}"
                >
                    ↖ بازگشت به محصولات
                </a>

                <span class="eyebrow">
                    {{ $product->category?->name ?: 'JANAN' }}
                </span>

                <h1>{{ $product->name }}</h1>

                <p>
                    {{ $product->short_description ?: 'جزئیات محصول، قیمت و وضعیت موجودی را بررسی کن.' }}
                </p>
            </div>

            <div class="page-hero__stat">
                <b>{{ $variant && $variant->stock > 0 ? '01' : '—' }}</b>
                <span>{{ $variant && $variant->stock > 0 ? 'آماده خرید' : 'ناموجود' }}</span>
            </div>
        </div>
    </section>

    <section class="section-block">
        <div class="container">

            <div class="product-detail">

                <div class="product-detail__media">
                    @if($image)
                        <img
                            src="{{ $image }}"
                            alt="{{ $product->name }}"
                            fetchpriority="high"
                            decoding="async"
                        >
                    @else
                        <div class="product-image-placeholder">
                            <span>{{ $product->name }}</span>
                        </div>
                    @endif
                </div>

                <article class="product-detail__panel">

                    <div class="product-detail__meta">
                        @if($product->brand)
                            <a
                                class="eyebrow"
                                href="{{ route('brands.show', $product->brand) }}"
                            >
                                {{ $product->brand->name }}
                            </a>
                        @endif

                        @if($variant?->sku)
                            <span class="eyebrow">
                                SKU / {{ $variant->sku }}
                            </span>
                        @endif
                    </div>

                    <div class="product-detail__summary">
                        <h2>{{ $product->name }}</h2>

                        @if($product->description)
                            <p>
                                {{ $product->description }}
                            </p>
                        @endif
                    </div>

                    @if($variant)
                        <div class="detail-price">
                            <strong>
                                {{ number_format($variant->effective_price) }}
                            </strong>
                            <span>تومان</span>

                            @if($variant->is_on_sale)
                                <del>
                                    {{ number_format($variant->price) }}
                                </del>
                            @endif
                        </div>

                        <div class="detail-stock {{ $variant->stock < 1 ? 'is-out' : ($variant->is_low_stock ? 'is-low' : '') }}">
                            {{ $variant->stock < 1
                                ? 'ناموجود'
                                : ($variant->is_low_stock ? 'موجودی محدود' : 'موجود و آماده سفارش')
                            }}
                        </div>

                        <div class="product-detail__purchase">

                            @if($variant->stock > 0)
                                <form
                                    method="POST"
                                    action="{{ route('cart.store', $variant) }}"
                                    data-cart-add
                                >
                                    @csrf

                                    <div class="product-detail__quantity">
                                        <label for="product-quantity">
                                            تعداد
                                        </label>

                                        <input
                                            id="product-quantity"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="{{ $variant->stock }}"
                                            value="1"
                                            inputmode="numeric"
                                        >
                                    </div>

                                    <button
                                        type="submit"
                                        class="button button--primary detail-add-button"
                                    >
                                        افزودن به سبد خرید
                                    </button>
                                </form>

                                <span class="product-detail__note">
                                    بعد از افزودن، سبد خرید به‌صورت پنجره باز می‌شود و می‌توانی ادامه خرید یا پرداخت را انتخاب کنی.
                                </span>
                            @else
                                <button
                                    type="button"
                                    class="button button--ghost"
                                    disabled
                                >
                                    این محصول فعلاً ناموجود است
                                </button>
                            @endif

                        </div>
                    @endif

                    @if($variant?->size || $variant?->color || $product->attributes)
                        <div class="product-detail__facts">

                            @if($variant?->size)
                                <div>
                                    <span>سایز</span>
                                    <strong>{{ $variant->size }}</strong>
                                </div>
                            @endif

                            @if($variant?->color)
                                <div>
                                    <span>رنگ</span>
                                    <strong>{{ $variant->color }}</strong>
                                </div>
                            @endif

                            @if($product->attributes)
                                @foreach($product->attributes as $key => $values)
                                    <div>
                                        <span>{{ $key }}</span>

                                        <strong>
                                            {{ is_array($values) ? implode(' · ', $values) : $values }}
                                        </strong>
                                    </div>
                                @endforeach
                            @endif

                        </div>
                    @endif

                </article>

            </div>

        </div>
    </section>

    @if($relatedProducts->isNotEmpty())
        <section class="section-block section-block--soft product-detail__related">
            <div class="container">

                <header class="section-head">
                    <div>
                        <span class="eyebrow">JANAN / RELATED</span>
                        <h2>انتخاب‌های مشابه</h2>
                        <p>
                            محصولاتی از همین دسته یا انتخاب‌های نزدیک برای مقایسه بیشتر.
                        </p>
                    </div>

                    <a
                        class="text-link"
                        href="{{ route('products.index') }}"
                    >
                        مشاهده همه محصولات
                        <span aria-hidden="true">↗</span>
                    </a>
                </header>

                <div class="product-grid product-grid--related">
                    @foreach($relatedProducts as $relatedProduct)
                        <x-store.product-card
                            :product="$relatedProduct"
                        />
                    @endforeach
                </div>

            </div>
        </section>
    @endif

</div>
@endsection
