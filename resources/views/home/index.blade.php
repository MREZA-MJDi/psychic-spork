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
    <section
        class="home-section home-section--collections"
        aria-labelledby="home-collections-title"
    >
        <x-store.category-grid
            :categories="$categories"
            title-id="home-collections-title"
        />
    </section>

    {{-- 04. Two promotional cards between collections and products --}}
    <x-store.promo-stack
        :latest-product="$latestProduct"
        :category="$categories->first()"
        label="JANAN / DISCOVER"
    />

    {{-- 05. Products --}}
    <x-store.product-showcase
        :products="$products"
    />

    {{-- 06. Editorial --}}
    <x-store.editorial-banner
        :product="$latestProduct"
    />

    {{-- 07. Brands --}}
    <x-store.brand-grid
        :brands="$brands"
    />

    {{-- 08. Final discovery --}}
    <x-store.mini-banners
        :latest-product="$latestProduct"
        :categories="$categories"
    />

</div>
@endsection
