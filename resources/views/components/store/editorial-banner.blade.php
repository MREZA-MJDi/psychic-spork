@php
    $editorialMedia = $product?->galleryMedia?->first();
    $editorialImage = $editorialMedia?->url;
    $editorialAlt = $product?->name ?: 'کالکشن جانان';
    $editorialVariant = $product?->variants?->first();
    $editorialCategory = $product?->category?->name ?? 'Janan Collection';
    $editorialBrand = $product?->brand?->name ?? ($siteBrandNameLatin ?? 'Janan');
    $editorialPrice = $editorialVariant?->effective_price;
    $editorialStock = (int) ($editorialVariant?->stock ?? 0);
    $editorialSale = $editorialVariant?->is_on_sale ?? false;
    $editorialDiscount = $editorialSale
        ? max(
            1,
            round(
                (1 - (
                    $editorialVariant->effective_price /
                    max(1, (float) $editorialVariant->price)
                )) * 100
            )
        )
        : 0;
@endphp

<section
    class="editorial-tech-section"
    aria-labelledby="editorial-tech-title"
>
    <div class="container">
        <article class="editorial-tech">

            <div class="editorial-tech__visual">
                <div class="editorial-tech__media">
                    @if($editorialImage)
                        <img
                            src="{{ $editorialImage }}"
                            alt="{{ $editorialAlt }}"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="editorial-tech__placeholder" aria-hidden="true">
                            <span>JANAN</span>
                            <strong>THE ART OF DETAILS</strong>
                        </div>
                    @endif
                </div>

                <div class="editorial-tech__veil" aria-hidden="true"></div>

                <div class="editorial-tech__topline" aria-hidden="true">
                    <span>JANAN / EDIT</span>
                    <span>01 — 04</span>
                </div>

                <div class="editorial-tech__floating-card">
                    <span class="editorial-tech__floating-label">CURRENT EDIT</span>

                    <div class="editorial-tech__floating-main">
                        <strong>{{ $editorialBrand }}</strong>
                        @if($editorialPrice)
                            <span>{{ number_format($editorialPrice) }} تومان</span>
                        @endif
                    </div>

                    <div class="editorial-tech__floating-meta">
                        <span>{{ $editorialCategory }}</span>
                        <span class="{{ $editorialStock > 0 ? 'is-available' : 'is-out' }}">
                            {{ $editorialStock > 0 ? 'AVAILABLE' : 'SOLD OUT' }}
                        </span>
                    </div>
                </div>

                @if($editorialSale)
                    <span class="editorial-tech__discount">
                        -{{ $editorialDiscount }}%
                    </span>
                @endif

                <span class="editorial-tech__vertical" aria-hidden="true">
                    DETAILS / TEXTURE / FORM
                </span>
            </div>

            <div class="editorial-tech__content">
                <div class="editorial-tech__content-top">
                    <span class="eyebrow">
                        {{ $siteBrandNameLatin ?? 'Janan' }} / THE ART OF DETAILS
                    </span>

                    <span class="editorial-tech__counter" aria-hidden="true">
                        04 <i></i> 08
                    </span>
                </div>

                <div class="editorial-tech__copy">
                    <span class="editorial-tech__kicker">
                        A MORE PERSONAL EDIT
                    </span>

                    <h2 id="editorial-tech-title">
                        جزئیاتی که
                        <em>دیده می‌شوند.</em>
                    </h2>

                    <p>
                        انتخاب‌های جانان فقط محصول نیستند؛ ترکیبی از فرم، بافت و جزئیاتی هستند که تجربه‌ی نهایی را کامل می‌کنند.
                    </p>
                </div>

                <div class="editorial-tech__footer">
                    <div class="editorial-tech__metrics">
                        <div>
                            <strong>01</strong>
                            <span>CURATED</span>
                        </div>

                        <div>
                            <strong>{{ $editorialCategory }}</strong>
                            <span>COLLECTION</span>
                        </div>

                        <div>
                            <strong>{{ $editorialStock > 0 ? 'LIVE' : 'OFF' }}</strong>
                            <span>STATUS</span>
                        </div>
                    </div>

                    <a
                        href="{{ $product ? route('products.show', $product) : route('products.index') }}"
                        class="editorial-tech__cta"
                    >
                        <span>مشاهده انتخاب</span>
                        <i aria-hidden="true">↗</i>
                    </a>
                </div>
            </div>

            <span class="editorial-tech__orb editorial-tech__orb--one" aria-hidden="true"></span>
            <span class="editorial-tech__orb editorial-tech__orb--two" aria-hidden="true"></span>
        </article>
    </div>
</section>
