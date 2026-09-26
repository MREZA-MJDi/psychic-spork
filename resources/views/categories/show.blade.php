@extends('layouts.store')

@section('title', $category->name . ' — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium page-hero--collection">
    <div class="container page-hero__layout">
        <div>
            <a
                class="page-kicker"
                href="{{ route('categories.index') }}"
            >
                ↖ بازگشت به دسته‌بندی‌ها
            </a>

            <span class="eyebrow">
                COLLECTION / {{ strtoupper($category->slug) }}
            </span>

            <h1>{{ $category->name }}</h1>

            <p>
                {{ $category->description ?: 'منتخب محصولات این دسته‌بندی را ببینید.' }}
            </p>
        </div>

        @if($category->coverMedia?->url)
            <div class="page-hero__visual">
                <img
                    src="{{ $category->coverMedia->url }}"
                    alt="{{ $category->name }}"
                    loading="eager"
                >
            </div>
        @else
            <div class="page-hero__watermark" aria-hidden="true">
                {{ mb_substr($category->name, 0, 1) }}
            </div>
        @endif
    </div>
</section>

<section class="store-catalog-section">
    <div class="container">

        <div class="catalog-category-head">
            <div>
                <span class="eyebrow">CURATED PRODUCTS</span>
                <p>
                    {{ number_format($products->total()) }}
                    محصول در این کالکشن
                </p>
            </div>

            <a
                class="text-link"
                href="{{ route('products.index') }}"
            >
                همه محصولات
                <span>↗</span>
            </a>
        </div>

        @if($products->isNotEmpty())
            <div class="store-product-catalog">
                @foreach($products as $product)
                    <x-store.product-card
                        :product="$product"
                        variant="catalog"
                    />
                @endforeach
            </div>

            <div class="store-pagination">
                {{ $products->onEachSide(1)->links() }}
            </div>
        @else
            <div class="empty-state">
                <span class="eyebrow">EMPTY COLLECTION</span>
                <h2>محصول فعالی در این دسته نیست.</h2>
                <a
                    class="button button--primary"
                    href="{{ route('products.index') }}"
                >
                    همه محصولات
                </a>
            </div>
        @endif

    </div>
</section>
@endsection
