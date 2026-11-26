@extends('layouts.store')

@section('title', $product->name . ' — ' . ($siteBrandNameFa ?? 'جانان'))

@section('content')
@php
    $gallery = $product->relationLoaded('galleryMedia')
        ? $product->getRelation('galleryMedia')->values()
        : collect();
    $variants = $product->activeVariants->values();

    $variant = $variants->first(
        fn ($item) => (int) $item->stock > 0
    ) ?? $variants->first();

    $image = $gallery->first()?->url ?: $variant?->primaryGalleryMedia?->url;
    $attributes = collect($product->attributes ?? [])
        ->filter(fn ($value) => filled($value))
        ->values();

    $galleryCount = max(1, $gallery->count());

    $discount = null;

    if ($variant?->is_on_sale) {
        $discount = max(
            1,
            round(
                (1 - (
                    $variant->effective_price /
                    max(1, (float) $variant->price)
                )) * 100
            )
        );
    }
@endphp

<div class="product-detail-v2">

    <section class="product-detail-v2__hero">
        <div class="container">

            <nav class="product-breadcrumbs product-detail-v2__breadcrumbs" aria-label="مسیر محصول">
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
                <span aria-current="page">{{ \App\Support\PersianNumber::digits($product->name) }}</span>
            </nav>

            <div class="product-detail-v2__layout">

                {{-- =====================================================
                     VISUAL STAGE
                ====================================================== --}}
                <section
                    class="product-detail-v2__visual product-detail-v2__visual-stage"
                    aria-label="گالری و تصاویر محصول"
                >
                    <div
                        class="product-gallery product-gallery--v2"
                        data-product-gallery
                    >
                        <div class="product-gallery-v2__stage">

                            <div class="product-gallery-v2__stage-meta">


                                <span class="product-gallery-v2__index">
                                    <span data-gallery-current>۰۱</span>
                                    <i aria-hidden="true">/</i>
                                    <span>{{ str_pad((string) $galleryCount, 2, '0', STR_PAD_LEFT) }}</span>
                                </span>
                            </div>

                            <figure class="product-gallery-v2__main">
                                <x-store.image
                                    :src="$image"
                                    :alt="$product->name"
                                    loading="eager"
                                    fetchpriority="high"
                                    fallback-class="product-gallery__placeholder"
                                    fallback="جانان"
                                    class="product-gallery-v2__main-image"
                                />

                                @if($discount)
                                    <span class="product-gallery-v2__sale">
                                        {{ $discount }}٪-
                                    </span>
                                @endif
                            </figure>

                        </div>

                        @if($gallery->count() > 1)
                            <div class="product-gallery-v2__thumbs-head">
                                <div>
                                    <strong>گالری محصول</strong>
                                    <span>برای مشاهده تصویر، روی thumbnail بزن.</span>
                                </div>

                                <span>{{ \App\Support\PersianNumber::format($gallery->count()) }} تصویر</span>
                            </div>

                            <div
                                class="product-gallery__thumbs product-gallery__thumbs--v2"
                                role="list"
                                aria-label="تمام تصاویر محصول"
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
                                        <span class="product-gallery__thumb-index">
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>

                                        <img
                                            src="{{ $galleryItem->url }}"
                                            alt=""
                                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                            data-store-image-fallback="جانان"
                                            data-store-image-fallback-class="product-gallery__thumb-fallback"
                                        >
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Variant-specific image strip. Each variant can have
                             one gallery asset through the existing media system. --}}
                        @if($variants->isNotEmpty())
                            <section class="product-variant-visuals" aria-label="تصاویر واریانت‌ها">
                                <div class="product-variant-visuals__head">
                                    <div>
                                        <strong>انتخاب مدل</strong>
                                    </div>
                                </div>

                                <div class="product-variant-visuals__grid">
                                    @foreach($variants as $variantOption)
                                        @php
                                            $variantMediaPath = $variantOption->primaryGalleryMedia?->path;
                                            $variantImage = filled($variantMediaPath)
                                                ? (preg_match('/^https?:\\/\\//i', $variantMediaPath)
                                                    ? $variantMediaPath
                                                    : route('store.media', ['path' => ltrim($variantMediaPath, '/')]))
                                                : null;
                                            $variantColorCode = (string) ($variantOption->color_code ?? '');
                                            $variantSafeColor = preg_match('/^#[0-9A-Fa-f]{3,8}$/', $variantColorCode)
                                                ? $variantColorCode
                                                : null;
                                        @endphp

                                        <button
                                            type="button"
                                            class="product-variant-visual {{ $variantOption->id === $variant?->id ? 'is-selected' : '' }} {{ $variantOption->stock < 1 ? 'is-out' : '' }}"
                                            data-product-variant
                                            data-variant-action="{{ route('cart.store', $variantOption) }}"
                                            data-variant-price="{{ $variantOption->effective_price }}"
                                            data-variant-regular="{{ $variantOption->price }}"
                                            data-variant-sale="{{ $variantOption->is_on_sale ? '1' : '0' }}"
                                            data-variant-stock="{{ $variantOption->stock }}"
                                            data-variant-low-stock="{{ $variantOption->is_low_stock ? '1' : '0' }}"
                                            data-variant-label="{{ $variantOption->customer_label }}"
                                            data-variant-index="{{ $loop->iteration }}"
                                            data-variant-image="{{ $variantImage ?? '' }}"
                                            data-variant-image-alt="{{ $product->name }} — {{ $variantOption->customer_label }}"
                                            aria-pressed="{{ $variantOption->id === $variant?->id ? 'true' : 'false' }}"
                                            @disabled($variantOption->stock < 1)
                                        >
                                            <span class="product-variant-visual__media">
                                                @if($variantImage)
                                                    <x-store.image
                                                        :src="$variantImage"
                                                        alt=""
                                                        fallback-tag="span"
                                                        fallback-class="product-variant-visual__fallback"
                                                        :fallback="mb_substr($variantOption->customer_label ?: 'م', 0, 1)"
                                                    />
                                                @elseif($variantSafeColor)
                                                    <i
                                                        class="product-variant-visual__swatch"
                                                        style="--variant-color: {{ $variantSafeColor }}"
                                                        aria-hidden="true"
                                                    ></i>
                                                @else
                                                    <span class="product-variant-visual__fallback" aria-hidden="true">
                                                        {{ mb_substr($variantOption->customer_label ?: 'م', 0, 1) }}
                                                    </span>
                                                @endif
                                            </span>

                                            <span class="product-variant-visual__copy">
                                                <strong>{{ $variantOption->customer_label }}</strong>
                                                <small>
                                                    {{ $variantOption->stock > 0 ? 'موجود' : 'ناموجود' }}
                                                    ·
                                                    {{ \App\Support\PersianNumber::format($variantOption->effective_price) }} تومان
                                                </small>
                                            </span>

                                            <span class="product-variant-visual__check" aria-hidden="true">
                                                ✓
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>
                </section>

                {{-- =====================================================
                     PURCHASE STAGE
                ====================================================== --}}
                <aside
                    class="product-detail-v2__purchase"
                    aria-labelledby="product-title"
                    data-product-purchase
                >
                    <div class="product-detail-v2__purchase-head">
                        <div class="product-detail-v2__identity">

                            @if($product->brand)
                                <a href="{{ route('brands.show', $product->brand) }}">
                                    {{ $product->brand->name }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="product-detail-v2__title">
                        <h1 id="product-title">{{ \App\Support\PersianNumber::digits($product->name) }}</h1>

                        @if(filled($product->short_description))
                            <p>{{ $product->short_description }}</p>
                        @endif
                    </div>

                    <div class="product-detail-v2__purchase-block product-detail-v2__price-block">
                        <div class="product-detail-v2__section-label">
                            <span>قیمت و موجودی</span>
                            <small>
                                مدل منتخب:
                                <b data-product-variant-label>{{ $variant?->customer_label ?: 'انتخاب نشده' }}</b>
                            </small>
                        </div>

                        <div class="product-detail-v2__price-row">
                            <div class="product-buy-card__price product-buy-card__price--v2" aria-live="polite">
                                <strong data-product-price>
                                    {{ \App\Support\PersianNumber::format($variant?->effective_price ?? 0) }}
                                </strong>
                                <span>تومان</span>


                                <del
                                    data-product-regular-price
                                    @if(!$variant?->is_on_sale) hidden @endif
                                >
                                    {{ \App\Support\PersianNumber::format($variant?->price ?? 0) }}
                                </del>
                            </div>

                            <div
                                class="product-buy-card__stock product-detail-v2__stock {{ ($variant?->stock ?? 0) < 1 ? 'is-out' : (($variant?->is_low_stock ?? false) ? 'is-low' : '') }}"
                                data-product-stock
                            >
                                <i aria-hidden="true"></i>
                                {{ ($variant?->stock ?? 0) < 1
                                    ? 'فعلاً ناموجود'
                                    : (($variant?->is_low_stock ?? false) ? 'موجودی محدود' : 'موجود و آماده سفارش')
                                }}
                            </div>
                        </div>
                    </div>

                    @if($variant)
                        <div class="product-detail-v2__purchase-block product-detail-v2__action-block">
                            <form
                                method="POST"
                                action="{{ route('cart.store', $variant) }}"
                                class="product-purchase-form product-purchase-form--v2"
                                data-cart-add
                                data-product-name="{{ $product->name }}"
                                data-product-image="{{ $variant?->primaryGalleryMedia?->url ?: $image ?: '' }}"
                            >
                                @csrf

                                <div class="product-purchase-form__row">
                                    <div class="product-quantity-control">
                                        <span class="product-quantity-control__label">تعداد</span>

                                        <div class="product-quantity-control__control">
                                            <button type="button" data-product-quantity="decrease" aria-label="کاهش تعداد">−</button>

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

                                            <button type="button" data-product-quantity="increase" aria-label="افزایش تعداد">+</button>
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

                            </form>
                        </div>
                    @else
                        <div class="product-unavailable">
                            <strong>این محصول فعلاً قابل سفارش نیست.</strong>
                            <span>وقتی مدل فعال شود، خرید از همین صفحه در دسترس خواهد بود.</span>
                        </div>
                    @endif

                </aside>

            </div>
        </div>
    </section>

    @if(filled($product->description) || $variant || $attributes->isNotEmpty())
        <section class="container product-information" aria-labelledby="product-information-title">
            <div class="product-information__accordions">
                <h2 id="product-information-title">دربارهٔ محصول</h2>

                @if(filled($product->description))
                    <details open>
                        <summary>توضیحات محصول <b aria-hidden="true">+</b></summary>
                        <div class="product-information__content">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </details>
                @endif

                @if($variant || $attributes->isNotEmpty())
                    <details @if(!filled($product->description)) open @endif>
                        <summary>مشخصات محصول <b aria-hidden="true">+</b></summary>
                        <div class="product-information__content">
                            <dl class="product-spec-list">
                                @if($variant)
                                    <div>
                                        <dt>مدل منتخب</dt>
                                        <dd data-product-variant-label>{{ $variant->customer_label }}</dd>
                                    </div>
                                @endif
                                @foreach($attributes as $attribute)
                                    <div>
                                        <dt>ویژگی</dt>
                                        <dd>{{ is_array($attribute) ? implode(' · ', $attribute) : $attribute }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </details>
                @endif
            </div>
        </section>
    @endif

    @if($relatedProducts->isNotEmpty())
        <section class="product-detail__related product-detail__related--v2">
            <div class="container">

                <header class="product-detail__related-head">
                    <div>

                        <h2>انتخاب‌های نزدیک</h2>
                        <p>چند محصول نزدیک برای ادامه‌ی کشف و خرید.</p>
                    </div>

                    <a class="text-link" href="{{ route('products.index') }}">
                        همه محصولات <span aria-hidden="true">↗</span>
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
