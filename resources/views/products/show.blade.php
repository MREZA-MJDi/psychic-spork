@extends('layouts.store')

@section('content')
@php
    $gallery = $product->galleryMedia->take(8)->values();
    $variants = $product->activeVariants->values();

    $variant = $variants->first(
        fn ($item) => (int) $item->stock > 0
    ) ?? $variants->first();

    $image = $gallery->first()?->url;

    $attributes = collect($product->attributes ?? [])
        ->filter(fn ($value) => filled($value))
        ->values();

    $pageNumber = str_pad((string) ($gallery->count() ?: 1), 2, '0', STR_PAD_LEFT);
@endphp

<div class="product-experience">

    <section class="product-experience__top">
        <div class="container">

            <nav class="product-breadcrumbs" aria-label="مسیر محصول">
                <a href="{{ route('home') }}">خانه</a>
                <span aria-hidden="true">/</span>

                <a href="{{ route('products.index') }}">محصولات</a>

                @if($product->category)
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('categories.show', $product->category) }}">
                        {{ $product->category->name }}
                    </a>
                @endif

                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ $product->name }}</span>
            </nav>

            <div class="product-experience__stage">

                <div class="product-gallery" data-product-gallery>

                    <div class="product-gallery__main">
                        @if($image)
                            <img
                                src="{{ $image }}"
                                alt="{{ $product->name }}"
                                fetchpriority="high"
                                decoding="async"
                                data-gallery-main
                            >
                        @else
                            <div class="product-gallery__placeholder">
                                <span>JANAN</span>
                            </div>
                        @endif

                        <div class="product-gallery__counter">
                            <span data-gallery-current>01</span>
                            <i>/</i>
                            <span>{{ $pageNumber }}</span>
                        </div>

                        @if($variant?->is_on_sale)
                            @php
                                $discount = max(
                                    1,
                                    round(
                                        (1 - (
                                            $variant->effective_price /
                                            max(1, (float) $variant->price)
                                        )) * 100
                                    )
                                );
                            @endphp

                            <span class="product-gallery__sale">
                                {{ $discount }}٪-
                            </span>
                        @endif
                    </div>

                    @if($gallery->count() > 1)
                        <div
                            class="product-gallery__thumbs"
                            role="list"
                            aria-label="تصاویر محصول"
                        >
                            @foreach($gallery as $galleryItem)
                                <button
                                    type="button"
                                    class="product-gallery__thumb {{ $loop->first ? 'is-active' : '' }}"
                                    data-gallery-thumb
                                    data-gallery-src="{{ $galleryItem->url }}"
                                    data-gallery-alt="{{ $product->name }} — تصویر {{ $loop->iteration }}"
                                    data-gallery-index="{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}"
                                    aria-label="تصویر {{ $loop->iteration }} محصول"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                >
                                    <img
                                        src="{{ $galleryItem->url }}"
                                        alt=""
                                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                        decoding="async"
                                    >
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <aside
                    class="product-buy-card"
                    aria-labelledby="product-title"
                    data-product-purchase
                >
                    <div class="product-buy-card__topline">
                        <div class="product-buy-card__brand">
                            <span class="eyebrow">JANAN / PRODUCT</span>

                            @if($product->brand)
                                <a
                                    href="{{ route('brands.show', $product->brand) }}"
                                >
                                    {{ $product->brand->name }}
                                </a>
                            @endif
                        </div>

                        @if($variant?->sku)
                            <span class="product-buy-card__sku">
                                SKU / {{ $variant->sku }}
                            </span>
                        @endif
                    </div>

                    <div class="product-buy-card__heading">
                        <h1 id="product-title">{{ $product->name }}</h1>

                        <p>
                            {{ $product->short_description ?: 'جزئیات محصول را بررسی کن و انتخابت را برای خرید کامل کن.' }}
                        </p>
                    </div>

                    <div class="product-buy-card__price" aria-live="polite">
                        <strong data-product-price>
                            {{ number_format($variant?->effective_price ?? 0) }}
                        </strong>

                        <span>تومان</span>

                        <del
                            data-product-regular-price
                            @if(!$variant?->is_on_sale) hidden @endif
                        >
                            {{ number_format($variant?->price ?? 0) }}
                        </del>
                    </div>

                    <div
                        class="product-buy-card__stock {{ ($variant?->stock ?? 0) < 1 ? 'is-out' : (($variant?->is_low_stock ?? false) ? 'is-low' : '') }}"
                        data-product-stock
                    >
                        {{ ($variant?->stock ?? 0) < 1
                            ? 'فعلاً ناموجود'
                            : (($variant?->is_low_stock ?? false) ? 'موجودی محدود' : 'موجود و آماده سفارش')
                        }}
                    </div>

                    @if($variants->isNotEmpty())
                        <div class="product-variant-picker">
                            <div class="product-variant-picker__head">
                                <span>انتخاب مدل / Variant</span>
                                <small data-product-variant-label>
                                    {{ $variant?->display_name ?: 'انتخاب نشده' }}
                                </small>
                            </div>

                            <div class="product-variant-picker__grid">
                                @foreach($variants as $variantOption)
                                    @php
                                        $colorCode = (string) ($variantOption->color_code ?? '');
                                        $safeColor = preg_match('/^#[0-9A-Fa-f]{3,8}$/', $colorCode)
                                            ? $colorCode
                                            : null;
                                    @endphp

                                    <button
                                        type="button"
                                        class="product-variant-option {{ $variantOption->id === $variant?->id ? 'is-selected' : '' }} {{ $variantOption->stock < 1 ? 'is-out' : '' }}"
                                        data-product-variant
                                        data-variant-action="{{ route('cart.store', $variantOption) }}"
                                        data-variant-price="{{ $variantOption->effective_price }}"
                                        data-variant-regular="{{ $variantOption->price }}"
                                        data-variant-sale="{{ $variantOption->is_on_sale ? '1' : '0' }}"
                                        data-variant-stock="{{ $variantOption->stock }}"
                                        data-variant-low-stock="{{ $variantOption->is_low_stock ? '1' : '0' }}"
                                        data-variant-label="{{ $variantOption->display_name }}"
                                        data-variant-sku="{{ $variantOption->sku }}"
                                        data-variant-index="{{ $loop->iteration }}"
                                        aria-pressed="{{ $variantOption->id === $variant?->id ? 'true' : 'false' }}"
                                        @disabled($variantOption->stock < 1)
                                    >
                                        @if($safeColor)
                                            <i
                                                class="product-variant-option__swatch"
                                                style="--variant-color: {{ $safeColor }}"
                                                aria-hidden="true"
                                            ></i>
                                        @endif

                                        <span>
                                            {{ $variantOption->display_name }}
                                        </span>

                                        <small>
                                            {{ $variantOption->stock > 0 ? 'موجود' : 'ناموجود' }}
                                        </small>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($variant)
                        <form
                            method="POST"
                            action="{{ route('cart.store', $variant) }}"
                            class="product-purchase-form"
                            data-cart-add
                            data-product-name="{{ $product->name }}"
                            data-product-image="{{ $image ?? '' }}"
                        >
                            @csrf

                            <div class="product-purchase-form__row">
                                <div class="product-quantity-control">
                                    <span class="product-quantity-control__label">
                                        تعداد
                                    </span>

                                    <div class="product-quantity-control__control">
                                        <button
                                            type="button"
                                            data-product-quantity="decrease"
                                            aria-label="کاهش تعداد"
                                        >
                                            −
                                        </button>

                                        <input
                                            id="product-quantity"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="{{ max(1, $variant->stock) }}"
                                            value="1"
                                            inputmode="numeric"
                                            data-product-quantity-input
                                        >

                                        <button
                                            type="button"
                                            data-product-quantity="increase"
                                            aria-label="افزایش تعداد"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    class="button button--primary product-purchase-form__submit"
                                    data-product-add-button
                                    @disabled($variant->stock < 1)
                                >
                                    <span data-product-add-label>
                                        {{ $variant->stock > 0 ? 'افزودن به سبد خرید' : 'ناموجود' }}
                                    </span>

                                    <span aria-hidden="true">←</span>
                                </button>
                            </div>

                            <p class="product-purchase-form__hint">
                                پیش‌نمایش سبد بلافاصله کنار آیکن سبد نمایش داده می‌شود.
                            </p>
                        </form>
                    @else
                        <div class="product-unavailable">
                            <strong>این محصول فعلاً Variant فعالی ندارد.</strong>
                            <span>وقتی موجودی یا مدل فعال شود، خرید از همین صفحه در دسترس خواهد بود.</span>
                        </div>
                    @endif

                    <div class="product-buy-benefits" aria-label="مزایای خرید">
                        <div>
                            <span>01</span>
                            <strong>موجودی لحظه‌ای</strong>
                            <small>وضعیت خرید از همین صفحه خوانده می‌شود.</small>
                        </div>

                        <div>
                            <span>02</span>
                            <strong>سبد سریع</strong>
                            <small>بدون ترک کردن صفحه محصول.</small>
                        </div>

                        <div>
                            <span>03</span>
                            <strong>جزئیات واقعی</strong>
                            <small>قیمت و مشخصات از کاتالوگ فروشگاه.</small>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <section class="product-information">
        <div class="container">

            <div class="product-information__grid">

                <div class="product-information__intro">
                    <span class="eyebrow">JANAN / DETAILS</span>
                    <h2>همه‌چیز را قبل از خرید ببین.</h2>
                    <p>
                        مشخصات، توضیحات و اطلاعات سفارش در یک ساختار خوانا و جمع‌وجور قرار گرفته‌اند.
                    </p>
                </div>

                <div class="product-information__accordions">

                    @if($product->description)
                        <details open>
                            <summary>
                                <span>توضیحات محصول</span>
                                <b>+</b>
                            </summary>

                            <div class="product-information__content">
                                {!! nl2br(e($product->description)) !!}
                            </div>
                        </details>
                    @endif

                    <details {{ !$product->description ? 'open' : '' }}>
                        <summary>
                            <span>مشخصات فنی و محصول</span>
                            <b>+</b>
                        </summary>

                        <div class="product-information__content">
                            <dl class="product-spec-list">

                                @if($variant?->sku)
                                    <div>
                                        <dt>SKU</dt>
                                        <dd data-product-sku>{{ $variant->sku }}</dd>
                                    </div>
                                @endif

                                @if($variant?->size)
                                    <div>
                                        <dt>سایز</dt>
                                        <dd>{{ $variant->size }}</dd>
                                    </div>
                                @endif

                                @if($variant?->color)
                                    <div>
                                        <dt>رنگ</dt>
                                        <dd>{{ $variant->color }}</dd>
                                    </div>
                                @endif

                                @foreach($attributes as $attribute)
                                    <div>
                                        <dt>ویژگی</dt>
                                        <dd>
                                            {{ is_array($attribute) ? implode(' · ', $attribute) : $attribute }}
                                        </dd>
                                    </div>
                                @endforeach

                                @if(!$variant?->sku && !$variant?->size && !$variant?->color && $attributes->isEmpty())
                                    <div>
                                        <dt>وضعیت</dt>
                                        <dd>اطلاعات تکمیلی برای این محصول ثبت نشده است.</dd>
                                    </div>
                                @endif

                            </dl>
                        </div>
                    </details>

                    <details>
                        <summary>
                            <span>راهنمای سفارش</span>
                            <b>+</b>
                        </summary>

                        <div class="product-information__content">
                            <p>
                                Variant موردنظر را انتخاب کن، تعداد را مشخص کن و محصول را به سبد اضافه کن.
                                اگر موجودی یک مدل تمام شده باشد، همان مدل برای خرید غیرفعال می‌شود.
                            </p>
                        </div>
                    </details>

                </div>
            </div>
        </div>
    </section>

    @if($relatedProducts->isNotEmpty())
        <section class="section-block section-block--soft product-detail__related">
            <div class="container">

                <header class="section-head">
                    <div>
                        <span class="eyebrow">JANAN / RELATED</span>
                        <h2>انتخاب‌های نزدیک</h2>
                        <p>
                            چند محصول مشابه برای مقایسه و ادامه خرید.
                        </p>
                    </div>

                    <a
                        class="text-link"
                        href="{{ route('products.index') }}"
                    >
                        همه محصولات
                        <span aria-hidden="true">↗</span>
                    </a>
                </header>

                <div class="product-grid product-grid--related">
                    @foreach($relatedProducts as $relatedProduct)
                        <x-store.product-card :product="$relatedProduct" />
                    @endforeach
                </div>

            </div>
        </section>
    @endif

</div>
@endsection
