@php
    $latestImage = $latestProduct?->galleryMedia?->first()?->url;
    $category = $categories->first();
    $categoryImage = $category?->coverMedia?->url;

    $latestUrl = $latestProduct
        ? route('products.show', $latestProduct)
        : route('products.index');

    $categoryUrl = $category
        ? route('categories.show', $category)
        : route('categories.index');
@endphp

<section
    class="home-discovery-section"
    aria-labelledby="home-discovery-title"
>
    <div class="container">

        <header class="home-discovery__head">
            <div>
                <span class="eyebrow">JANAN / DISCOVER</span>

                <h2 id="home-discovery-title">
                    برای انتخاب بعدی، دو مسیر روشن.
                </h2>

                <p>
                    تازه‌ترین محصول یا یک کالکشن مشخص را انتخاب کن و مستقیم ادامه بده.
                </p>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                همه محصولات
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        <div class="home-discovery__grid">

            <a
                href="{{ $latestUrl }}"
                class="discovery-card discovery-card--featured"
            >
                <div class="discovery-card__media">
                    @if($latestImage)
                        <img
                            src="{{ $latestImage }}"
                            alt="{{ $latestProduct?->name ?? 'محصولات تازه جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="discovery-card__placeholder" aria-hidden="true">
                            <span>JANAN</span>
                        </div>
                    @endif
                </div>

                <span class="discovery-card__overlay" aria-hidden="true"></span>

                <div class="discovery-card__top">
                    <span>01</span>
                    <span>{{ $siteBrandNameLatin ?? 'JANAN' }} / NEW IN</span>
                </div>

                <div class="discovery-card__body">
                    <div>
                        <span class="discovery-card__kicker">
                            تازه‌های جانان
                        </span>

                        <h3>
                            {{ $latestProduct?->name ?? 'انتخاب‌های تازه' }}
                        </h3>

                        <p>
                            محصولات فعال و تازه‌ای که همین حالا آماده‌ی کشف‌اند.
                        </p>
                    </div>

                    <span class="discovery-card__action">
                        مشاهده محصول
                        <b aria-hidden="true">↗</b>
                    </span>
                </div>
            </a>

            <a
                href="{{ $categoryUrl }}"
                class="discovery-card discovery-card--category"
            >
                <div class="discovery-card__media">
                    @if($categoryImage)
                        <img
                            src="{{ $categoryImage }}"
                            alt="{{ $category?->name ?? 'کالکشن جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="discovery-card__placeholder" aria-hidden="true">
                            <span>{{ $category?->name ?? 'JANAN' }}</span>
                        </div>
                    @endif
                </div>

                <span class="discovery-card__overlay" aria-hidden="true"></span>

                <div class="discovery-card__top">
                    <span>02</span>
                    <span>CURATED / COLLECTION</span>
                </div>

                <div class="discovery-card__body">
                    <div>
                        <span class="discovery-card__kicker">
                            کالکشن منتخب
                        </span>

                        <h3>
                            {{ $category?->name ?? 'انتخاب‌های خاص' }}
                        </h3>

                        <p>
                            {{ $category?->description ?? 'مسیر مستقیم برای دیدن محصولات این کالکشن.' }}
                        </p>
                    </div>

                    <span class="discovery-card__action">
                        ورود به کالکشن
                        <b aria-hidden="true">↗</b>
                    </span>
                </div>
            </a>

        </div>

    </div>
</section>
