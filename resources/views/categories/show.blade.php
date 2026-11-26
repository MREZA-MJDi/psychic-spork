@extends('layouts.store')

@section('title', $category->name . ' — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
@php
    $categoryName = \App\Support\PersianNumber::digits($category->name);
    $categoryImage = $category->coverMedia?->url
        ?: $products->getCollection()->map(fn ($item) => $item->primaryGalleryMedia?->url
            ?: $item->primaryActiveVariant?->primaryGalleryMedia?->url)->filter()->first();
@endphp
<div class="store-page category-collection">
    <div class="container category-collection__shell">
        <nav class="category-collection__crumbs" aria-label="مسیر صفحه">
            <a href="{{ route('home') }}">خانه</a><span aria-hidden="true">/</span>
            <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a><span aria-hidden="true">/</span>
            <span aria-current="page">{{ $categoryName }}</span>
        </nav>
        <section class="category-collection__hero" aria-labelledby="category-title">
            <div class="category-collection__intro">
                <span class="category-collection__kicker">کالکشن جانان</span>
                <h1 id="category-title">{{ $categoryName }}</h1>
                <p>{{ $category->description ?: 'مدل‌های این دسته را مرور کنید و مشخصات، قیمت و موجودی هر محصول را ببینید.' }}</p>
                <div class="category-collection__count">
                    <strong>{{ \App\Support\PersianNumber::format($products->total()) }}</strong>
                    <span>محصول فعال</span>
                </div>
                <div class="category-collection__actions">
                    <a class="button button--primary" href="#category-products">مشاهده محصولات <span aria-hidden="true">↓</span></a>
                    <a class="button button--ghost" href="{{ route('categories.index') }}">همه دسته‌ها</a>
                </div>
            </div>
            <div class="category-collection__visual">
                @if($categoryImage)
                    <x-store.image :src="$categoryImage" :alt="$categoryName" loading="eager" fetchpriority="high" :fallback="$categoryName" />
                @else
                    <span class="category-collection__placeholder" aria-hidden="true">{{ mb_substr($categoryName, 0, 1) }}</span>
                @endif
                <span class="category-collection__visual-label">{{ $categoryName }}</span>
            </div>
        </section>
        @if($children->isNotEmpty())
            <section class="category-collection__children" aria-labelledby="category-children-title">
                <h2 id="category-children-title">زیرمجموعه‌ها</h2>
                <div class="category-collection__child-grid">
                    @foreach($children as $child)
                        <a class="category-collection__child" href="{{ route('categories.show', $child->slug) }}">
                            <div class="category-collection__child-image">
                                <x-store.image :src="$child->coverMedia?->url" :alt="$child->name" :fallback="mb_substr($child->name, 0, 1)" />
                            </div>
                            <strong>{{ $child->name }}</strong><span aria-hidden="true">←</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
        <section id="category-products" class="category-collection__catalog" aria-labelledby="category-products-title">
            <header class="category-collection__catalog-head">
                <div>
                    <span class="category-collection__kicker">فروشگاه جانان</span>
                    <h2 id="category-products-title">محصولات {{ $categoryName }}</h2>
                    @if($products->isNotEmpty())
                        <p>نمایش {{ \App\Support\PersianNumber::format($products->firstItem()) }} تا {{ \App\Support\PersianNumber::format($products->lastItem()) }} از {{ \App\Support\PersianNumber::format($products->total()) }} محصول</p>
                    @endif
                </div>
                <a class="button button--ghost" href="{{ route('products.index', ['category' => $category->slug]) }}">فیلترهای بیشتر <span aria-hidden="true">←</span></a>
            </header>
            <div class="category-collection__toolbar">
                <x-store.catalog-sort :sort="$sort" :per-page="$perPage" />
            </div>
            @if($products->isNotEmpty())
                <div class="product-grid category-collection__products">
                    @foreach($products as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
                @if($products->hasPages())
                    <nav class="store-pagination" aria-label="صفحه‌بندی محصولات این دسته">
                        {{ $products->onEachSide(1)->links('vendor.pagination.store') }}
                    </nav>
                @endif
            @else
                <div class="category-collection__empty">
                    <h3>محصول فعالی در این صفحه نیست.</h3>
                    <p>می‌توانید سایر محصولات فروشگاه را مرور کنید.</p>
                    @if($products->total() > 0)
                        <a class="button button--primary" href="{{ route('categories.show', $category->slug) }}">بازگشت به صفحه اول</a>
                    @else
                        <a class="button button--primary" href="{{ route('products.index') }}">همه محصولات</a>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
