@extends('layouts.store')

@section('title', 'محصولات — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="catalog-page catalog-page--products catalog-discovery-layout">
    <section class="catalog-masthead catalog-masthead--products">
        <div class="container">
            <div class="catalog-masthead__top">
                <span class="eyebrow">{{ $siteBrandNameLatin }} / CATALOG</span>
                <div class="catalog-masthead__stat">
                    <strong>{{ number_format($products->total()) }}</strong>
                    <span>محصول فعال</span>
                </div>
            </div>
            <div class="catalog-masthead__content">
                <div>
                    <h1>انتخابت را ساده‌تر پیدا کن.</h1>
                    <p>دسته‌بندی را انتخاب کن، برند را جستجو کن و محصولات را مقایسه کن.</p>
                </div>
                <nav class="catalog-local-nav" aria-label="بخش‌های فروشگاه">
                    <a href="{{ route('products.index') }}" aria-current="page">محصولات</a>
                    <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
                    <a href="{{ route('brands.index') }}">برندها</a>
                </nav>
            </div>
        </div>
    </section>

    <section class="catalog-discovery catalog-discovery--compact">
        <div class="container">
            <form class="catalog-search catalog-search--product" method="GET" action="{{ route('products.index') }}" role="search">
                <label class="catalog-search__field">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>
                    </svg>
                    <span class="sr-only">جستجوی محصولات</span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="نام محصول، مدل یا کد کالا…" autocomplete="off" minlength="2">
                </label>
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                @if(request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                <button class="button button--primary" type="submit">جستجوی محصول</button>
            </form>
        </div>
    </section>

    <section class="catalog-results catalog-results--discovery">
        <div class="container catalog-discovery-shell">
            <aside class="catalog-category-sidebar" aria-label="فیلتر دسته‌بندی محصولات">
                <div class="catalog-category-sidebar__head">
                    <span class="eyebrow">CATEGORIES</span>
                    <h2>دسته‌بندی</h2>
                </div>
                <nav class="catalog-category-list">
                    <a href="{{ route('products.index', request()->except(['category', 'page'])) }}"
                       class="{{ request()->filled('category') ? '' : 'is-active' }}">
                        <span>همه محصولات</span>
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                           class="{{ request('category') === $category->slug ? 'is-active' : '' }}"
                           @if(request('category') === $category->slug) aria-current="page" @endif>
                            <span>{{ $category->name }}</span>
                            <span class="catalog-category-list__arrow" aria-hidden="true">←</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="catalog-main-column">
                @if($featuredBanner)
                    <article class="catalog-feature-banner">
                        <a class="catalog-feature-banner__image" href="{{ $featuredBanner['url'] }}" aria-label="مشاهده {{ $featuredBanner['title'] }}">
                            <x-store.image :src="$featuredBanner['image']" :alt="$featuredBanner['title']" fallback-class="product-image-placeholder" fallback="JANAN" />
                        </a>
                        <div class="catalog-feature-banner__content">
                            <span class="eyebrow">منتخب جانان · {{ $featuredBanner['brand'] }}</span>
                            <h2>{{ $featuredBanner['title'] }}</h2>
                            <p>{{ $featuredBanner['description'] }}</p>
                            <a class="button button--dark" href="{{ $featuredBanner['url'] }}">مشاهده جزئیات محصول <span aria-hidden="true">↗</span></a>
                        </div>
                    </article>
                @endif

                <form class="catalog-brand-filter" method="GET" action="{{ route('products.index') }}">
                    <div class="catalog-brand-filter__top">
                        <div>
                            <span class="eyebrow">FIND A BRAND</span>
                            <h2>جستجو بر اساس برند</h2>
                        </div>
                        <label class="catalog-brand-filter__search">
                            <span class="sr-only">نام برند</span>
                            <input type="search" name="brand" value="{{ request('brand') }}" placeholder="نام برند را بنویس…" autocomplete="off">
                            <button type="submit" aria-label="جستجوی برند">⌕</button>
                        </label>
                    </div>
                    @foreach(request()->except(['brand', 'page']) as $key => $value)
                        @if(is_scalar($value) && $value !== '')
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <div class="catalog-brand-rail" aria-label="انتخاب سریع برند">
                        @foreach($brands as $brand)
                            <a href="{{ route('products.index', array_merge(request()->except('page'), ['brand' => $brand->slug])) }}"
                               class="catalog-brand-chip {{ request('brand') === $brand->slug ? 'is-active' : '' }}"
                               title="فیلتر محصولات {{ $brand->name }}">
                                <span class="catalog-brand-chip__shape" aria-hidden="true">{{ mb_substr($brand->name, 0, 1) }}</span>
                                <span>{{ $brand->name }}</span>
                            </a>
                        @endforeach
                    </div>
                    @if(request()->filled('brand'))
                        <a class="catalog-brand-filter__clear" href="{{ route('products.index', request()->except(['brand', 'page'])) }}">حذف فیلتر برند ×</a>
                    @endif
                </form>

                <header class="catalog-results__head catalog-results__head--compact">
                    <div class="catalog-results__title">
                        <span class="eyebrow">{{ $siteBrandNameLatin }} / CATALOG</span>
                        <h2>{{ request('q') ? 'نتیجه جستجوی «'.request('q').'»' : 'محصولات جانان' }}</h2>
                        <p>{{ number_format($products->total()) }} محصول مطابق انتخاب‌های شما</p>
                    </div>
                    <form class="catalog-inline-sort" method="GET" action="{{ route('products.index') }}">
                        @foreach(request()->except(['sort', 'page']) as $key => $value)
                            @if(is_scalar($value) && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label><span>مرتب‌سازی</span>
                            <select name="sort" onchange="this.form.submit()">
                                <option value="newest" @selected(request('sort', 'newest') === 'newest')>جدیدترین</option>
                                <option value="price_asc" @selected(request('sort') === 'price_asc')>ارزان‌ترین</option>
                                <option value="price_desc" @selected(request('sort') === 'price_desc')>گران‌ترین</option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>الفبایی</option>
                            </select>
                        </label>
                    </form>
                </header>

                @if($products->isNotEmpty())
                    <div class="product-grid product-grid--compact">
                        @foreach($products as $product)
                            <x-store.product-card :product="$product" variant="compact" />
                        @endforeach
                    </div>
                    @if($products->hasPages())
                        <nav class="store-pagination" aria-label="صفحه‌بندی محصولات">
                            {{ $products->onEachSide(1)->links('vendor.pagination.store') }}
                        </nav>
                    @endif
                @else
                    <div class="empty-state empty-state--search catalog-empty">
                        <span class="eyebrow">NO RESULT</span>
                        <h2>محصولی با این مشخصات پیدا نشد.</h2>
                        <p>عبارت جستجو یا فیلترها را تغییر بده.</p>
                        <a class="button button--primary" href="{{ route('products.index') }}">نمایش همه محصولات</a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
