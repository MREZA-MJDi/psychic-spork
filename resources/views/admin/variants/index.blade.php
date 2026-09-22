@extends('layouts.admin')

@section('title', 'واریانت‌های محصول')
@section('page-title', 'واریانت‌های محصول')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">
                واریانت‌های محصول
            </h1>

            <p class="admin-page-description">
                {{ $product->name }}
            </p>
        </div>

        <div class="admin-actions">

            <a
                href="{{ route('admin.products.edit', $product) }}"
                class="admin-btn admin-btn-light"
            >
                ویرایش محصول
            </a>

            <a
                href="{{ route('admin.products.variants.create', $product) }}"
                class="admin-btn admin-btn-primary"
            >
                + افزودن واریانت
            </a>

        </div>

    </div>


    <div class="admin-card admin-product-summary">

        <div class="admin-product-summary-main">

            <div>
                <div class="admin-product-name">
                    {{ $product->name }}
                </div>

                <div class="admin-product-meta">
                    دسته‌بندی:
                    {{ $product->category?->name ?? '—' }}
                </div>

                <div class="admin-product-meta">
                    برند:
                    {{ $product->brand?->name ?? '—' }}
                </div>
            </div>

        </div>

    </div>


    <div class="admin-card">

        <div class="admin-table-wrap">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>SKU</th>
                    <th>سایز</th>
                    <th>رنگ</th>
                    <th>قیمت</th>
                    <th>قیمت فروش</th>
                    <th>موجودی</th>
                    <th>وضعیت</th>
                    <th>ترتیب</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($variants as $variant)

                    <tr>

                        <td dir="ltr">
                            <strong>
                                {{ $variant->sku }}
                            </strong>
                        </td>

                        <td>
                            {{ $variant->size ?: '—' }}
                        </td>

                        <td>
                            <div class="admin-color-cell">

                                @if($variant->color_code)
                                    <span
                                        class="admin-color-dot"
                                        style="background-color: {{ $variant->color_code }}"
                                    ></span>
                                @endif

                                <span>
                                    {{ $variant->color ?: '—' }}
                                </span>

                            </div>
                        </td>

                        <td>
                            {{ number_format((float) $variant->price) }}
                            <span class="admin-muted">تومان</span>
                        </td>

                        <td>
                            @if($variant->sale_price !== null)
                                {{ number_format((float) $variant->sale_price) }}
                                <span class="admin-muted">تومان</span>
                            @else
                                —
                            @endif
                        </td>

                        <td>

                            @if($variant->stock <= $variant->low_stock_threshold)

                                <span class="admin-badge admin-badge-warning">
                                    {{ number_format($variant->stock) }}
                                </span>

                            @else

                                <span class="admin-badge admin-badge-success">
                                    {{ number_format($variant->stock) }}
                                </span>

                            @endif

                        </td>

                        <td>

                            @if($variant->is_active)

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
                            {{ $variant->sort_order }}
                        </td>

                        <td>

                            <div class="admin-actions">

                                <a
                                    href="{{ route('admin.products.variants.edit', [$product, $variant]) }}"
                                    class="admin-btn admin-btn-sm admin-btn-light"
                                >
                                    ویرایش
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}"
                                    onsubmit="return confirm('آیا از حذف این واریانت مطمئن هستید؟');"
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
                        <td colspan="9">

                            <div class="admin-empty">
                                هنوز واریانتی برای این محصول ثبت نشده است.
                            </div>

                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        @if($variants->hasPages())

            <div class="admin-pagination">
                {{ $variants->links() }}
            </div>

        @endif

    </div>

@endsection
