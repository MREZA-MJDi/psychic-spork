@extends('layouts.store')

@section('content')
<div class="home-page">

    {{-- 01. Hero --}}
    <x-store.hero :hero-slides="$heroSlides" />

    {{-- 02. Store benefits --}}
    <section class="home-proof" aria-label="مزایای خرید از جانان">
        <div class="container">
            <x-store.trust-strip />
        </div>
    </section>

    {{-- 03. Collections --}}
    <x-store.category-grid
        :categories="$categories"
        title-id="home-collections-title"
    />

    {{-- 04. Featured products --}}
    <x-store.product-showcase
        :products="$products"
    />

    {{-- 05. Live store signals --}}
    <x-store.home-signals
        :recent-products="$recentProducts"
        :popular-products="$popularProducts"
    />

    {{-- 06. Brands --}}
    <x-store.brand-grid
        :brands="$brands"
    />

    {{-- 07. Closing CTA --}}
    <x-store.home-closing />

    {{-- 08. Recommendations: immediately before the storefront footer --}}
    <x-store.home-recommendations
        :products="$recommendedProducts"
    />

</div>
@endsection
