@extends('layouts.store')

@section('content')
<div class="catalog-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / CATALOG</span>
                <h1>محصولات جانان</h1>
                <p>
                    بین محصولات واقعی فروشگاه جستجو کن، فیلترها را ترکیب کن و سریع به انتخابت برس.
                </p>
            </div>

            <div class="page-hero__stat">
                <b>{{ number_format($products->total()) }}</b>
                <span>محصول فعال</span>
            </div>
        </div>
    </section>

    <section class="section-block catalog-page__content">
        <div class="container">

            <div class="catalog-command">
                <div class="catalog-command__intro">
                    <span class="eyebrow">FIND YOUR PRODUCT</span>
                    <h2>دقیق‌تر جستجو کن.</h2>
                    <p>
                        جستجو روی نام محصول، SKU، برند، دسته و توضیحات انجام می‌شود.
                    </p>
                </div>

                <form
                    class="catalog-search"
                    data-store-search
                    method="GET"
                    action="{{ route('products.index') }}"
                    role="search"
                >
                    <div class="catalog-search__field">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <circle cx="11" cy="11" r="6.5"/>
                            <path d="m16 16 4.5 4.5"/>
                        </svg>

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
                    </div>

                    <button class="button button--primary" type="submit">
                        جستجو
                        <span aria-hidden="true">↵</span>
                    </button>
                </form>
            </div>

            <div class="store-filter-bar">
                <form
                    class="store-filter-form"
                    method="GET"
                    action="{{ route('products.index') }}"
                >
                    <input
                        type="hidden"
                        name="q"
                        value="{{ request('q') }}"
                    >

                    <label>
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
                    </label>

                    <label>
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
                    </label>

                    <button class="button button--ghost" type="submit">
                        اعمال فیلتر
                    </button>

                    @if(request()->hasAny(['q', 'category', 'brand']))
                        <a
                            class="button button--ghost"
                            href="{{ route('products.index') }}"
                        >
                            پاک کردن
                        </a>
                    @endif
                </form>

                <span class="store-result-count">
                    {{ number_format($products->total()) }} نتیجه
                </span>
            </div>

            @if($products->isNotEmpty())

                <div class="catalog-toolbar catalog-toolbar--modern">
                    <div>
                        <span class="eyebrow">CURATED CATALOG</span>
                        <strong>
                            صفحه {{ $products->currentPage() }}
                            از {{ $products->lastPage() }}
                        </strong>
                    </div>

                    <span class="catalog-toolbar__hint">
                        {{ request('q') ? 'نتیجه جستجوی «' . request('q') . '»' : 'محصولات فعال فروشگاه' }}
                    </span>
                </div>

                <div class="product-grid">
                    @foreach($products as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>

                @if($products->hasPages())
                    <nav
                        class="store-pagination"
                        aria-label="صفحه‌بندی محصولات"
                    >
                        {{ $products->onEachSide(1)->links() }}
                    </nav>
                @endif

            @else

                <div class="empty-state empty-state--search">
                    <span class="eyebrow">NO RESULT</span>

                    <h2>چیزی با این مشخصات پیدا نشد.</h2>

                    <p>
                        عبارت کوتاه‌تری امتحان کن یا فیلترها را بردار تا گزینه‌های بیشتری ببینی.
                    </p>

                    <div class="empty-state__actions">
                        <a
                            class="button button--primary"
                            href="{{ route('products.index') }}"
                        >
                            بازگشت به همه محصولات
                        </a>

                        @if(request('q'))
                            <a
                                class="button button--ghost"
                                href="{{ route('products.index', ['q' => request('q')]) }}"
                            >
                                فقط همین عبارت
                            </a>
                        @endif
                    </div>
                </div>

            @endif

        </div>
    </section>

</div>
@endsection
