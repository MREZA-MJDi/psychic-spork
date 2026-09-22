@extends('layouts.store')

@section('content')
<div class="home-page">
    <x-store.hero :hero-slides="$heroSlides" />

    <section class="section-block section-block--compact section-block--no-top">
        <div class="container"><x-store.trust-strip /></div>
    </section>

    <div class="home-section home-section--soft">
        <x-store.category-grid :categories="$categories" />
    </div>

    <section class="section-block section-block--soft">
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
