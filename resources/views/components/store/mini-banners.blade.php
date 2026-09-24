@php
    $latestImage = $latestProduct?->galleryMedia?->first()?->url;

    $category = $categories->first();
    $categoryImage = $category?->coverMedia?->url;

    $latestUrl = route('products.index');

    $categoryUrl = $category
        ? route('categories.show', $category)
        : route('categories.index');
@endphp

<section class="section-block section-block--compact home-mini-banners">
    <div class="container">

        <div class="mini-banner-grid">

            {{-- New arrivals --}}
            <a
                href="{{ $latestUrl }}"
                class="mini-banner mini-banner--dark"
            >
                <div class="mini-banner__media">

                    @if($latestImage)
                        <img
                            src="{{ $latestImage }}"
                            alt="{{ $latestProduct?->name ?? 'محصولات تازه جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="mini-banner__placeholder">
                            <span>JANAN</span>
                        </div>
                    @endif

                </div>

                <span
                    class="mini-banner__veil"
                    aria-hidden="true"
                ></span>

                <div class="mini-banner__content">

                    <span class="mini-banner__eyebrow">
                        {{ $siteBrandNameLatin ?? 'Janan' }} / NEW IN
                    </span>

                    <h3>
                        تازه‌های جانان
                    </h3>

                    <span class="mini-banner__link">
                        مشاهده محصولات
                        <b aria-hidden="true">←</b>
                    </span>

                </div>

                <span
                    class="mini-banner__number"
                    aria-hidden="true"
                >
                    01
                </span>
            </a>


            {{-- Curated category --}}
            <a
                href="{{ $categoryUrl }}"
                class="mini-banner mini-banner--soft"
            >
                <div class="mini-banner__media">

                    @if($categoryImage)
                        <img
                            src="{{ $categoryImage }}"
                            alt="{{ $category?->name ?? 'انتخاب‌های خاص جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="mini-banner__placeholder">
                            <span>
                                {{ $category?->name ?? 'JANAN' }}
                            </span>
                        </div>
                    @endif

                </div>

                <span
                    class="mini-banner__veil"
                    aria-hidden="true"
                ></span>

                <div class="mini-banner__content">

                    <span class="mini-banner__eyebrow">
                        EDITED FOR YOU
                    </span>

                    <h3>
                        {{ $category?->name ?? 'انتخاب‌های خاص' }}
                    </h3>

                    <span class="mini-banner__link">
                        دیدن انتخاب‌ها
                        <b aria-hidden="true">←</b>
                    </span>

                </div>

                <span
                    class="mini-banner__number"
                    aria-hidden="true"
                >
                    02
                </span>
            </a>

        </div>

    </div>
</section>
