@extends('layouts.admin')

@section('title', 'داشبورد مدیریت')

@section('page-title', 'داشبورد')

@section('content')

    {{-- =====================================================
         HEADER
         ===================================================== --}}

    <div class="admin-page-head">

        <div>
            <h2 class="admin-page-head__title">
                نمای کلی فروشگاه
            </h2>

            <p class="admin-page-head__text">
                وضعیت فروش، سفارش‌ها، موجودی و عملکرد فروشگاه.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('admin.dashboard') }}"
            class="admin-period-form"
        >
            <label for="dashboard-period">
                بازه
            </label>

            <select
                id="dashboard-period"
                name="period"
                class="admin-select"
                onchange="this.form.submit()"
            >
                <option
                    value="7"
                    @selected($period === 7)
                >
                    ۷ روز اخیر
                </option>

                <option
                    value="30"
                    @selected($period === 30)
                >
                    ۳۰ روز اخیر
                </option>

                <option
                    value="60"
                    @selected($period === 60)
                >
                    ۶۰ روز اخیر
                </option>

                <option
                    value="90"
                    @selected($period === 90)
                >
                    ۹۰ روز اخیر
                </option>
            </select>
        </form>

    </div>


    {{-- =====================================================
         KPI CARDS
         ===================================================== --}}

    <div class="admin-dashboard-stats">

        <div class="admin-stat-card">

            <div class="admin-stat-card__top">
                <span class="admin-stat-card__label">
                    درآمد پرداخت‌شده
                </span>

                <span class="admin-stat-card__icon">
                    ر
                </span>
            </div>

            <strong class="admin-stat-card__value">
                {{ number_format($revenue) }}
            </strong>

            <span class="admin-stat-card__meta">
                تومان در {{ $period }} روز اخیر
            </span>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-card__top">
                <span class="admin-stat-card__label">
                    هزینه‌ها
                </span>

                <span class="admin-stat-card__icon">
                    −
                </span>
            </div>

            <strong class="admin-stat-card__value">
                {{ number_format($expenses) }}
            </strong>

            <span class="admin-stat-card__meta">
                هزینه ثبت‌شده در بازه
            </span>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-card__top">
                <span class="admin-stat-card__label">
                    خالص جریان نقدی
                </span>

                <span class="admin-stat-card__icon">
                    ↗
                </span>
            </div>

            <strong class="admin-stat-card__value {{ $netCash < 0 ? 'is-negative' : '' }}">
                {{ number_format($netCash) }}
            </strong>

            <span class="admin-stat-card__meta">
                درآمد منهای هزینه
            </span>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-card__top">
                <span class="admin-stat-card__label">
                    سفارش‌ها
                </span>

                <span class="admin-stat-card__icon">
                    #
                </span>
            </div>

            <strong class="admin-stat-card__value">
                {{ number_format($ordersCount) }}
            </strong>

            <span class="admin-stat-card__meta">
                {{ number_format($paidOrdersCount) }} سفارش پرداخت‌شده
            </span>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-card__top">
                <span class="admin-stat-card__label">
                    سفارش‌های جاری
                </span>

                <span class="admin-stat-card__icon">
                    ◷
                </span>
            </div>

            <strong class="admin-stat-card__value">
                {{ number_format($pendingOrders) }}
            </strong>

            <span class="admin-stat-card__meta">
                در حال پردازش
            </span>

        </div>


        <div class="admin-stat-card">

            <div class="admin-stat-card__top">
                <span class="admin-stat-card__label">
                    مشتریان
                </span>

                <span class="admin-stat-card__icon">
                    ♙
                </span>
            </div>

            <strong class="admin-stat-card__value">
                {{ number_format($customers) }}
            </strong>

            <span class="admin-stat-card__meta">
                حساب مشتری فعال
            </span>

        </div>

    </div>


    {{-- =====================================================
         MAIN GRID
         ===================================================== --}}

    <div class="admin-dashboard-grid">


        {{-- =================================================
             SALES CHART
             ================================================= --}}

        <section class="admin-card admin-dashboard-chart">

            <div class="admin-card__header">

                <div>
                    <h3 class="admin-card__title">
                        فروش روزانه
                    </h3>

                    <p class="admin-card__subtitle">
                        درآمد پرداخت‌شده و تعداد سفارش‌ها
                    </p>
                </div>

                <span class="admin-dashboard-period">
                    {{ $period }} روز
                </span>

            </div>

            <div class="admin-card__body">

                @if($daily->isNotEmpty())

                    <div class="admin-chart">

                        <div class="admin-chart__bars">

                            @foreach($daily as $day)

                                @php
                                    $height = $day['income'] > 0
                                        ? max(4, ($day['income'] / $maxIncome) * 100)
                                        : 2;
                                @endphp

                                <div class="admin-chart__item">

                                    <div class="admin-chart__value">
                                        @if($day['income'] > 0)
                                            {{ number_format($day['income'] / 1000000, 1) }}M
                                        @endif
                                    </div>

                                    <div class="admin-chart__bar-wrap">

                                        <div
                                            class="admin-chart__bar"
                                            style="height: {{ $height }}%"
                                            title="{{ number_format($day['income']) }} تومان"
                                        ></div>

                                    </div>

                                    <span class="admin-chart__label">
                                        {{ $day['label'] }}
                                    </span>

                                    <span class="admin-chart__orders">
                                        {{ $day['orders'] }}
                                    </span>

                                </div>

                            @endforeach

                        </div>

                    </div>

                @else

                    <div class="admin-empty">
                        <div class="admin-empty__icon">
                            ∿
                        </div>

                        <h3>
                            هنوز داده‌ای وجود ندارد
                        </h3>

                        <p>
                            با ثبت سفارش، نمودار فروش این بخش نمایش داده می‌شود.
                        </p>
                    </div>

                @endif

            </div>

        </section>


        {{-- =================================================
             ORDER BREAKDOWN
             ================================================= --}}

        <section class="admin-card">

            <div class="admin-card__header">

                <div>
                    <h3 class="admin-card__title">
                        وضعیت سفارش‌ها
                    </h3>

                    <p class="admin-card__subtitle">
                        تعداد سفارش‌ها بر اساس وضعیت
                    </p>
                </div>

            </div>

            <div class="admin-card__body">

                @php
                    $statusClasses = [
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'preparing' => 'info',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'returned' => 'neutral',
                    ];
                @endphp

                <div class="admin-status-list">

                    @foreach($statusNames as $status => $name)

                        @php
                            $count = (int) ($orderBreakdown[$status] ?? 0);
                            $class = $statusClasses[$status] ?? 'neutral';
                        @endphp

                        <div class="admin-status-row">

                            <div class="admin-status-row__name">

                                <span class="admin-status-dot admin-status-dot--{{ $class }}"></span>

                                <span>
                                    {{ $name }}
                                </span>

                            </div>

                            <strong>
                                {{ number_format($count) }}
                            </strong>

                        </div>

                    @endforeach

                </div>

            </div>

        </section>


    </div>


    {{-- =====================================================
         SECOND GRID
         ===================================================== --}}

    <div class="admin-dashboard-grid admin-dashboard-grid--equal">


        {{-- =================================================
             LOW STOCK
             ================================================= --}}

        <section class="admin-card">

            <div class="admin-card__header">

                <div>
                    <h3 class="admin-card__title">
                        موجودی کم
                    </h3>

                    <p class="admin-card__subtitle">
                        {{ number_format($lowStock) }} تنوع محصول نیاز به بررسی دارد.
                    </p>
                </div>

                <a
                    href="{{ route('admin.inventory.index') }}"
                    class="admin-btn admin-btn--ghost admin-btn--sm"
                >
                    مشاهده همه
                </a>

            </div>

            <div class="admin-card__body admin-card__body--flush">

                @if($lowStockVariants->isNotEmpty())

                    <div class="admin-mini-list">

                        @foreach($lowStockVariants as $variant)

                            <div class="admin-mini-row">

                                <div class="admin-mini-row__main">

                                    <strong>
                                        {{ $variant->product?->name ?? 'محصول حذف‌شده' }}
                                    </strong>

                                    <small>
                                        SKU:
                                        {{ $variant->sku ?: '—' }}
                                    </small>

                                </div>

                                <div class="admin-mini-row__stock">

                                    <strong class="{{ $variant->stock <= 0 ? 'is-danger' : '' }}">
                                        {{ number_format($variant->stock) }}
                                    </strong>

                                    <small>
                                        موجودی
                                    </small>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="admin-empty admin-empty--compact">

                        <div class="admin-empty__icon">
                            ✓
                        </div>

                        <h3>
                            موجودی وضعیت خوبی دارد
                        </h3>

                        <p>
                            محصولی در محدوده هشدار موجودی نیست.
                        </p>

                    </div>

                @endif

            </div>

        </section>


        {{-- =================================================
             INVENTORY SUMMARY
             ================================================= --}}

        <section class="admin-card">

            <div class="admin-card__header">

                <div>
                    <h3 class="admin-card__title">
                        خلاصه موجودی
                    </h3>

                    <p class="admin-card__subtitle">
                        ارزش فعلی موجودی فعال فروشگاه
                    </p>
                </div>

                <a
                    href="{{ route('admin.inventory.index') }}"
                    class="admin-btn admin-btn--secondary admin-btn--sm"
                >
                    مدیریت موجودی
                </a>

            </div>

            <div class="admin-card__body">

                <div class="admin-inventory-summary">

                    <div class="admin-inventory-summary__main">

                        <span>
                            ارزش موجودی
                        </span>

                        <strong>
                            {{ number_format($inventoryValue) }}
                            <small>تومان</small>
                        </strong>

                    </div>

                    <div class="admin-inventory-summary__divider"></div>

                    <div class="admin-inventory-summary__item">

                        <span>
                            تنوع کم‌موجودی
                        </span>

                        <strong>
                            {{ number_format($lowStock) }}
                        </strong>

                    </div>

                </div>

            </div>

        </section>

    </div>


    {{-- =====================================================
         RECENT ORDERS
         ===================================================== --}}

    <section class="admin-card admin-dashboard-section">

        <div class="admin-card__header">

            <div>
                <h3 class="admin-card__title">
                    آخرین سفارش‌ها
                </h3>

                <p class="admin-card__subtitle">
                    جدیدترین سفارش‌های ثبت‌شده در فروشگاه
                </p>
            </div>

            <a
                href="{{ route('admin.orders.index') }}"
                class="admin-btn admin-btn--secondary admin-btn--sm"
            >
                همه سفارش‌ها
            </a>

        </div>

        @if($recentOrders->isNotEmpty())

            <div class="admin-table-wrap admin-table-wrap--plain">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>
                            سفارش
                        </th>

                        <th>
                            مشتری
                        </th>

                        <th>
                            مبلغ
                        </th>

                        <th>
                            وضعیت
                        </th>

                        <th>
                            پرداخت
                        </th>

                        <th>
                            تاریخ
                        </th>

                        <th>
                        </th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($recentOrders as $order)

                        @php
                            $orderStatusClasses = [
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'preparing' => 'info',
                                'shipped' => 'success',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                                'returned' => 'neutral',
                            ];

                            $paymentClasses = [
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'neutral',
                            ];
                        @endphp

                        <tr>

                            <td>
                                    <span class="admin-table__primary">
                                        {{ $order->order_number }}
                                    </span>
                            </td>

                            <td>
                                    <span class="admin-table__primary">
                                        {{ $order->customer_name }}
                                    </span>

                                @if($order->customer_phone)
                                    <span class="admin-table__muted">
                                            {{ $order->customer_phone }}
                                        </span>
                                @endif
                            </td>

                            <td>
                                {{ number_format($order->total) }}
                                تومان
                            </td>

                            <td>

                                    <span class="admin-badge admin-badge--{{ $orderStatusClasses[$order->status] ?? 'neutral' }}">
                                        {{ $statusNames[$order->status] ?? $order->status }}
                                    </span>

                            </td>

                            <td>

                                    <span class="admin-badge admin-badge--{{ $paymentClasses[$order->payment_status] ?? 'neutral' }}">
                                        @switch($order->payment_status)

                                            @case('paid')
                                            پرداخت‌شده
                                            @break

                                            @case('pending')
                                            در انتظار پرداخت
                                            @break

                                            @case('failed')
                                            ناموفق
                                            @break

                                            @case('refunded')
                                            بازپرداخت‌شده
                                            @break

                                            @default
                                            {{ $order->payment_status }}

                                        @endswitch
                                    </span>

                            </td>

                            <td>
                                {{ optional($order->placed_at)->format('Y/m/d H:i') }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('admin.orders.show', $order) }}"
                                    class="admin-btn admin-btn--ghost admin-btn--sm"
                                >
                                    جزئیات
                                </a>
                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="admin-empty">

                <div class="admin-empty__icon">
                    #
                </div>

                <h3>
                    هنوز سفارشی ثبت نشده
                </h3>

                <p>
                    آخرین سفارش‌های فروشگاه پس از ثبت در این بخش نمایش داده می‌شوند.
                </p>

            </div>

        @endif

    </section>


    {{-- =====================================================
         TOP PRODUCTS
         ===================================================== --}}

    <section class="admin-card admin-dashboard-section">

        <div class="admin-card__header">

            <div>
                <h3 class="admin-card__title">
                    پرفروش‌ترین محصولات
                </h3>

                <p class="admin-card__subtitle">
                    بر اساس تعداد اقلام ثبت‌شده در سفارش‌ها
                </p>
            </div>

            <a
                href="{{ route('admin.products.index') }}"
                class="admin-btn admin-btn--secondary admin-btn--sm"
            >
                مدیریت محصولات
            </a>

        </div>

        @if($topProducts->isNotEmpty())

            <div class="admin-top-products">

                @foreach($topProducts as $index => $product)

                    <div class="admin-top-product">

                        <span class="admin-top-product__rank">
                            {{ $index + 1 }}
                        </span>

                        <div class="admin-top-product__main">

                            <strong>
                                {{ $product->name }}
                            </strong>

                            <small>
                                {{ $product->category?->name ?? 'بدون دسته‌بندی' }}
                            </small>

                        </div>

                        <div class="admin-top-product__sales">

                            <strong>
                                {{ number_format((int) ($product->order_items_sum_quantity ?? 0)) }}
                            </strong>

                            <small>
                                عدد فروخته‌شده
                            </small>

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="admin-empty">

                <div class="admin-empty__icon">
                    ★
                </div>

                <h3>
                    هنوز داده فروش وجود ندارد
                </h3>

                <p>
                    پس از ثبت سفارش، محصولات پرفروش اینجا نمایش داده می‌شوند.
                </p>

            </div>

        @endif

    </section>


@endsection
