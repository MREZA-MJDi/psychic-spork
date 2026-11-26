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

    {{-- 04. Store signals --}}
    <x-store.home-signals
        :recent-products="$recentProducts"
        :popular-products="$popularProducts"
    />

    {{-- 05. Brand discovery --}}
    <x-store.brand-grid
        :brands="$brands"
    />
</div>
@endsection
