@extends('layouts.store')

@section('content')
<div class="home-page">

    {{-- 01. Hero --}}
    <x-store.hero :hero-slides="$heroSlides" />
{{-- 02. Collections --}}
    <x-store.category-grid
        :categories="$categories"
        title-id="home-collections-title"
    />

    {{-- 03. Featured product slider --}}
    <x-store.product-showcase
        :products="$products"
    />

    {{-- 04. Dynamic editorial product intelligence --}}
    <x-store.home-discovery :products="$products" />

    {{-- 05. Live store signals: new + popular --}}
    <x-store.home-signals
        :recent-products="$recentProducts"
        :popular-products="$popularProducts"
    />

    {{-- 06. Brands --}}
    <x-store.brand-grid
        :brands="$brands"
    />

</div>
@endsection
