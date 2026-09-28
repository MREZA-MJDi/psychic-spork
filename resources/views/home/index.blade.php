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

    {{-- 03. Selected products --}}
    <x-store.product-showcase :products="$products" />
</div>
@endsection
