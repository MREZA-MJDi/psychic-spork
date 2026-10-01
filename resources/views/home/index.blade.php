@extends('layouts.store')

@section('content')
<div class="home-page">
    {{-- 01. Immersive hero --}}
    <x-store.hero :hero-slides="$heroSlides" />

    {{-- 02. Category discovery --}}
    <x-store.category-grid
        :categories="$categories"
        title-id="home-collections-title"
    />

    {{-- 03. Featured products --}}
    <x-store.product-showcase
        :products="$products"
    />

    {{-- 04. Editorial discovery --}}
    <x-store.home-discovery :products="$products" />

    {{-- 05. Store signals --}}
    <x-store.home-signals
        :recent-products="$recentProducts"
        :popular-products="$popularProducts"
    />

    {{-- 06. Brand discovery --}}
    <x-store.brand-grid
        :brands="$brands"
    />
</div>
@endsection
