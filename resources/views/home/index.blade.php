@extends('layouts.store')

@section('content')
<div class="home-page">
    <x-store.hero :hero-slides="$heroSlides" />

    <section class="home-signature">
        <div class="container home-signature__inner">
            <div class="home-signature__index">01 <span>/ 05</span></div>
            <div class="home-signature__copy">
                <span class="eyebrow">THE JANAN EDIT</span>
                <h1>انتخابی آرام،<br><em>جسور و شخصی.</em></h1>
                <p>{{ $homeTagline }}</p>
            </div>
            <div class="home-signature__mark" aria-hidden="true">J</div>
        </div>
    </section>

    <section class="home-proof"><div class="container"><x-store.trust-strip /></div></section>

    <div class="home-section home-section--collections"><x-store.category-grid :categories="$categories" /></div>

    <section class="section-block home-editorial-section">
        <div class="container">
            <div class="split-callout">
                <div class="split-callout__copy">
                    <span class="eyebrow">SPECIAL FOR YOU</span>
                    <h2>هر انتخاب، امضای شماست.</h2>
                    <p>{{ $homeTagline }}</p>
                    <div class="split-callout__actions">
                        <a href="{{ route('products.index') }}" class="button button--primary">مشاهده محصولات</a>
                        <a href="{{ route('categories.index') }}" class="button button--ghost">دسته‌بندی‌ها</a>
                    </div>
                </div>
                <div class="split-callout__visual">
                    @if($latestProduct?->galleryMedia->first()?->url)
                        <img src="{{ $latestProduct->galleryMedia->first()->url }}" alt="{{ $latestProduct->name }}" loading="lazy">
                        <span class="split-callout__stamp">JANAN / 01</span>
                    @else
                        <div class="visual-placeholder"><span>JANAN</span><strong>کالکشن تازه</strong></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <x-store.product-section :products="$products" />
    <x-store.editorial-banner :product="$latestProduct" />
    <x-store.brand-grid :brands="$brands" />
    <x-store.mini-banners :latest-product="$latestProduct" :categories="$categories" />
</div>
@endsection
