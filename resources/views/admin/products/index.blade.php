@extends('layouts.admin')

@section('title', 'محصولات')
@section('page-title', 'محصولات')

@section('content')

    <div class="admin-page-head">
        <div>
            <h1 class="admin-page-title">محصولات</h1>
            <p class="admin-page-description">
                مدیریت محصولات، وضعیت انتشار و موجودی
            </p>
        </div>

        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary">
            + افزودن محصول
        </a>
    </div>

    <div class="admin-card admin-filter-card">
        <form method="GET" action="{{ route('admin.products.index') }}" class="admin-filter-grid">

            <div class="admin-field">
                <label for="q">جستجو</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="نام، SKU یا slug..."
                >
            </div>

            <div class="admin-field">
                <label for="category_id">دسته‌بندی</label>
                <select id="category_id" name="category_id">
                    <option value="">همه دسته‌بندی‌ها</option>

                    @foreach($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) request('category_id') === (string) $category->id)
                        >
                        {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-field">
                <label for="brand_id">برند</label>
                <select id="brand_id" name="brand_id">
                    <option value="">همه برندها</option>

                    @foreach($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected((string) request('brand_id') === (string) $brand->id)
                        >
                        {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-field">
                <label for="status">وضعیت</label>
                <select id="status" name="status">
                    <option value="">همه</option>
                    <option value="active" @selected(request('status') === 'active')>
                    فعال
                    </option>
                    <option value="inactive" @selected(request('status') === 'inactive')>
                    غیرفعال
                    </option>
                    <option value="featured" @selected(request('status') === 'featured')>
                    ویژه
                    </option>
                </select>
            </div>

            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    جستجو
                </button>

                <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-light">
                    پاک کردن
                </a>
            </div>

        </form>
    </div>

    <div class="admin-card">
        <div class="admin-table-wrap">
            <table class="admin-table">

                <thead>
                <tr>
                    <th>محصول</th>
                    <th>دسته‌بندی</th>
                    <th>برند</th>
                    <th>قیمت</th>
                    <th>موجودی</th>
                    <th>وضعیت</th>
                    <th>ویژه</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>
                @forelse($products as $product)

                    @php
                        $variant = $product->variants->first();
                        $image = $product->galleryMedia->first();
                    @endphp

                    <tr>

                        <td>
                            <div class="admin-product-cell">

                                @if($image?->url)
                                    <img
                                        src="{{ $image->url }}"
                                        alt="{{ $product->name }}"
                                        class="admin-product-thumb"
                                    >
                                @else
                                    <div class="admin-product-thumb admin-product-thumb-empty">
                                        —
                                    </div>
                                @endif

                                <div>
                                    <div class="admin-product-name">
                                        {{ $product->name }}
                                    </div>

                                    <div class="admin-product-meta">
                                        {{ $product->slug }}
                                    </div>

                                    @if($variant?->sku)
                                        <div class="admin-product-meta">
                                            SKU: {{ $variant->sku }}
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </td>

                        <td>
                            {{ $product->category?->name ?? '—' }}
                        </td>

                        <td>
                            {{ $product->brand?->name ?? '—' }}
                        </td>

                        <td>
                            @if($variant)
                                <div>
                                    {{ number_format((float) $variant->effective_price) }}
                                    <span class="admin-muted">تومان</span>
                                </div>

                                @if($variant->sale_price !== null && (float) $variant->sale_price < (float) $variant->price)
                                    <div class="admin-old-price">
                                        {{ number_format((float) $variant->price) }}
                                    </div>
                                @endif
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            @if($variant)
                                @if($variant->stock <= $variant->low_stock_threshold)
                                    <span class="admin-badge admin-badge-warning">
                                        {{ number_format($variant->stock) }}
                                    </span>
                                @else
                                    <span class="admin-badge admin-badge-success">
                                        {{ number_format($variant->stock) }}
                                    </span>
                                @endif
                            @else
                                <span class="admin-muted">بدون واریانت</span>
                            @endif
                        </td>

                        <td>
                            @if($product->is_active)
                                <span class="admin-badge admin-badge-success">
                                    فعال
                                </span>
                            @else
                                <span class="admin-badge admin-badge-danger">
                                    غیرفعال
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($product->is_featured)
                                <span class="admin-badge admin-badge-info">
                                    ویژه
                                </span>
                            @else
                                <span class="admin-muted">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="admin-actions">

                                <a
                                    href="{{ route('admin.products.edit', $product) }}"
                                    class="admin-btn admin-btn-sm admin-btn-light"
                                >
                                    ویرایش
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.products.destroy', $product) }}"
                                    onsubmit="return confirm('آیا از حذف این محصول مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="admin-btn admin-btn-sm admin-btn-danger"
                                    >
                                        حذف
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8">
                            <div class="admin-empty">
                                محصولی پیدا نشد.
                            </div>
                        </td>
                    </tr>

                @endforelse
                </tbody>

            </table>
        </div>

        @if($products->hasPages())
            <div class="admin-pagination">
                {{ $products->links() }}
            </div>
        @endif
    </div>

@endsection
