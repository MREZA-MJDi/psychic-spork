@props([
    'latestProduct' => null,
    'category' => null,
    'label' => 'JANAN / DISCOVER',
])

@php
    $latestImage = $latestProduct?->galleryMedia?->first()?->url;
    $categoryImage = $category?->coverMedia?->url;
    $latestUrl = $latestProduct
        ? route('products.show', $latestProduct)
        : route('products.index');
    $categoryUrl = $category
        ? route('categories.show', $category)
        : route('categories.index');
@endphp

<section class="store-promo-section" aria-label="پیشنهادهای جانان">
    <div class="container">

        <header class="store-promo-section__head">
            <div>
                <span class="eyebrow">{{ $label }}</span>
                <h2>یک انتخاب دیگر برای تو.</h2>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                مشاهده همه
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        <div class="store-promo-stack">

            <a href="{{ $latestUrl }}" class="store-promo">
                <span class="store-promo__media">
                    @if($latestImage)
                        <img
                            src="{{ $latestImage }}"
                            alt="{{ $latestProduct?->name ?? 'محصول منتخب جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <span class="store-promo__placeholder">JANAN</span>
                    @endif
                </span>

                <span class="store-promo__veil" aria-hidden="true"></span>

                <span class="store-promo__content">
                    <small>01 / NEW EDIT</small>
                    <strong>{{ $latestProduct?->name ?? 'کالکشن تازه جانان' }}</strong>
                    <span>مشاهده انتخاب ↗</span>
                </span>
            </a>

            <a href="{{ $categoryUrl }}" class="store-promo">
                <span class="store-promo__media">
                    @if($categoryImage)
                        <img
                            src="{{ $categoryImage }}"
                            alt="{{ $category?->name ?? 'دسته‌بندی جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <span class="store-promo__placeholder">
                            {{ $category?->name ?? 'JANAN' }}
                        </span>
                    @endif
                </span>

                <span class="store-promo__veil" aria-hidden="true"></span>

                <span class="store-promo__content">
                    <small>02 / COLLECTION</small>
                    <strong>{{ $category?->name ?? 'کالکشن‌های جانان' }}</strong>
                    <span>ورود به دسته ↗</span>
                </span>
            </a>

        </div>
    </div>
</section>
