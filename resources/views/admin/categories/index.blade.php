@extends('layouts.admin')

@section('title', 'دسته‌بندی‌ها')
@section('page-title', 'دسته‌بندی‌ها')

@section('content')

    <div class="admin-page-head">
        <div>
            <h1 class="admin-page-title">دسته‌بندی‌ها</h1>
            <p class="admin-page-description">
                ساختار دسته‌بندی محصولات فروشگاه
            </p>
        </div>

        <a href="{{ route('admin.categories.create') }}" class="admin-btn admin-btn-primary">
            + افزودن دسته‌بندی
        </a>
    </div>

    <div class="admin-card admin-filter-card">

        <form method="GET" action="{{ route('admin.categories.index') }}" class="admin-filter-grid">

            <div class="admin-field">
                <label for="q">جستجو</label>

                <input
                    id="q"
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="نام یا slug..."
                >
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
                </select>
            </div>

            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    جستجو
                </button>

                <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-light">
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
                    <th>نام</th>
                    <th>والد</th>
                    <th>محصولات</th>
                    <th>ترتیب</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($categories as $category)

                    <tr>

                        <td>
                            <div class="admin-product-name">
                                {{ $category->name }}
                            </div>

                            <div class="admin-product-meta">
                                {{ $category->slug }}
                            </div>
                        </td>

                        <td>
                            {{ $category->parent?->name ?? 'دسته اصلی' }}
                        </td>

                        <td>
                            {{ number_format($category->products_count ?? 0) }}
                        </td>

                        <td>
                            {{ $category->sort_order }}
                        </td>

                        <td>
                            @if($category->is_active)
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
                            <div class="admin-actions">

                                <a
                                    href="{{ route('admin.categories.edit', $category) }}"
                                    class="admin-btn admin-btn-sm admin-btn-light"
                                >
                                    ویرایش
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.categories.destroy', $category) }}"
                                    onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');"
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
                        <td colspan="6">
                            <div class="admin-empty">
                                دسته‌بندی‌ای پیدا نشد.
                            </div>
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        @if($categories->hasPages())
            <div class="admin-pagination">
                {{ $categories->links() }}
            </div>
        @endif

    </div>

@endsection
