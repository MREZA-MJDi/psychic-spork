@extends('layouts.store')

@section('content')
<div class="catalog-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / CATALOG</span>
                <h1>محصولات</h1>
                <p>
                    مجموعه کامل محصولات فعال جانان با جستجو، فیلتر و مسیر روشن برای خرید.
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

            <div class="store-filter-bar">
                <form
                    class="store-filter-form"
                    method="GET"
                    action="{{ route('products.index') }}"
                >
                    <label class="store-filter-form__search">
                        <span>جستجوی محصول</span>
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="نام محصول یا SKU"
                            autocomplete="off"
                        >
                    </label>

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

                    <button
                        class="button button--primary"
                        type="submit"
                    >
                        فیلتر
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

                <div class="catalog-toolbar">
                    <div>
                        <span class="eyebrow">CURATED CATALOG</span>
                        <strong>
                            صفحه {{ $products->currentPage() }}
                            از {{ $products->lastPage() }}
                        </strong>
                    </div>

                    <span class="catalog-toolbar__hint">
                        برای خرید مستقیم، دکمه «افزودن به سبد خرید» زیر هر محصول قرار دارد.
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

                <div class="empty-state">
                    <span class="eyebrow">NO RESULT</span>

                    <h2>
                        محصولی با این فیلتر پیدا نشد.
                    </h2>

                    <p>
                        عبارت جستجو یا فیلترها را تغییر بده و دوباره تلاش کن.
                    </p>

                    <a
                        class="button button--primary"
                        href="{{ route('products.index') }}"
                    >
                        بازگشت به همه محصولات
                    </a>
                </div>

            @endif

        </div>
    </section>

</div>
@endsection
