@props([
    'latestProduct' => null,
    'category' => null,
    'label' => 'JANAN / DISCOVER',
])

@php
    $latestImage = $latestProduct?->galleryMedia?->first()?->url;
    $categoryImage = $category?->coverMedia?->url;
    $productsUrl = route('products.index');
@endphp

<section
    class="store-promo-section"
    aria-label="دسترسی سریع به محصولات"
>
    <div class="container">

        <div class="store-promo-tiles">

            <a
                href="{{ $productsUrl }}"
                class="store-promo-tile is-large"
                aria-label="مشاهده همه محصولات"
            >
                @if($latestImage)
                    <img
                        src="{{ $latestImage }}"
                        alt=""
                        loading="lazy"
                        decoding="async"
                    >
                @else
                    <span class="store-promo-tile__placeholder" aria-hidden="true">
                        JANAN
                    </span>
                @endif

                <span class="store-promo-tile__shade" aria-hidden="true"></span>
                <span class="store-promo-tile__index" aria-hidden="true">01</span>
                <span class="store-promo-tile__arrow" aria-hidden="true">↗</span>
            </a>

            <a
                href="{{ $productsUrl }}"
                class="store-promo-tile"
                aria-label="مشاهده همه محصولات"
            >
                @if($categoryImage)
                    <img
                        src="{{ $categoryImage }}"
                        alt=""
                        loading="lazy"
                        decoding="async"
                    >
                @else
                    <span class="store-promo-tile__placeholder" aria-hidden="true">
                        JANAN
                    </span>
                @endif

                <span class="store-promo-tile__shade" aria-hidden="true"></span>
                <span class="store-promo-tile__index" aria-hidden="true">02</span>
                <span class="store-promo-tile__arrow" aria-hidden="true">↗</span>
            </a>

        </div>

    </div>
</section>
