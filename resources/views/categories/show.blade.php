@extends('layouts.store')

@section('title', $category->name . ' — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium page-hero--collection">
    <div class="container page-hero__layout">
        <div>
            <a class="page-kicker" href="{{ route('categories.index') }}">↖ بازگشت به دسته‌بندی‌ها</a>
            <span class="eyebrow">COLLECTION / {{ strtoupper($category->slug) }}</span>
            <h1>{{ $category->name }}</h1>
            <p>{{ $category->description ?: 'منتخب محصولات این دسته‌بندی را ببینید.' }}</p>
        </div>
        <div class="page-hero__watermark" aria-hidden="true">{{ mb_substr($category->name,0,1) }}</div>
    </div>
</section>
<section class="section-block catalog-stage"><div class="container">
<div class="catalog-toolbar">
    <div>
        <span class="eyebrow">CURATED PRODUCTS</span>
        <strong>{{ number_format($products->total()) }} محصول</strong>
    </div>

    <div class="catalog-toolbar__actions">
        <form class="catalog-inline-sort" method="GET">
            <label>
                <span>مرتب‌سازی</span>
                <select name="sort" onchange="this.form.submit()">
                    <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
                    <option value="oldest" @selected($sort === 'oldest')>قدیمی‌ترین</option>
                    <option value="price_asc" @selected($sort === 'price_asc')>ارزان‌ترین</option>
                    <option value="price_desc" @selected($sort === 'price_desc')>گران‌ترین</option>
                    <option value="name_asc" @selected($sort === 'name_asc')>نام: الف تا ی</option>
                    <option value="name_desc" @selected($sort === 'name_desc')>نام: ی تا الف</option>
                </select>
            </label>
            <label>
                <span>نمایش</span>
                <select name="per_page" onchange="this.form.submit()">
                    <option value="12" @selected($perPage === 12)>۱۲</option>
                    <option value="24" @selected($perPage === 24)>۲۴</option>
                    <option value="36" @selected($perPage === 36)>۳۶</option>
                </select>
            </label>
        </form>
        <a class="text-link" href="{{ route('products.index') }}">همه محصولات <span>↗</span></a>
    </div>
</div>
@if($products->isNotEmpty())
<div class="product-grid product-grid--editorial">@foreach($products as $product)<x-store.product-card :product="$product" />@endforeach</div>
<div class="store-pagination">{{ $products->links() }}</div>
@else
<div class="empty-state"><span class="eyebrow">EMPTY COLLECTION</span><h2>محصول فعالی در این دسته نیست.</h2><a class="button button--primary" href="{{ route('products.index') }}">همه محصولات</a></div>
@endif
</div></section>
@endsection
