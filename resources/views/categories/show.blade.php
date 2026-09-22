@extends('layouts.store')

@section('title', $category->name . ' — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">CATEGORY</span>
        <h1>{{ $category->name }}</h1>
        <p>{{ $category->description ?: 'محصولات این دسته‌بندی را ببینید.' }}</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        @if($products->isNotEmpty())
            <div class="product-grid">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
            <div class="store-pagination">{{ $products->links() }}</div>
        @else
            <div class="empty-state">
                <h2>محصول فعالی در این دسته نیست.</h2>
                <a class="button button--primary" href="{{ route('products.index') }}">همه محصولات</a>
            </div>
        @endif
    </div>
</section>
@endsection
