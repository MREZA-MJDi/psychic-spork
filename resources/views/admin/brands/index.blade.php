@extends('layouts.admin')

@section('title', 'برندها')
@section('page-title', 'برندها')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">برندها</h1>
            <p class="admin-page-description">
                مدیریت برندهای فروشگاه
            </p>
        </div>

        <a href="{{ route('admin.brands.create') }}" class="admin-btn admin-btn-primary">
            + افزودن برند
        </a>

    </div>

    <div class="admin-card admin-filter-card">

        <form
            method="GET"
            action="{{ route('admin.brands.index') }}"
            class="admin-filter-grid"
        >

            <div class="admin-field">

                <label for="q">
                    جستجو
                </label>

                <input
                    id="q"
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="نام یا slug..."
                >

            </div>

            <div class="admin-field">

                <label for="status">
                    وضعیت
                </label>

                <select id="status" name="status">

                    <option value="">
                        همه
                    </option>

                    <option value="active" @selected(request('status') === 'active')>
                    فعال
                    </option>

                    <option value="inactive" @selected(request('status') === 'inactive')>
                    غیرفعال
                    </option>

                </select>

            </div>

            <div class="admin-filter-actions">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary"
                >
                    جستجو
                </button>

                <a
                    href="{{ route('admin.brands.index') }}"
                    class="admin-btn admin-btn-light"
                >
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
                    <th>برند</th>
                    <th>Slug</th>
                    <th>محصولات</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($brands as $brand)

                    @php
                        $logo = $brand->logoMedia;
                    @endphp

                    <tr>

                        <td>

                            <div class="admin-product-cell">

                                @if($logo?->url)

                                    <img
                                        src="{{ $logo->url }}"
                                        alt="{{ $brand->name }}"
                                        class="admin-product-thumb"
                                    >

                                @else

                                    <div class="admin-product-thumb admin-product-thumb-empty">
                                        {{ mb_substr($brand->name, 0, 1) }}
                                    </div>

                                @endif

                                <div>

                                    <div class="admin-product-name">
                                        {{ $brand->name }}
                                    </div>

                                    @if($brand->description)
                                        <div class="admin-product-meta">
                                            {{ \Illuminate\Support\Str::limit($brand->description, 70) }}
                                        </div>
                                    @endif

                                </div>

                            </div>

                        </td>

                        <td dir="ltr">
                            {{ $brand->slug }}
                        </td>

                        <td>
                            {{ number_format($brand->products_count ?? 0) }}
                        </td>

                        <td>

                            @if($brand->is_active)

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
                                    href="{{ route('admin.brands.edit', $brand) }}"
                                    class="admin-btn admin-btn-sm admin-btn-light"
                                >
                                    ویرایش
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('admin.brands.destroy', $brand) }}"
                                    onsubmit="return confirm('آیا از حذف این برند مطمئن هستید؟');"
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
                        <td colspan="5">

                            <div class="admin-empty">
                                برندی پیدا نشد.
                            </div>

                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        @if($brands->hasPages())

            <div class="admin-pagination">
                {{ $brands->links() }}
            </div>

        @endif

    </div>

@endsection
