@extends('layouts.store')

@section('title', $product->name . ' — ' . ($siteBrandNameLatin ?? 'Janan'))

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
                <span aria-current="page">{{ $product->name }}</span>
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
                                <span class="product-gallery-v2__kicker">
                                    JANAN / VISUAL
                                </span>

                                <span class="product-gallery-v2__index">
                                    <span data-gallery-current>01</span>
                                    <i aria-hidden="true">/</i>
                                    <span>{{ str_pad((string) $galleryCount, 2, '0', STR_PAD_LEFT) }}</span>
                                </span>
                            </div>

                            <figure class="product-gallery-v2__main">
                                @if($image)
                                    <img
                                        src="{{ $image }}"
                                        alt="{{ $product->name }}"
                                        fetchpriority="high"
                                        decoding="async"
                                        data-gallery-main
                                    >
                                @else
                                    <div class="product-gallery__placeholder" aria-hidden="true">
                                        <span>JANAN</span>
                                    </div>
                                @endif

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

                                <span>{{ number_format($gallery->count()) }} تصویر</span>
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
                                        <span class="eyebrow">VARIANTS / VISUAL</span>
                                        <strong>تصویر مدل‌ها</strong>
                                    </div>
                                    <span>با انتخاب هر مدل، تصویر اصلی هم هماهنگ می‌شود.</span>
                                </div>

                                <div class="product-variant-visuals__grid">
                                    @foreach($variants as $variantOption)
                                        @php
                                            $variantImage = $variantOption->galleryMedia->first()?->url;
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
                                            data-variant-wholesale="{{ $variantOption->wholesale_price !== null ? $variantOption->wholesale_price : '' }}"
                                            data-variant-regular="{{ $variantOption->price }}"
                                            data-variant-sale="{{ $variantOption->is_on_sale ? '1' : '0' }}"
                                            data-variant-stock="{{ $variantOption->stock }}"
                                            data-variant-low-stock="{{ $variantOption->is_low_stock ? '1' : '0' }}"
                                            data-variant-label="{{ $variantOption->display_name }}"
                                            data-variant-sku="{{ $variantOption->sku }}"
                                            data-variant-index="{{ $loop->iteration }}"
                                            data-variant-image="{{ $variantImage ?? '' }}"
                                            data-variant-image-alt="{{ $product->name }} — {{ $variantOption->display_name }}"
                                            aria-pressed="{{ $variantOption->id === $variant?->id ? 'true' : 'false' }}"
                                            @disabled($variantOption->stock < 1)
                                        >
                                            <span class="product-variant-visual__media">
                                                @if($variantImage)
                                                    <img
                                                        src="{{ $variantImage }}"
                                                        alt=""
                                                        loading="lazy"
                                                        decoding="async"
                                                    >
                                                @elseif($variantSafeColor)
                                                    <i
                                                        class="product-variant-visual__swatch"
                                                        style="--variant-color: {{ $variantSafeColor }}"
                                                        aria-hidden="true"
                                                    ></i>
                                                @else
                                                    <span class="product-variant-visual__fallback" aria-hidden="true">
                                                        {{ mb_substr($variantOption->display_name ?: 'V', 0, 1) }}
                                                    </span>
                                                @endif
                                            </span>

                                            <span class="product-variant-visual__copy">
                                                <strong>{{ $variantOption->display_name }}</strong>
                                                <small>
                                                    {{ $variantOption->stock > 0 ? 'موجود' : 'ناموجود' }}
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
                            <span class="eyebrow">JANAN / PRODUCT</span>

                            @if($product->brand)
                                <a href="{{ route('brands.show', $product->brand) }}">
                                    {{ $product->brand->name }}
                                </a>
                            @endif
                        </div>

                        @if($variant?->sku)
                            <span class="product-detail-v2__sku" data-product-sku>
                                SKU / {{ $variant->sku }}
                            </span>
                        @endif
                    </div>

                    <div class="product-detail-v2__title">
                        <h1 id="product-title">{{ $product->name }}</h1>

                        <p>
                            {{ $product->short_description ?: 'جزئیات محصول را بررسی کن و انتخابت را برای خرید کامل کن.' }}
                        </p>
                    </div>

                    <div class="product-detail-v2__purchase-block product-detail-v2__price-block">
                        <div class="product-detail-v2__section-label">
                            <span>قیمت و موجودی</span>
                            <small data-product-variant-label>
                                {{ $variant?->display_name ?: 'انتخاب نشده' }}
                            </small>
                        </div>

                        <div class="product-detail-v2__price-row">
                            <div class="product-buy-card__price product-buy-card__price--v2" aria-live="polite">
                                <strong data-product-price>
                                    {{ number_format($variant?->effective_price ?? 0) }}
                                </strong>
                                <span>تومان</span>

                                <span
                                    class="product-wholesale-price"
                                    data-product-wholesale-price
                                    @if($variant?->wholesale_price === null) hidden @endif
                                >
                                    @if($variant?->wholesale_price !== null)
                                        عمده: {{ number_format($variant->wholesale_price) }} تومان
                                    @endif
                                </span>

                                <del
                                    data-product-regular-price
                                    @if(!$variant?->is_on_sale) hidden @endif
                                >
                                    {{ number_format($variant?->price ?? 0) }}
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

                    @if($variants->isNotEmpty())
                        <div class="product-detail-v2__purchase-block product-detail-v2__variant-block">
                            <div class="product-variant-picker__head">
                                <div>
                                    <span>انتخاب مدل</span>
                                    <small>قیمت، موجودی و تصویر با انتخاب مدل تغییر می‌کند.</small>
                                </div>
                            </div>

                            <div class="product-variant-picker__grid">
                                @foreach($variants as $variantOption)
                                    @php
                                        $colorCode = (string) ($variantOption->color_code ?? '');
                                        $safeColor = preg_match('/^#[0-9A-Fa-f]{3,8}$/', $colorCode)
                                            ? $colorCode
                                            : null;
                                        $variantImage = $variantOption->galleryMedia->first()?->url;
                                    @endphp

                                    <button
                                        type="button"
                                        class="product-variant-option {{ $variantOption->id === $variant?->id ? 'is-selected' : '' }} {{ $variantOption->stock < 1 ? 'is-out' : '' }}"
                                        data-product-variant
                                        data-variant-action="{{ route('cart.store', $variantOption) }}"
                                        data-variant-price="{{ $variantOption->effective_price }}"
                                        data-variant-wholesale="{{ $variantOption->wholesale_price !== null ? $variantOption->wholesale_price : '' }}"
                                        data-variant-regular="{{ $variantOption->price }}"
                                        data-variant-sale="{{ $variantOption->is_on_sale ? '1' : '0' }}"
                                        data-variant-stock="{{ $variantOption->stock }}"
                                        data-variant-low-stock="{{ $variantOption->is_low_stock ? '1' : '0' }}"
                                        data-variant-label="{{ $variantOption->display_name }}"
                                        data-variant-sku="{{ $variantOption->sku }}"
                                        data-variant-index="{{ $loop->iteration }}"
                                        data-variant-image="{{ $variantImage ?? '' }}"
                                        data-variant-image-alt="{{ $product->name }} — {{ $variantOption->display_name }}"
                                        aria-pressed="{{ $variantOption->id === $variant?->id ? 'true' : 'false' }}"
                                        @disabled($variantOption->stock < 1)
                                    >
                                        @if($variantImage)
                                            <span class="product-variant-option__media">
                                                <img src="{{ $variantImage }}" alt="" loading="lazy" decoding="async">
                                            </span>
                                        @elseif($safeColor)
                                            <i
                                                class="product-variant-option__swatch"
                                                style="--variant-color: {{ $safeColor }}"
                                                aria-hidden="true"
                                            ></i>
                                        @endif

                                        <span>{{ $variantOption->display_name }}</span>
                                        <small>{{ $variantOption->stock > 0 ? 'موجود' : 'ناموجود' }}</small>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($variant)
                        <div class="product-detail-v2__purchase-block product-detail-v2__action-block">
                            <form
                                method="POST"
                                action="{{ route('cart.store', $variant) }}"
                                class="product-purchase-form product-purchase-form--v2"
                                data-cart-add
                                data-product-name="{{ $product->name }}"
                                data-product-image="{{ $variant?->galleryMedia->first()?->url ?: $image ?: '' }}"
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

                                <p class="product-purchase-form__hint">
                                    انتخابت را اضافه کن؛ سبد خرید بدون ترک کردن صفحه به‌روزرسانی می‌شود.
                                </p>
                            </form>
                        </div>
                    @else
                        <div class="product-unavailable">
                            <strong>این محصول فعلاً Variant فعالی ندارد.</strong>
                            <span>وقتی مدل فعال شود، خرید از همین صفحه در دسترس خواهد بود.</span>
                        </div>
                    @endif

                    <div class="product-buy-benefits product-buy-benefits--v2" aria-label="اطلاعات خرید">
                        <div>
                            <span>01</span>
                            <div>
                                <strong>موجودی واقعی</strong>
                                <small>وضعیت موجودی همین مدل نمایش داده می‌شود.</small>
                            </div>
                        </div>

                        <div>
                            <span>02</span>
                            <div>
                                <strong>انتخاب مدل</strong>
                                <small>قیمت و موجودی با تغییر مدل هماهنگ می‌شود.</small>
                            </div>
                        </div>

                        <div>
                            <span>03</span>
                            <div>
                                <strong>خرید سریع</strong>
                                <small>بعد از افزودن، سبد خرید از همان صفحه در دسترس است.</small>
                            </div>
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <div class="container customer-action-wrap customer-product-action">
        <div class="customer-action-strip">
            <div class="customer-action-strip__copy">
                <small>JANAN / PRODUCT PATH</small>
                <strong>مدل را انتخاب کن، قیمت و موجودی را ببین و همان‌جا به سبد اضافه کن.</strong>
            </div>
            <div class="customer-action-strip__actions">
                @if($product->category)
                    <a class="button button--ghost" href="{{ route('categories.show', $product->category) }}">این دسته</a>
                @endif
                @if($product->brand)
                    <a class="button button--primary" href="{{ route('brands.show', $product->brand) }}">این برند</a>
                @endif
            </div>
        </div>
    </div>

    <section class="product-information product-information--v2">
        <div class="container">
            <div class="product-information__grid">

                <div class="product-information__intro">
                    <span class="eyebrow">JANAN / DETAILS</span>

                    <h2>قبل از خرید، محصول را کامل بشناس.</h2>

                    <p>
                        اطلاعاتی که برای انتخاب لازم است، پایین همین صفحه در دسترس است.
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
                            <span>مشخصات محصول</span>
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
                                مدل موردنظر را انتخاب کن، تعداد را مشخص کن و محصول را به سبد اضافه کن.
                                اگر موجودی یک مدل تمام شده باشد، همان مدل برای خرید غیرفعال می‌شود.
                            </p>
                        </div>
                    </details>

                </div>
            </div>
        </div>
    </section>

    @if($relatedProducts->isNotEmpty())
        <section class="product-detail__related product-detail__related--v2">
            <div class="container">

                <header class="product-detail__related-head">
                    <div>
                        <span class="eyebrow">JANAN / RELATED</span>
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
