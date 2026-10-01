@extends('layouts.store')

@section('title', 'محصولات — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="catalog-page catalog-page--products">

    <section class="catalog-masthead catalog-masthead--products">
        <div class="container">
            <div class="catalog-masthead__top">
                <span class="eyebrow">JANAN / CATALOG / 01</span>

                <div class="catalog-masthead__stat">
                    <strong>{{ number_format($products->total()) }}</strong>
                    <span>محصول فعال</span>
                </div>
            </div>

            <div class="catalog-masthead__content">
                <div>
                    <h1>محصولی که می‌خواهی را پیدا کن.</h1>
                    <p>
                        دسته‌ها، برندها و جستجو را ترکیب کن تا سریع به انتخابت برسی.
                    </p>
                </div>

                <nav class="catalog-local-nav" aria-label="بخش‌های فروشگاه">
                    <a href="{{ route('products.index') }}" aria-current="page">محصولات</a>
                    <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
                    <a href="{{ route('brands.index') }}">برندها</a>
                </nav>
            </div>
        </div>
    </section>

    <section class="catalog-discovery">
        <div class="container">

            <div class="catalog-search-box">
                <div class="catalog-search-box__copy">
                    <span class="eyebrow">SEARCH</span>
                    <strong>هر چیزی را که در ذهن داری، بنویس.</strong>
                </div>

                <form
                    class="catalog-search"
                    data-store-search
                    data-suggestions-url="{{ route('search.suggestions') }}"
                    method="GET"
                    action="{{ route('products.index') }}"
                    role="search"
                >
                    <label class="catalog-search__field">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <circle cx="11" cy="11" r="6.5"/>
                            <path d="m16 16 4.5 4.5"/>
                        </svg>

                        <span class="sr-only">جستجوی محصولات</span>

                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="نام محصول، برند یا SKU…"
                            autocomplete="off"
                            data-search-input
                            minlength="2"
                        >

                        <button
                            type="button"
                            class="catalog-search__clear"
                            data-search-clear
                            aria-label="پاک کردن"
                            @if(!request('q')) hidden @endif
                        >
                            ×
                        </button>
                    </label>

                    <button class="button button--primary" type="submit">
                        جستجو
                        <span aria-hidden="true">↵</span>
                    </button>
                </form>
            </div>

            <form
                class="catalog-filter-bar"
                method="GET"
                action="{{ route('products.index') }}"
            >
                <input type="hidden" name="q" value="{{ request('q') }}">

                <div class="catalog-filter-bar__field">
                    <span>دسته‌بندی</span>
                    <select name="category">
                        <option value="">همه دسته‌ها</option>
                        @foreach($categories as $category)
                            <option
                                value="{{ $category->slug }}"
                                @selected(request('category') === $category->slug)
                            >
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="catalog-filter-bar__field">
                    <span>مرتب‌سازی</span>
                    <select name="sort" aria-label="مرتب‌سازی محصولات">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>جدیدترین</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>ارزان‌ترین</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>گران‌ترین</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>الفبایی</option>
                    </select>
                </div>

                <div class="catalog-filter-bar__field">
                    <span>برند</span>
                    <select name="brand">
                        <option value="">همه برندها</option>
                        @foreach($brands as $brand)
                            <option
                                value="{{ $brand->slug }}"
                                @selected(request('brand') === $brand->slug)
                            >
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="catalog-filter-bar__field">
                    <span>تعداد نمایش</span>
                    <select name="per_page" aria-label="تعداد نمایش در هر صفحه">
                        <option value="12" @selected((int) request('per_page', $perPage) === 12)>۱۲</option>
                        <option value="24" @selected((int) request('per_page', $perPage) === 24)>۲۴</option>
                        <option value="36" @selected((int) request('per_page', $perPage) === 36)>۳۶</option>
                    </select>
                </div>

                <div class="catalog-filter-bar__actions">
                    <button class="button button--primary" type="submit">
                        اعمال فیلتر
                    </button>

                    @if(request()->hasAny(['q', 'category', 'brand', 'sort', 'per_page']))
                        <a
                            class="button button--ghost"
                            href="{{ route('products.index') }}"
                        >
                            پاک کردن
                        </a>
                    @endif
                </div>

                <span class="catalog-filter-bar__count">
                    {{ number_format($products->total()) }} نتیجه
                </span>
            </form>

        </div>
    </section>

    <section class="catalog-results">
        <div class="container">

            @if($products->isNotEmpty())
                <header class="catalog-results__head">
                    <div>
                        <span class="eyebrow">CURATED CATALOG</span>
                        <h2>
                            {{ request('q') ? 'نتیجه جستجوی «'.request('q').'»' : 'محصولات فعال فروشگاه' }}
                        </h2>
                    </div>

                    <span class="catalog-results__page">
                        صفحه {{ $products->currentPage() }}
                        از {{ $products->lastPage() }}
                    </span>
                </header>

                <div class="product-grid">
                    @foreach($products as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>

                @if($products->hasPages())
                    <nav class="store-pagination" aria-label="صفحه‌بندی محصولات">
                        {{ $products->onEachSide(1)->links() }}
                    </nav>
                @endif

            @else
                <div class="empty-state empty-state--search catalog-empty">
                    <span class="eyebrow">NO RESULT</span>
                    <h2>چیزی با این مشخصات پیدا نشد.</h2>
                    <p>
                        عبارت کوتاه‌تری امتحان کن یا فیلترها را بردار تا گزینه‌های بیشتری ببینی.
                    </p>
                    <a class="button button--primary" href="{{ route('products.index') }}">
                        بازگشت به همه محصولات
                    </a>
                </div>
            @endif

        </div>
    </section>

</div>
@endsection
