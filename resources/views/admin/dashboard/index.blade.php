@extends('layouts.admin')

@section('title', 'داشبورد مدیریت')
@section('page-title', 'داشبورد')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | STATUS MAPS
        |--------------------------------------------------------------------------
        */

        $statusClasses = [
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


    <div class="admin-dashboard">

        {{-- =========================================================
             PAGE HEADER
        ========================================================== --}}

        <div class="admin-page-head">

            <div>

                <h1 class="admin-page-head__title">
                    نمای کلی فروشگاه
                </h1>

                <p class="admin-page-head__text">
                    وضعیت فروش، سفارش‌ها، موجودی و عملکرد فروشگاه
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

                    @foreach([
                        7 => '۷ روز اخیر',
                        30 => '۳۰ روز اخیر',
                        60 => '۶۰ روز اخیر',
                        90 => '۹۰ روز اخیر',
                    ] as $days => $label)

                        <option
                            value="{{ $days }}"
                            @selected((int) $period === $days)
                        >
                            {{ $label }}
                        </option>

                    @endforeach

                </select>

            </form>

        </div>


        {{-- =========================================================
             KPI
        ========================================================== --}}

        <section class="admin-dashboard-stats">

            {{-- Revenue --}}

            <article class="admin-stat-card admin-stat-card--success">

                <div class="admin-stat-card__label">
                    درآمد پرداخت‌شده
                </div>

                <div class="admin-stat-card__value">
                    {{ number_format((float) $revenue) }}
                </div>

                <div class="admin-stat-card__meta">
                    تومان در {{ number_format($period) }} روز اخیر
                </div>

            </article>


            {{-- Expenses --}}

            <article class="admin-stat-card admin-stat-card--danger">

                <div class="admin-stat-card__label">
                    هزینه‌ها
                </div>

                <div class="admin-stat-card__value">
                    {{ number_format((float) $expenses) }}
                </div>

                <div class="admin-stat-card__meta">
                    هزینه ثبت‌شده در بازه
                </div>

            </article>


            {{-- Net Cash --}}

            <article
                class="admin-stat-card {{ $netCash < 0 ? 'admin-stat-card--danger' : 'admin-stat-card--info' }}"
            >

                <div class="admin-stat-card__label">
                    خالص جریان نقدی
                </div>

                <div class="admin-stat-card__value">
                    {{ number_format((float) $netCash) }}
                </div>

                <div class="admin-stat-card__meta">
                    درآمد منهای هزینه
                </div>

            </article>


            {{-- Orders --}}

            <article class="admin-stat-card">

                <div class="admin-stat-card__label">
                    سفارش‌ها
                </div>

                <div class="admin-stat-card__value">
                    {{ number_format((int) $ordersCount) }}
                </div>

                <div class="admin-stat-card__meta">
                    {{ number_format((int) $paidOrdersCount) }}
                    سفارش پرداخت‌شده
                </div>

            </article>


            {{-- Processing --}}

            <article class="admin-stat-card admin-stat-card--warning">

                <div class="admin-stat-card__label">
                    سفارش‌های جاری
                </div>

                <div class="admin-stat-card__value">
                    {{ number_format((int) $pendingOrders) }}
                </div>

                <div class="admin-stat-card__meta">
                    در حال پردازش
                </div>

            </article>


            {{-- Customers --}}

            <article class="admin-stat-card admin-stat-card--info">

                <div class="admin-stat-card__label">
                    مشتریان
                </div>

                <div class="admin-stat-card__value">
                    {{ number_format((int) $customers) }}
                </div>

                <div class="admin-stat-card__meta">
                    حساب مشتری
                </div>

            </article>

        </section>


        {{-- =========================================================
             SALES + ORDER STATUS
        ========================================================== --}}

        <div class="admin-dashboard-grid">


            {{-- =====================================================
                 SALES
            ====================================================== --}}

            <section class="admin-card">

                <header class="admin-card-header">

                    <div>

                        <h2 class="admin-card-title">
                            فروش روزانه
                        </h2>

                        <p class="admin-card-description">
                            درآمد پرداخت‌شده در {{ number_format($period) }} روز اخیر
                        </p>

                    </div>


                    <span class="admin-badge admin-badge--neutral">
                        {{ number_format($period) }} روز
                    </span>

                </header>


                <div class="admin-card-body">

                    @if($daily->isNotEmpty())

                        @php
                            $chartMax = max((float) $maxIncome, 1);
                        @endphp


                        <div
                            style="
                                display:grid;
                                grid-template-columns:repeat({{ $daily->count() }}, minmax(14px,1fr));
                                gap:8px;
                                align-items:end;
                                min-height:260px;
                                overflow-x:auto;
                                padding-top:18px;
                                "
                        >

                            @foreach($daily as $day)

                                @php
                                    $income = (float) ($day['income'] ?? 0);

                                    $height = $income > 0
                                        ? max(5, ($income / $chartMax) * 100)
                                        : 3;
                                @endphp


                                <div
                                    style="
                                        min-width:28px;
                                        height:230px;
                                        display:flex;
                                        flex-direction:column;
                                        align-items:center;
                                        justify-content:flex-end;
                                        gap:6px;
                                    "
                                >

                                    <span
                                        style="
                                            color:var(--admin-muted);
                                            font-size:8px;
                                            white-space:nowrap;
                                        "
                                    >
                                        @if($income > 0)
                                            {{ number_format($income / 1000000, 1) }}M
                                        @else
                                            —
                                        @endif
                                    </span>


                                    <div
                                        style="
                                            width:100%;
                                            max-width:28px;
                                            height:180px;
                                            display:flex;
                                            align-items:flex-end;
                                            justify-content:center;
                                        "
                                    >

                                        <div
                                            style="
                                                width:100%;
                                                height:{{ $height }}%;
                                                min-height:{{ $income > 0 ? '6px' : '3px' }};
                                                border-radius:8px 8px 4px 4px;
                                                background:var(--admin-dark);
                                                opacity:{{ $income > 0 ? '1' : '.15' }};
                                                "
                                            title="{{ number_format($income) }} تومان"
                                        ></div>

                                    </div>


                                    <span
                                        style="
                                            color:var(--admin-muted);
                                            font-size:8px;
                                            white-space:nowrap;
                                        "
                                    >
                                        {{ $day['label'] ?? '—' }}
                                    </span>


                                    <span
                                        style="
                                            color:var(--admin-text);
                                            font-size:8px;
                                            font-weight:800;
                                        "
                                    >
                                        {{ number_format((int) ($day['orders'] ?? 0)) }}
                                    </span>

                                </div>

                            @endforeach

                        </div>


                        <div
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                                gap:10px;
                                margin-top:12px;
                                padding-top:12px;
                                border-top:1px solid var(--admin-border);
                            "
                        >

                            <span class="admin-muted">
                                سفارش در روز
                            </span>

                            <span class="admin-muted">
                                مبلغ به میلیون تومان
                            </span>

                        </div>

                    @else

                        <div class="admin-empty">

                            <div class="admin-empty__icon">
                                ∿
                            </div>

                            <h3 class="admin-empty__title">
                                هنوز داده‌ای وجود ندارد
                            </h3>

                            <p class="admin-empty__text">
                                با ثبت سفارش، اطلاعات فروش این بخش نمایش داده می‌شود.
                            </p>

                        </div>

                    @endif

                </div>

            </section>


            {{-- =====================================================
                 ORDER STATUS
            ====================================================== --}}

            <section class="admin-card">

                <header class="admin-card-header">

                    <div>

                        <h2 class="admin-card-title">
                            وضعیت سفارش‌ها
                        </h2>

                        <p class="admin-card-description">
                            تعداد سفارش‌ها در بازه انتخاب‌شده
                        </p>

                    </div>

                </header>


                <div class="admin-status-list">

                    @foreach($statusNames as $status => $name)

                        @php
                            $count = (int) (
                                $orderBreakdown[$status] ?? 0
                            );

                            $class = $statusClasses[$status] ?? 'neutral';
                        @endphp


                        <div class="admin-status-row">

                            <span class="admin-status-row__label">
                                {{ $name }}
                            </span>

                            <strong class="admin-status-row__value">
                                {{ number_format($count) }}
                            </strong>

                        </div>

                    @endforeach

                </div>

            </section>

        </div>


        {{-- =========================================================
             INVENTORY
        ========================================================== --}}

        <div class="admin-dashboard-grid admin-dashboard-grid--equal">


            {{-- LOW STOCK --}}

            <section class="admin-card">

                <header class="admin-card-header">

                    <div>

                        <h2 class="admin-card-title">
                            موجودی کم
                        </h2>

                        <p class="admin-card-description">
                            {{ number_format((int) $lowStock) }}
                            تنوع محصول نیاز به بررسی دارد.
                        </p>

                    </div>


                    <a
                        href="{{ route('admin.inventory.index') }}"
                        class="admin-btn admin-btn--ghost admin-btn--sm"
                    >
                        مشاهده همه
                    </a>

                </header>


                <div class="admin-inventory-list">

                    @if($lowStockVariants->isNotEmpty())

                        @foreach($lowStockVariants as $variant)

                            <div class="admin-stock-row">

                                <div class="admin-stock-info">

                                    <div class="admin-stock-name">
                                        {{ $variant->product?->name ?? 'محصول' }}
                                    </div>

                                    <div class="admin-stock-meta">

                                        SKU:

                                        <span dir="ltr">
                                            {{ $variant->sku ?: '—' }}
                                        </span>

                                    </div>

                                </div>


                                <div class="admin-stock-bar">

                                    @php
                                        $threshold =
                                            max(
                                                (int) ($variant->low_stock_threshold ?? 5),
                                                1
                                            );

                                        $stockPercent =
                                            min(
                                                100,
                                                max(
                                                    0,
                                                    (
                                                        (int) $variant->stock
                                                        /
                                                        $threshold
                                                    ) * 100
                                                )
                                            );
                                    @endphp

                                    <span
                                        style="width:{{ $stockPercent }}%"
                                    ></span>

                                </div>


                                <div class="admin-stock-count">

                                    <strong>
                                        {{ number_format((int) $variant->stock) }}
                                    </strong>

                                </div>

                            </div>

                        @endforeach

                    @else

                        <div class="admin-empty admin-empty--compact">

                            <div class="admin-empty__icon">
                                ✓
                            </div>

                            <h3 class="admin-empty__title">
                                موجودی وضعیت خوبی دارد
                            </h3>

                            <p class="admin-empty__text">
                                محصولی در محدوده هشدار موجودی نیست.
                            </p>

                        </div>

                    @endif

                </div>

            </section>


            {{-- INVENTORY VALUE --}}

            <section class="admin-card">

                <header class="admin-card-header">

                    <div>

                        <h2 class="admin-card-title">
                            خلاصه موجودی
                        </h2>

                        <p class="admin-card-description">
                            ارزش فعلی موجودی فعال فروشگاه
                        </p>

                    </div>


                    <a
                        href="{{ route('admin.inventory.index') }}"
                        class="admin-btn admin-btn--secondary admin-btn--sm"
                    >
                        مدیریت موجودی
                    </a>

                </header>


                <div class="admin-card-body">

                    <div
                        style="
                            display:flex;
                            flex-direction:column;
                            gap:18px;
                        "
                    >

                        <div>

                            <div class="admin-muted">
                                ارزش فعلی موجودی
                            </div>

                            <div
                                style="
                                    margin-top:5px;
                                    color:var(--admin-text);
                                    font-size:24px;
                                    font-weight:850;
                                    line-height:1.4;
                                "
                            >
                                {{ number_format((float) $inventoryValue) }}

                                <span
                                    style="
                                        color:var(--admin-muted);
                                        font-size:9px;
                                        font-weight:500;
                                    "
                                >
                                    تومان
                                </span>
                            </div>

                        </div>


                        <div class="admin-divider"></div>


                        <div
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                                gap:12px;
                            "
                        >

                            <span class="admin-muted">
                                تنوع کم‌موجودی
                            </span>

                            <strong
                                style="
                                    color:var(--admin-text);
                                    font-size:16px;
                                "
                            >
                                {{ number_format((int) $lowStock) }}
                            </strong>

                        </div>

                    </div>

                </div>

            </section>

        </div>


        {{-- =========================================================
             RECENT ORDERS
        ========================================================== --}}

        <section class="admin-card">

            <header class="admin-card-header">

                <div>

                    <h2 class="admin-card-title">
                        آخرین سفارش‌ها
                    </h2>

                    <p class="admin-card-description">
                        جدیدترین سفارش‌های ثبت‌شده در فروشگاه
                    </p>

                </div>


                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-btn admin-btn--secondary admin-btn--sm"
                >
                    همه سفارش‌ها
                </a>

            </header>


            @if($recentOrders->isNotEmpty())

                <div class="admin-table-wrap">

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
                                عملیات
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        @foreach($recentOrders as $order)

                            @php
                                $customerName =
                                    $order->user?->name
                                    ?? $order->customer_name
                                    ?? 'مشتری';

                                $customerPhone =
                                    $order->user?->phone
                                    ?? $order->customer_phone
                                    ?? null;
                            @endphp


                            <tr>

                                {{-- ORDER --}}

                                <td>

                                    <strong>
                                        {{ $order->order_number }}
                                    </strong>

                                </td>


                                {{-- CUSTOMER --}}

                                <td>

                                    <div>

                                        <strong>
                                            {{ $customerName }}
                                        </strong>

                                        @if($customerPhone)

                                            <div class="admin-product-meta">
                                                {{ $customerPhone }}
                                            </div>

                                        @endif

                                    </div>

                                </td>


                                {{-- TOTAL --}}

                                <td>

                                    <strong>
                                        {{ number_format((float) $order->total) }}
                                    </strong>

                                    <span class="admin-muted">
                                            تومان
                                        </span>

                                </td>


                                {{-- STATUS --}}

                                <td>

                                        <span
                                            class="admin-badge admin-badge--{{ $statusClasses[$order->status] ?? 'neutral' }}"
                                        >
                                            {{ $statusNames[$order->status] ?? $order->status }}
                                        </span>

                                </td>


                                {{-- PAYMENT --}}

                                <td>

                                        <span
                                            class="admin-badge admin-badge--{{ $paymentClasses[$order->payment_status] ?? 'neutral' }}"
                                        >

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


                                {{-- DATE --}}

                                <td>

                                        <span class="admin-muted">
                                            {{ optional($order->placed_at)->format('Y/m/d H:i') }}
                                        </span>

                                </td>


                                {{-- ACTION --}}

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

                    <h3 class="admin-empty__title">
                        هنوز سفارشی ثبت نشده
                    </h3>

                    <p class="admin-empty__text">
                        آخرین سفارش‌های فروشگاه پس از ثبت در این بخش نمایش داده می‌شوند.
                    </p>

                </div>

            @endif

        </section>


        {{-- =========================================================
             TOP PRODUCTS
        ========================================================== --}}

        <section class="admin-card">

            <header class="admin-card-header">

                <div>

                    <h2 class="admin-card-title">
                        پرفروش‌ترین محصولات
                    </h2>

                    <p class="admin-card-description">
                        بر اساس تعداد اقلام فروخته‌شده
                    </p>

                </div>


                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-btn admin-btn--secondary admin-btn--sm"
                >
                    مدیریت محصولات
                </a>

            </header>


            @if($topProducts->isNotEmpty())

                <div class="admin-top-products">

                    @foreach($topProducts as $index => $product)

                        @php
                            $salesQuantity =
                                $product->sales_quantity
                                ?? $product->order_items_sum_quantity
                                ?? 0;

                            $productImage =
                                $product->galleryMedia?->first();
                        @endphp


                        <article class="admin-top-product">

                            <div class="admin-top-product__rank">
                                {{ $index + 1 }}
                            </div>


                            @if($productImage?->url)

                                <img
                                    src="{{ $productImage->url }}"
                                    alt="{{ $product->name }}"
                                    class="admin-top-product__image"
                                    loading="lazy"
                                >

                            @else

                                <div class="admin-top-product__image">
                                    —
                                </div>

                            @endif


                            <div class="admin-top-product__content">

                                <div class="admin-top-product__name">
                                    {{ $product->name }}
                                </div>

                                <div class="admin-top-product__meta">
                                    {{ $product->category?->name ?? 'بدون دسته‌بندی' }}
                                </div>

                            </div>


                            <div class="admin-top-product__value">

                                {{ number_format((int) $salesQuantity) }}

                                <div class="admin-top-product__meta">
                                    عدد
                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="admin-empty">

                    <div class="admin-empty__icon">
                        ★
                    </div>

                    <h3 class="admin-empty__title">
                        هنوز داده فروش وجود ندارد
                    </h3>

                    <p class="admin-empty__text">
                        بعد از ثبت سفارش‌های واقعی، محصولات پرفروش اینجا نمایش داده می‌شوند.
                    </p>

                </div>

            @endif

        </section>

    </div>

@endsection
