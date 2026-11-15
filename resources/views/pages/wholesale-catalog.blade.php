@extends('layouts.store')

@section('title', 'کاتالوگ خرید عمده — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="wholesale-page">
    <section class="section-block section-block--compact">
        <div class="container">
            <header class="wholesale-catalog__head">
                <div>
                    <span class="eyebrow">JANAN / WHOLESALE CATALOG</span>
                    <h1>کاتالوگ محصولات عمده</h1>
                    <p>محصول را جستجو کن؛ تعداد تنوع‌های دارای قیمت عمده و بازه قیمت هر محصول را ببین.</p>
                </div>
                <a class="button button--ghost" href="{{ route('wholesale.show') }}">بازگشت به پک‌های آماده</a>
            </header>

            <form method="GET" action="{{ route('wholesale.catalog') }}" class="customer-surface wholesale-catalog__filters">
                <label>
                    جستجوی محصول یا SKU
                    <input type="search" name="q" value="{{ request('q') }}" maxlength="120" placeholder="نام محصول، برند یا کد کالا…">
                </label>
                <label>
                    دسته‌بندی
                    <select name="category_id">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    برند
                    <select name="brand_id">
                        <option value="">همه برندها</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="wholesale-catalog__filter-actions">
                    <button class="button button--primary" type="submit">اعمال فیلتر</button>
                    <a class="button button--ghost" href="{{ route('wholesale.catalog') }}">پاک کردن</a>
                </div>
            </form>
        </div>
    </section>

    <section class="section-block wholesale-catalog">
        <div class="container">
            <div class="wholesale-products-head">
                <div>
                    <span class="eyebrow">WHOLESALE PRODUCTS</span>
                    <h2>محصولات دارای قیمت عمده</h2>
                </div>
                <span>{{ number_format($products->total()) }} محصول</span>
            </div>

            @if($products->count())
                <div class="wholesale-product-grid">
                    @foreach($products as $product)
                        <article class="wholesale-product-card">
                            <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__media">
                                <x-store.image
                                    :src="$product->primaryGalleryMedia?->url ?: $product->primaryActiveVariant?->primaryGalleryMedia?->url"
                                    :alt="$product->name"
                                    fallback-tag="span"
                                    fallback-class=""
                                    fallback="JANAN"
                                />
                            </a>
                            <div class="wholesale-product-card__body">
                                <div class="wholesale-product-card__meta">
                                    <span>{{ $product->brand?->name ?? 'Janan' }}</span>
                                    <span>{{ $product->category?->name ?? 'محصول' }}</span>
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__title">{{ $product->name }}</a>
                                <div class="wholesale-product-card__facts">
                                    <span>{{ number_format((int) $product->wholesale_variants_count) }} تنوع دارای قیمت عمده</span>
                                </div>
                                <div class="wholesale-variant-price">
                                    @if((float) $product->minimum_wholesale_price === (float) $product->maximum_wholesale_price)
                                        {{ number_format((float) $product->minimum_wholesale_price) }}
                                    @else
                                        {{ number_format((float) $product->minimum_wholesale_price) }}
                                        تا
                                        {{ number_format((float) $product->maximum_wholesale_price) }}
                                    @endif
                                    <small>تومان</small>
                                </div>
                                <a class="button button--ghost wholesale-product-card__action" href="{{ route('products.show', $product) }}">
                                    مشاهده رنگ و سایزها
                                    <span aria-hidden="true">↗</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                @if($products->hasPages())
                    <div class="store-pagination">{{ $products->links() }}</div>
                @endif
            @else
                <div class="customer-surface wholesale-empty">
                    <strong>محصولی با این شرایط پیدا نشد.</strong>
                    <span>عبارت جستجو یا فیلترها را تغییر بده.</span>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
