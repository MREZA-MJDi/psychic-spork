@props([
    'products',
])

@php
    $products = collect($products)->take(12)->values();
@endphp

<section
    class="home-product-section"
    aria-labelledby="home-products-title"
>
    <div class="container">

        <header class="home-product-section__head">
            <div>
                <span class="eyebrow">JANAN / EDIT / 03</span>

                <h2 id="home-products-title">
                    محصولات منتخب
                </h2>

                <p>
                    چهار محصول هم‌زمان نمایش داده می‌شوند؛ برای دیدن انتخاب‌های بعدی حرکت کن یا وارد کالکشن کامل شو.
                </p>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                مشاهده همه محصولات
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        @if($products->isNotEmpty())

            <div
                class="home-product-carousel"
                data-product-carousel
                data-autoplay="3500"
                role="region"
                aria-roledescription="carousel"
                aria-label="محصولات منتخب جانان"
            >
                <div class="home-product-carousel__topline">
                    <span>
                        <b data-product-status aria-live="polite">01 / {{ str_pad((string) $products->count(), 2, '0', STR_PAD_LEFT) }}</b>
                        انتخاب
                    </span>

                    <div class="home-product-carousel__controls">
                        <button
                            type="button"
                            class="home-product-carousel__control"
                            data-product-prev
                            aria-label="محصول قبلی"
                        >
                            ←
                        </button>

                        <button
                            type="button"
                            class="home-product-carousel__control"
                            data-product-next
                            aria-label="محصول بعدی"
                        >
                            →
                        </button>
                    </div>
                </div>

                <div
                    class="home-product-carousel__viewport"
                    data-product-viewport
                    tabindex="0"
                >
                    <div
                        class="home-product-carousel__track"
                        data-product-track
                    >
                        @foreach($products as $product)
                            <div
                                class="home-product-slide"
                                role="group"
                                aria-label="محصول {{ $loop->iteration }} از {{ $products->count() }}"
                            >
                                <x-store.product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="home-product-section__footer">
                    <a
                        href="{{ route('products.index') }}"
                        class="button button--ghost"
                    >
                        دیدن بیشتر محصولات
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            </div>

        @else

            <div class="empty-state">
                <span class="eyebrow">JANAN COLLECTION</span>

                <h2>
                    هنوز محصول فعالی برای نمایش وجود ندارد.
                </h2>

                <a
                    href="{{ route('products.index') }}"
                    class="button button--primary"
                >
                    مشاهده محصولات
                </a>
            </div>

        @endif

    </div>
</section>
