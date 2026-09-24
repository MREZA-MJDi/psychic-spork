@extends('layouts.store')

@section('content')

    @php
        $latestProductImage = $latestProduct?->galleryMedia->first()?->url;
    @endphp

    <div class="home-page">

        {{-- =====================================================
             01. HERO
        ====================================================== --}}

        <x-store.hero :hero-slides="$heroSlides" />


        {{-- =====================================================
             02. TRUST
        ====================================================== --}}

        <section
            class="home-proof"
            aria-label="مزایای خرید از جانان"
        >
            <div class="container">
                <x-store.trust-strip />
            </div>
        </section>


        {{-- =====================================================
             03. EDITORIAL STATEMENT
        ====================================================== --}}

        <section
            class="home-signature"
            aria-labelledby="home-signature-title"
        >
            <div class="container home-signature__inner">

                <div class="home-signature__meta">
                <span class="eyebrow">
                    THE JANAN EDIT
                </span>

                    <span class="home-signature__edition">
                    02 / 05
                </span>
                </div>


                <div class="home-signature__copy">

                    <h1 id="home-signature-title">
                        انتخابی آرام،
                        <em>جسور و شخصی.</em>
                    </h1>

                    <p>
                        {{ $homeTagline }}
                    </p>

                    <a
                        href="{{ route('products.index') }}"
                        class="home-signature__link"
                    >
                        کشف انتخاب‌های جانان
                        <span aria-hidden="true">↗</span>
                    </a>

                </div>


                <div
                    class="home-signature__mark"
                    aria-hidden="true"
                >
                    J
                </div>

            </div>
        </section>


        {{-- =====================================================
             04. COLLECTIONS
        ====================================================== --}}

        <section
            class="home-section home-section--collections"
            aria-labelledby="home-collections-title"
        >
            <x-store.category-grid
                :categories="$categories"
                title-id="home-collections-title"
            />
        </section>


        {{-- =====================================================
             05. EDITORIAL CALLOUT
        ====================================================== --}}

        <section
            class="home-editorial-interlude"
            aria-labelledby="home-editorial-title"
        >
            <div class="container">

                <div class="home-editorial-interlude__top">

            <span class="eyebrow">
                SPECIAL FOR YOU
            </span>

                    <span
                        class="home-editorial-interlude__index"
                        aria-hidden="true"
                    >
                04 / 05
            </span>

                </div>


                <div class="home-editorial-interlude__grid">

                    {{-- IMAGE --}}
                    <div class="home-editorial-interlude__visual">

                        @if($latestProductImage)

                            <img
                                src="{{ $latestProductImage }}"
                                alt="{{ $latestProduct?->name ?? 'محصول منتخب جانان' }}"
                                loading="lazy"
                                decoding="async"
                            >

                        @else

                            <div class="home-editorial-interlude__placeholder">
                                <span>JANAN</span>
                                <small>THE LATEST EDIT</small>
                            </div>

                        @endif

                        <span
                            class="home-editorial-interlude__image-number"
                            aria-hidden="true"
                        >
                    01
                </span>

                        <div class="home-editorial-interlude__caption">

                    <span>
                        {{ $siteBrandNameLatin ?? 'JANAN' }}
                        / EDIT
                    </span>

                            @if($latestProduct?->name)
                                <strong>
                                    {{ $latestProduct->name }}
                                </strong>
                            @endif

                        </div>

                    </div>


                    {{-- COPY --}}
                    <div class="home-editorial-interlude__copy">

                        <div class="home-editorial-interlude__copy-top">

                    <span class="home-editorial-interlude__micro">
                        A PERSONAL SELECTION
                    </span>

                            <span
                                class="home-editorial-interlude__line"
                                aria-hidden="true"
                            ></span>

                        </div>

                        <h2 id="home-editorial-title">
                            هر انتخاب،
                            <br>
                            <em>امضای شماست.</em>
                        </h2>

                        <p>
                            {{ $homeTagline }}
                        </p>

                        <a
                            href="{{ route('products.index') }}"
                            class="home-editorial-interlude__link"
                        >
                    <span>
                        مشاهده انتخاب‌های جانان
                    </span>

                            <b aria-hidden="true">
                                ↗
                            </b>
                        </a>


                        <div class="home-editorial-interlude__bottom">

                    <span>
                        CURATED WITH INTENTION
                    </span>

                            <span>
                        JANAN / 04
                    </span>

                        </div>

                    </div>

                </div>

            </div>
        </section>

        {{-- =====================================================
             06. PRODUCTS
        ====================================================== --}}

        <x-store.product-section
            :products="$products"
        />


        {{-- =====================================================
             07. EDITORIAL BANNER
        ====================================================== --}}

        <x-store.editorial-banner
            :product="$latestProduct"
        />


        {{-- =====================================================
             08. BRANDS
        ====================================================== --}}

        <x-store.brand-grid
            :brands="$brands"
            title-id="home-brands-title"
        />


        {{-- =====================================================
             09. MINI BANNERS
        ====================================================== --}}

        <x-store.mini-banners
            :latest-product="$latestProduct"
            :categories="$categories"
        />

    </div>

@endsection
