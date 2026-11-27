@extends('layouts.admin')

@section('title', 'مشتریان')
@section('page-title', 'مشتریان')

@section('content')

    {{-- =====================================================
        PAGE HEADER
    ====================================================== --}}

    <div class="admin-page-head">

        <div>

            <h1 class="admin-page-head__title">
                مشتریان
            </h1>

            <p class="admin-page-head__text">
                مدیریت و مشاهده اطلاعات مشتریان فروشگاه
            </p>

        </div>

    </div>


    {{-- =====================================================
        SEARCH
    ====================================================== --}}

    <div class="admin-card admin-filter-card">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    جستجو
                </h2>

                <p class="admin-card-description">
                    مشتری را با نام، ایمیل یا شماره تماس پیدا کنید.
                </p>

            </div>

        </div>


        <form
            method="GET"
            action="{{ route('admin.customers.index') }}"
        >

            <div class="admin-filter-grid">

                <div class="admin-field">

                    <label for="q">
                        جستجوی مشتری
                    </label>

                    <input
                        id="q"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="نام، ایمیل یا شماره تماس..."
                    >

                </div>


                <div class="admin-filter-actions">

                    <button
                        type="submit"
                        class="admin-btn admin-btn--secondary"
                    >
                        جستجو
                    </button>

                    @if(request()->filled('q'))

                        <a
                            href="{{ route('admin.customers.index') }}"
                            class="admin-btn admin-btn--ghost"
                        >
                            پاک کردن
                        </a>

                    @endif

                </div>

            </div>

        </form>

    </div>


    {{-- =====================================================
        CUSTOMERS
    ====================================================== --}}

    <div class="admin-card">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    فهرست مشتریان
                </h2>

                <p class="admin-card-description">
                    {{ \App\Support\PersianNumber::format($customers->total()) }}
                    مشتری
                </p>

            </div>

        </div>


        @if($customers->count())

            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>

                    <tr>

                        <th>
                            مشتری
                        </th>

                        <th>
                            ایمیل
                        </th>

                        <th>
                            شماره تماس
                        </th>

                        <th>
                            تعداد سفارش
                        </th>

                        <th>
                            مجموع سفارش‌ها
                        </th>

                        <th>
                            خرید چکی
                        </th>

                        <th>
                            عضویت
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    @foreach($customers as $customer)

                        @php

                            $customerName =
                                filled($customer->name)
                                    ? $customer->name
                                    : 'بدون نام';

                            $ordersCount =
                                (int) ($customer->orders_count ?? 0);

                            $ordersTotal =
                                (float) (
                                    $customer->orders_sum_total
                                    ?? 0
                                );

                        @endphp


                        <tr>

                            {{-- CUSTOMER --}}

                            <td>

                                <div class="admin-product-cell">

                                    <div class="admin-product-thumb-empty">

                                        {{ mb_substr(
                                            $customerName,
                                            0,
                                            1
                                        ) }}

                                    </div>

                                    <div>

                                        <div class="admin-product-name">
                                            {{ $customerName }}
                                        </div>

                                        <div class="admin-product-meta">
                                            مشتری فروشگاه
                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- EMAIL --}}

                            <td>

                                @if($customer->email)

                                    <span
                                        class="admin-muted"
                                        dir="ltr"
                                    >
                                        {{ $customer->email }}
                                    </span>

                                @else

                                    <span class="admin-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- PHONE --}}

                            <td>

                                @if($customer->phone)

                                    <span
                                        class="admin-muted"
                                        dir="ltr"
                                    >
                                        {{ $customer->phone }}
                                    </span>

                                @else

                                    <span class="admin-muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- ORDERS COUNT --}}

                            <td>

                                <span class="admin-price">
                                    {{ \App\Support\PersianNumber::format($ordersCount) }}
                                </span>

                            </td>


                            {{-- ORDERS TOTAL --}}

                            <td>

                                <div class="admin-price">
                                    {{ \App\Support\PersianNumber::format($ordersTotal) }}
                                </div>

                                <div class="admin-muted">
                                    تومان
                                </div>

                            </td>


                            {{-- CHEQUE PERMISSION --}}

                            <td>
                                @php
                                    $chequePermission = $customer->chequePermission;
                                    $chequeEnabled = $chequePermission?->isApproved() ?? false;
                                @endphp
                                @if($chequeEnabled)
                                    <span class="admin-badge admin-badge--success">فعال</span>
                                @elseif($chequePermission?->isPending())
                                    <span class="admin-badge admin-badge--warning">در انتظار بررسی</span>
                                @else
                                    <span class="admin-badge admin-badge--neutral">غیرفعال</span>
                                @endif
                                <a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ route('admin.cheques.index', ['tab' => 'customers', 'access' => 'all', 'customer_id' => $customer->id]) }}">مدیریت مجوز</a>
                            </td>

                            {{-- CREATED AT --}}

                            <td>

                                @if($customer->created_at)

                                    <span class="admin-muted">

                                        <span class="admin-local-date" data-admin-date="{{ $customer->created_at->toIso8601String() }}" data-admin-date-format="day">{{ $customer->created_at->format('Y/m/d') }}</span>

                                    </span>

                                @else

                                    <span class="admin-muted">
                                        —
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>


            {{-- PAGINATION --}}

            @if($customers->hasPages())

                <div class="admin-pagination">

                    {{ $customers->links() }}

                </div>

            @endif


        @else

            <div class="admin-empty">

                <div class="admin-empty__icon">
                    —
                </div>

                <h3 class="admin-empty__title">
                    مشتری‌ای پیدا نشد
                </h3>

                <p class="admin-empty__text">
                    با جستجوی فعلی مشتری‌ای برای نمایش وجود ندارد.
                </p>

            </div>

        @endif

    </div>

@endsection
