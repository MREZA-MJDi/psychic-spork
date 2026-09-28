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

    {{-- 04. Live store signals --}}
    <x-store.home-signals
        :recent-products="$recentProducts"
        :popular-products="$popularProducts"
    />

    {{-- 05. Brands --}}
    <x-store.brand-grid
        :brands="$brands"
    />

    {{-- 06. Closing CTA --}}
    <x-store.home-closing />

    {{-- 07. Recommendations: immediately before the storefront footer --}}
    <x-store.home-recommendations
        :products="$recommendedProducts"
    />

</div>
@endsection
