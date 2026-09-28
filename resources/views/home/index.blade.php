@extends('layouts.store')

@section('content')
<div class="home-page">

    {{-- 01. Hero --}}
    <x-store.hero :hero-slides="$heroSlides" />

    {{-- 02. Trust / system proof --}}
    <section class="home-proof" aria-label="اعتماد و مزایای خرید از جانان">
        <div class="container">
            <x-store.trust-strip />
        </div>
    </section>

    {{-- 03. Collections --}}
    <x-store.category-grid
        :categories="$categories"
        title-id="home-collections-title"
    />

    {{-- 04. Featured product slider --}}
    <x-store.product-showcase
        :products="$products"
    />

    {{-- 05. Dynamic editorial product intelligence --}}
    <x-store.home-intelligence
        :products="$products"
    />

    {{-- 06. Live store signals: new + popular --}}
    <x-store.home-signals
        :recent-products="$recentProducts"
        :popular-products="$popularProducts"
    />

    {{-- 07. Brands --}}
    <x-store.brand-grid
        :brands="$brands"
    />

</div>
@endsection
