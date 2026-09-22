@extends('layouts.admin')

@section('title', 'مشتریان')

@section('content')

    <div class="admin-page">

        <div class="admin-page-header">
            <div>
                <h1 class="admin-page-title">مشتریان</h1>

                <p class="admin-page-description">
                    مدیریت و مشاهده اطلاعات مشتریان فروشگاه
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="admin-alert admin-alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="admin-card">

            <div class="admin-card-header">

                <div>
                    <h2 class="admin-card-title">
                        فهرست مشتریان
                    </h2>

                    <p class="admin-card-description">
                        {{ $customers->total() }} مشتری
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('admin.customers.index') }}"
                    class="admin-filter-form"
                >

                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="نام، ایمیل یا شماره تماس..."
                    >

                    <button
                        type="submit"
                        class="admin-btn admin-btn-primary"
                    >
                        جستجو
                    </button>

                    @if(request()->filled('q'))
                        <a
                            href="{{ route('admin.customers.index') }}"
                            class="admin-btn admin-btn-light"
                        >
                            پاک کردن
                        </a>
                    @endif

                </form>

            </div>

            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>مشتری</th>
                        <th>ایمیل</th>
                        <th>شماره تماس</th>
                        <th>تعداد سفارش</th>
                        <th>مجموع سفارش‌ها</th>
                        <th>عضویت</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($customers as $customer)

                        <tr>

                            <td>
                                <div class="admin-table-primary">
                                    {{ $customer->name ?: 'بدون نام' }}
                                </div>
                            </td>

                            <td dir="ltr">
                                {{ $customer->email }}
                            </td>

                            <td dir="ltr">
                                {{ $customer->phone ?: '—' }}
                            </td>

                            <td>
                                {{ number_format($customer->orders_count) }}
                            </td>

                            <td>
                                {{ number_format((float) ($customer->orders_sum_total ?? 0)) }}
                                تومان
                            </td>

                            <td>
                                {{ optional($customer->created_at)->format('Y/m/d') }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">
                                <div class="admin-empty">
                                    مشتری‌ای پیدا نشد.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

            @if($customers->hasPages())
                <div class="admin-pagination">
                    {{ $customers->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection
