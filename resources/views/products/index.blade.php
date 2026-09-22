@extends('layouts.store')

@section('title', 'محصولات — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">JANAN COLLECTION</span>
        <h1>محصولات</h1>
        <p>تمام محصولات فعال فروشگاه، مستقیماً از Backend.</p>
    </div>
</section>

<section class="section-block">
    <div class="container">
        <div class="store-filter-bar">
            <form class="store-filter-form" method="GET" action="{{ route('products.index') }}">
                <label>
                    <span>جستجو</span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="نام محصول یا SKU">
                </label>
                <label>
                    <span>دسته‌بندی</span>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">همه دسته‌ها</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>برند</span>
                    <select name="brand" onchange="this.form.submit()">
                        <option value="">همه برندها</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="button button--primary" type="submit">اعمال فیلتر</button>
                @if(request()->hasAny(['q','category','brand']))
                    <a class="button button--ghost" href="{{ route('products.index') }}">پاک‌کردن</a>
                @endif
            </form>
            <span class="store-result-count">{{ number_format($products->total()) }} محصول</span>
        </div>

        @if($products->isNotEmpty())
            <div class="product-grid">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>

            <div class="store-pagination">{{ $products->links() }}</div>
        @else
            <div class="empty-state">
                <span class="eyebrow">NO RESULT</span>
                <h2>محصولی با این فیلتر پیدا نشد.</h2>
                <a class="button button--primary" href="{{ route('products.index') }}">بازگشت به محصولات</a>
            </div>
        @endif
    </div>
</section>
@endsection
