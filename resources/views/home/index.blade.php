@extends('layouts.store')

@section('content')
<div class="home-page">

    {{-- 01. Hero --}}
    <x-store.hero :hero-slides="$heroSlides" />

    {{-- 02. Trust / benefits --}}
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

    {{-- 04. DigiKala-style discovery rail --}}
    <x-store.promo-stack
        :latest-product="$latestProduct"
        :category="$categories->first()"
        label="JANAN / DISCOVERY 01"
    />

    {{-- 05. Product showcase --}}
    <x-store.product-showcase :products="$products" />

    {{-- 06. Editorial --}}
    <x-store.editorial-banner :product="$latestProduct" />

    {{-- 07. Discovery rail again --}}
    <x-store.promo-stack
        :latest-product="$latestProduct"
        :category="$categories->last()"
        label="JANAN / DISCOVERY 02"
    />

    {{-- 08. Brands --}}
    <x-store.brand-grid :brands="$brands" />

</div>
@endsection
