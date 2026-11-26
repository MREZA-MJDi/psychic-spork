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
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" placeholder="نام محصول، برند یا کد کالا…">
                </label>
                <label>
                    دسته‌بندی
                    <select name="category_id">
                        <option value="">همه دسته‌بندی‌ها</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>{{ \App\Support\PersianNumber::digits($category->name) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    برند
                    <select name="brand_id">
                        <option value="">همه برندها</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected((string) ($filters['brand_id'] ?? '') === (string) $brand->id)>{{ \App\Support\PersianNumber::digits($brand->name) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    مرتب‌سازی
                    <select name="sort">
                        <option value="featured" @selected($sort === 'featured')>پیشنهادی</option>
                        <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
                        <option value="price_asc" @selected($sort === 'price_asc')>کمترین قیمت عمده</option>
                        <option value="price_desc" @selected($sort === 'price_desc')>بیشترین قیمت عمده</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>الفبایی</option>
                    </select>
                </label>
                @if(isset($filters['per_page']))
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                @endif
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
                <span>{{ \App\Support\PersianNumber::format($products->total()) }} محصول</span>
            </div>

            @if($products->count())
                <div class="wholesale-product-grid">
                    @foreach($products as $product)
                        @include('pages.wholesale.product-card', ['product' => $product])
                    @endforeach
                </div>
                @if($products->hasPages())
                    <nav class="store-pagination" aria-label="صفحه‌بندی محصولات عمده">
                        {{ $products->onEachSide(1)->links('vendor.pagination.store') }}
                    </nav>
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
