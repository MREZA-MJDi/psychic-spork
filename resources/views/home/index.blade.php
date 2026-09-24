@extends('layouts.store')

@section('content')

    @php
        $latestProductImage = $latestProduct?->galleryMedia->first()?->url;
    @endphp

    <div class="home-page">

        {{-- Hero --}}
        <x-store.hero :hero-slides="$heroSlides" />

<br >
        {{-- Store benefits --}}
        <section
            class="home-proof"
            aria-label="مزایای خرید از جانان"
        >
            <div class="container">
                <x-store.trust-strip />
            </div>
        </section>


        {{-- Collections --}}
        <section
            class="home-section home-section--collections"
            aria-labelledby="home-collections-title"
        >
            <x-store.category-grid
                :categories="$categories"
                title-id="home-collections-title"
            />
        </section>


        {{-- Editorial feature --}}
        @php
            $editorialProduct = $latestProduct ?? null;
            $editorialImage = $editorialProduct?->galleryMedia?->first()?->url;
        @endphp

        <section
            class="home-editorial-interlude"
            aria-labelledby="home-editorial-title"
        >
            <div class="home-editorial-interlude__frame">

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

                        @if($editorialImage)
                            <img
                                src="{{ $editorialImage }}"
                                alt="{{ $editorialProduct?->name ?? 'محصول منتخب جانان' }}"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <div
                                class="home-editorial-interlude__placeholder"
                                aria-hidden="true"
                            >
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
                        {{ $siteBrandNameLatin ?? 'JANAN' }} / EDIT
                    </span>

                            @if($editorialProduct?->name)
                                <strong>
                                    {{ $editorialProduct->name }}
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
                            {{ $homeTagline ?? 'انتخاب‌هایی دقیق، آرام و شخصی برای جهان جانان.' }}
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


                <span
                    class="home-editorial-interlude__orb home-editorial-interlude__orb--one"
                    aria-hidden="true"
                ></span>

                <span
                    class="home-editorial-interlude__orb home-editorial-interlude__orb--two"
                    aria-hidden="true"
                ></span>

            </div>
        </section>
        {{-- Products --}}
        <x-store.product-section
            :products="$products"
        />


        {{-- Editorial banner --}}
        <x-store.editorial-banner
            :product="$latestProduct"
        />


        {{-- Brands --}}
        <x-store.brand-grid
            :brands="$brands"
        />


        {{-- Final discovery banners --}}
        <x-store.mini-banners
            :latest-product="$latestProduct"
            :categories="$categories"
        />

    </div>
    <section
        class="home-statement"
        aria-labelledby="home-statement-title"
    >
        <div class="home-statement__frame">

            <div class="home-statement__rail">

                <div class="home-statement__rail-left">
                <span class="eyebrow">
                    THE JANAN EDIT
                </span>

                    <span class="home-statement__code">
                    01 / 05
                </span>
                </div>

                <span
                    class="home-statement__rail-line"
                    aria-hidden="true"
                ></span>

                <span
                    class="home-statement__year"
                    aria-hidden="true"
                >
                JANAN
            </span>

            </div>


            <div class="home-statement__content">

                <div class="home-statement__intro">
                <span class="home-statement__micro">
                    A QUIET POINT OF VIEW
                </span>

                    <span
                        class="home-statement__micro-line"
                        aria-hidden="true"
                    ></span>
                </div>


                <div class="home-statement__headline">

                    <h2 id="home-statement-title">
                        انتخابی آرام،
                        <br>
                        <em>جسور و شخصی.</em>
                    </h2>

                    <p>
                        {{ $homeTagline ?? 'انتخاب‌هایی دقیق، آرام و شخصی برای جهان جانان.' }}
                    </p>

                </div>


                <div class="home-statement__signature">

                <span class="home-statement__signature-mark">
                    J
                </span>

                    <span class="home-statement__signature-text">
                    NOTHING EXTRA.
                    <br>
                    ONLY WHAT MATTERS.
                </span>

                </div>

            </div>


            <span
                class="home-statement__orb home-statement__orb--sky"
                aria-hidden="true"
            ></span>

            <span
                class="home-statement__orb home-statement__orb--pink"
                aria-hidden="true"
            ></span>

        </div>
    </section>


@endsection
