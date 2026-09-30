@extends('layouts.admin')

@section('title', 'داشبورد مدیریت')
@section('page-title', 'داشبورد')

@section('content')

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

        $paymentClasses = [
            'paid' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            'refunded' => 'neutral',
        ];

        $paymentRate = $ordersCount > 0
            ? round(($paidOrdersCount / $ordersCount) * 100)
            : 0;

        $processingCount = (int) (
            ($orderBreakdown['pending'] ?? 0)
            + ($orderBreakdown['confirmed'] ?? 0)
            + ($orderBreakdown['preparing'] ?? 0)
        );

        $chartMax = max((float) $maxIncome, 1);
    @endphp

    <div class="dashboard-v2">

        <section class="dashboard-v2__hero">
            <div>
                <span class="dashboard-v2__eyebrow">JANAN / CONTROL CENTER</span>

                <h1>
                    نمای کلی فروشگاه،
                    <br>
                    <em>در یک نگاه.</em>
                </h1>

                <p>
                    فروش، سفارش، موجودی و عملکرد فعلی را از یک نقطه کنترل کن.
                    اعداد این صفحه مستقیماً از داده‌های واقعی فروشگاه خوانده می‌شوند.
                </p>
            </div>

            <div class="dashboard-v2__hero-actions">
                <div class="dashboard-v2__live">
                    <i aria-hidden="true"></i>
                    <span>سیستم فعال</span>
                </div>

                <form
                    method="GET"
                    action="{{ route('admin.dashboard') }}"
                    class="dashboard-v2__period"
                >
                    <label for="dashboard-period">بازه گزارش</label>
                    <select id="dashboard-period" name="period" onchange="this.form.submit()">
                        @foreach([
                            7 => '۷ روز اخیر',
                            30 => '۳۰ روز اخیر',
                            60 => '۶۰ روز اخیر',
                            90 => '۹۰ روز اخیر',
                        ] as $days => $label)
                            <option value="{{ $days }}" @selected((int) $period === $days)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </section>

        <section class="dashboard-v2__stats" aria-label="شاخص‌های اصلی">
            <article class="dashboard-v2__stat dashboard-v2__stat--dark">
                <span>REVENUE</span>
                <strong>{{ number_format((float) $revenue) }}</strong>
                <small>تومان درآمد پرداخت‌شده</small>
                <i>01</i>
            </article>

            <article class="dashboard-v2__stat">
                <span>NET CASH</span>
                <strong>{{ number_format((float) $netCash) }}</strong>
                <small>خالص جریان نقدی</small>
                <i>02</i>
            </article>

            <article class="dashboard-v2__stat">
                <span>ORDERS</span>
                <strong>{{ number_format((int) $ordersCount) }}</strong>
                <small>{{ number_format((int) $paidOrdersCount) }} پرداخت‌شده</small>
                <i>03</i>
            </article>

            <article class="dashboard-v2__stat dashboard-v2__stat--accent">
                <span>PROCESSING</span>
                <strong>{{ number_format($processingCount) }}</strong>
                <small>{{ number_format((int) $pendingOrders) }} سفارش جاری</small>
                <i>04</i>
            </article>

            <article class="dashboard-v2__stat">
                <span>CUSTOMERS</span>
                <strong>{{ number_format((int) $customers) }}</strong>
                <small>حساب مشتری</small>
                <i>05</i>
            </article>

            <article class="dashboard-v2__stat">
                <span>LOW STOCK</span>
                <strong>{{ number_format((int) $lowStock) }}</strong>
                <small>تنوع در محدوده هشدار</small>
                <i>06</i>
            </article>

            <article class="dashboard-v2__stat dashboard-v2__stat--accent">
                <span>CONTACT INBOX</span>
                <strong>{{ number_format((int) ($unreadContactMessages ?? 0)) }}</strong>
                <small>پیام خوانده‌نشده</small>
                <i>07</i>
            </article>
        </section>

        <section class="dashboard-v2__control-center" aria-label="مرکز اقدام مدیریت">
            <div class="dashboard-v2__control-head">
                <div>
                    <span class="dashboard-v2__kicker">ACTION CENTER</span>
                    <h2>کارهایی که الان باید کنترل شوند</h2>
                    <p>این بخش فقط وضعیت‌هایی را نشان می‌دهد که واقعاً نیاز به تصمیم یا اقدام مدیریتی دارند.</p>
                </div>
            </div>

            <div class="dashboard-v2__control-grid">
                <a href="{{ route('admin.cheques.index', ['status' => 'under_review']) }}" class="dashboard-v2__control-item {{ $chequesAwaitingReview > 0 ? 'is-attention' : '' }}">
                    <span class="dashboard-v2__control-icon">چک</span>
                    <div>
                        <strong>پرداخت‌های چکی</strong>
                        <small>{{ number_format((int) $chequesAwaitingReview) }} مورد نیازمند بررسی · {{ number_format((float) $chequesAwaitingReviewAmount) }} تومان</small>
                    </div>
                    <b aria-hidden="true">←</b>
                </a>

                <a href="{{ route('admin.wholesale.index', ['status' => 'pending']) }}" class="dashboard-v2__control-item {{ $pendingWholesaleApplications > 0 ? 'is-attention' : '' }}">
                    <span class="dashboard-v2__control-icon">عمده</span>
                    <div>
                        <strong>درخواست‌های عمده</strong>
                        <small>{{ number_format((int) $pendingWholesaleApplications) }} درخواست در انتظار تصمیم مدیریتی</small>
                    </div>
                    <b aria-hidden="true">←</b>
                </a>

                <a href="{{ route('admin.inventory.index') }}" class="dashboard-v2__control-item {{ $lowStock > 0 ? 'is-attention' : '' }}">
                    <span class="dashboard-v2__control-icon">انبار</span>
                    <div>
                        <strong>کنترل موجودی</strong>
                        <small>{{ number_format((int) $lowStock) }} واریانت در محدوده هشدار · ارزش موجودی {{ number_format((float) $inventoryValue) }} تومان</small>
                    </div>
                    <b aria-hidden="true">←</b>
                </a>

                <div class="dashboard-v2__control-item dashboard-v2__control-item--static">
                    <span class="dashboard-v2__control-icon">نیلا</span>
                    <div>
                        <strong>مرز داده نیلا</strong>
                        <small>{{ number_format((int) $nilaProductMappings) }} محصول و {{ number_format((int) $nilaVariantMappings) }} واریانت mapping شده‌اند؛ همگام‌سازی API تا زمان قرارداد واقعی عمداً ادعا نمی‌شود.</small>
                    </div>
                    <span class="dashboard-v2__control-state">Foundation</span>
                </div>
            </div>
        </section>

        <section class="dashboard-v2__grid dashboard-v2__grid--main">

            <article class="dashboard-v2__panel dashboard-v2__panel--chart">
                <header class="dashboard-v2__panel-head">
                    <div>
                        <span class="dashboard-v2__kicker">SALES SIGNAL</span>
                        <h2>روند فروش روزانه</h2>
                        <p>درآمد پرداخت‌شده و تعداد سفارش‌ها در {{ number_format($period) }} روز اخیر.</p>
                    </div>

                    <div class="dashboard-v2__mini-stat">
                        <strong>{{ number_format($paymentRate) }}%</strong>
                        <span>نرخ پرداخت</span>
                    </div>
                </header>

                <div class="dashboard-v2__chart-wrap">
                    <div class="dashboard-v2__chart" style="--chart-columns: {{ max(1, $daily->count()) }};">
                        @foreach($daily as $day)
                            @php
                                $income = (float) ($day['income'] ?? 0);
                                $height = $income > 0
                                    ? max(6, ($income / $chartMax) * 100)
                                    : 3;
                            @endphp

                            <div class="dashboard-v2__bar-column">
                                <div class="dashboard-v2__bar-value">
                                    {{ $income > 0 ? number_format($income / 1000000, 1) . 'M' : '—' }}
                                </div>

                                <div class="dashboard-v2__bar-track">
                                    <span
                                        style="height: {{ $height }}%"
                                        title="{{ number_format($income) }} تومان"
                                    ></span>
                                </div>

                                <small
                                    data-admin-date="{{ $day['date'] ?? '' }}"
                                    data-admin-date-format="day"
                                >
                                    {{ $day['label'] ?? '—' }}
                                </small>
                                <b class="dashboard-v2__bar-orders">
                                    {{ number_format((int) ($day['orders'] ?? 0)) }}
                                </b>
                            </div>
                        @endforeach
                    </div>
                </div>

                <footer class="dashboard-v2__chart-footer">
                    <span>مقیاس مبلغ: میلیون تومان</span>
                    <span>سفارش‌ها: عدد پایین هر ستون</span>
                </footer>
            </article>

            <aside class="dashboard-v2__panel">
                <header class="dashboard-v2__panel-head">
                    <div>
                        <span class="dashboard-v2__kicker">ORDER FLOW</span>
                        <h2>قیف سفارش</h2>
                        <p>تصویر سریع از وضعیت سفارش‌های بازه انتخابی.</p>
                    </div>
                </header>

                <div class="dashboard-v2__status-list">
                    @foreach($statusNames as $status => $name)
                        @php
                            $count = (int) ($orderBreakdown[$status] ?? 0);
                            $percent = $ordersCount > 0
                                ? round(($count / $ordersCount) * 100)
                                : 0;
                        @endphp

                        <div class="dashboard-v2__status">
                            <div>
                                <span>{{ $name }}</span>
                                <strong>{{ number_format($count) }}</strong>
                            </div>

                            <div class="dashboard-v2__status-track">
                                <i
                                    class="dashboard-v2__status-fill dashboard-v2__status-fill--{{ $statusClasses[$status] ?? 'neutral' }}"
                                    style="width: {{ $percent }}%"
                                ></i>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>

        </section>

        <section class="dashboard-v2__grid dashboard-v2__grid--triple">

            <article class="dashboard-v2__panel dashboard-v2__panel--dark">
                <header class="dashboard-v2__panel-head dashboard-v2__panel-head--dark">
                    <div>
                        <span class="dashboard-v2__kicker">CASH FLOW</span>
                        <h2>پول واردشده و هزینه</h2>
                    </div>
                </header>

                <div class="dashboard-v2__cash-list">
                    <div>
                        <span>درآمد پرداخت‌شده</span>
                        <strong>{{ number_format((float) $revenue) }} <small>تومان</small></strong>
                    </div>
                    <div>
                        <span>هزینه‌ها</span>
                        <strong>{{ number_format((float) $expenses) }} <small>تومان</small></strong>
                    </div>
                    <div class="dashboard-v2__cash-total">
                        <span>خالص</span>
                        <strong>{{ number_format((float) $netCash) }} <small>تومان</small></strong>
                    </div>
                </div>

                <a href="{{ route('admin.accounting.index') }}" class="dashboard-v2__panel-link">
                    رفتن به حسابداری
                    <span aria-hidden="true">↗</span>
                </a>
            </article>

            <article class="dashboard-v2__panel">
                <header class="dashboard-v2__panel-head">
                    <div>
                        <span class="dashboard-v2__kicker">INVENTORY ALERT</span>
                        <h2>موجودی کم</h2>
                        <p>اولویت‌های انبار که بهتر است بررسی شوند.</p>
                    </div>

                    <a href="{{ route('admin.inventory.index') }}" class="dashboard-v2__small-link">
                        همه
                        <span aria-hidden="true">↗</span>
                    </a>
                </header>

                <div class="dashboard-v2__inventory">
                    @forelse($lowStockVariants as $variant)
                        @php
                            $threshold = max((int) ($variant->low_stock_threshold ?? 5), 1);
                            $stockPercent = min(100, max(0, ((int) $variant->stock / $threshold) * 100));
                        @endphp

                        <div class="dashboard-v2__inventory-row">
                            <div>
                                <strong>{{ $variant->product?->name ?? 'محصول' }}</strong>
                                <span dir="ltr">{{ $variant->sku ?: '—' }}</span>
                            </div>

                            <div class="dashboard-v2__inventory-bar">
                                <i style="width: {{ $stockPercent }}%"></i>
                            </div>

                            <b>{{ number_format((int) $variant->stock) }}</b>
                        </div>
                    @empty
                        <div class="dashboard-v2__empty">
                            <strong>انبار در وضعیت مناسب است.</strong>
                            <span>هیچ تنوعی در محدوده هشدار نیست.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-v2__panel">
                <header class="dashboard-v2__panel-head">
                    <div>
                        <span class="dashboard-v2__kicker">TOP PRODUCTS</span>
                        <h2>پرفروش‌ها</h2>
                        <p>بر اساس تعداد اقلام فروخته‌شده در بازه.</p>
                    </div>

                    <a href="{{ route('admin.products.index') }}" class="dashboard-v2__small-link">
                        محصولات
                        <span aria-hidden="true">↗</span>
                    </a>
                </header>

                <div class="dashboard-v2__top-products">
                    @forelse($topProducts as $index => $product)
                        @php
                            $salesQuantity = (int) ($product->sales_quantity ?? 0);
                            $productImage = $product->galleryMedia?->first();
                        @endphp

                        <a
                            href="{{ route('admin.products.index', ['q' => $product->name]) }}"
                            class="dashboard-v2__top-product"
                        >
                            <span class="dashboard-v2__rank">{{ $index + 1 }}</span>

                            @if($productImage?->url)
                                <img
                                    src="{{ $productImage->url }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                >
                            @else
                                <span class="dashboard-v2__product-placeholder">—</span>
                            @endif

                            <div>
                                <strong>{{ $product->name }}</strong>
                                <small>{{ $product->category?->name ?? 'بدون دسته‌بندی' }}</small>
                            </div>

                            <b>{{ number_format($salesQuantity) }} <small>عدد</small></b>
                        </a>
                    @empty
                        <div class="dashboard-v2__empty">
                            <strong>هنوز داده فروش وجود ندارد.</strong>
                            <span>پس از ثبت سفارش‌های واقعی این بخش پر می‌شود.</span>
                        </div>
                    @endforelse
                </div>
            </article>

        </section>

        <section class="dashboard-v2__panel dashboard-v2__panel--orders">

            <header class="dashboard-v2__panel-head">
                <div>
                    <span class="dashboard-v2__kicker">RECENT ACTIVITY</span>
                    <h2>آخرین سفارش‌ها</h2>
                    <p>جدیدترین سفارش‌های واقعی فروشگاه.</p>
                </div>

                <a href="{{ route('admin.orders.index') }}" class="admin-btn admin-btn--secondary admin-btn--sm">
                    همه سفارش‌ها
                    <span aria-hidden="true">↗</span>
                </a>
            </header>

            @if($recentOrders->isNotEmpty())
                <div class="dashboard-v2__orders-mobile" aria-label="آخرین سفارش‌ها در موبایل">
                    @foreach($recentOrders as $order)
                        @php
                            $customerName = $order->user?->name ?? $order->customer_name ?? 'مشتری';
                        @endphp
                        <article class="dashboard-v2__order-card">
                            <div class="dashboard-v2__order-card-head">
                                <strong>{{ $order->order_number }}</strong>
                                <span class="admin-badge admin-badge--{{ $statusClasses[$order->status] ?? 'neutral' }}">{{ $statusNames[$order->status] ?? $order->status }}</span>
                            </div>
                            <div class="dashboard-v2__order-card-body">
                                <div><small>مشتری</small><strong>{{ $customerName }}</strong></div>
                                <div><small>مبلغ</small><strong>{{ number_format((float) $order->total) }} <em>تومان</em></strong></div>
                                <div><small>پرداخت</small><strong>{{ $order->payment_status === 'paid' ? 'پرداخت‌شده' : ($order->payment_status === 'pending' ? 'در انتظار' : $order->payment_status) }}</strong></div>
                                <a href="{{ route('admin.orders.show', $order) }}" class="dashboard-v2__order-card-link">جزئیات <span aria-hidden="true">←</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="dashboard-v2__orders-table">
                    <table>
                        <thead>
                        <tr>
                            <th>سفارش</th>
                            <th>مشتری</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>پرداخت</th>
                            <th>تاریخ</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($recentOrders as $order)
                            @php
                                $customerName = $order->user?->name ?? $order->customer_name ?? 'مشتری';
                                $customerPhone = $order->user?->phone ?? $order->customer_phone ?? null;
                            @endphp

                            <tr>
                                <td>
                                    <strong>{{ $order->order_number }}</strong>
                                </td>

                                <td>
                                    <div class="dashboard-v2__customer">
                                        <strong>{{ $customerName }}</strong>
                                        @if($customerPhone)
                                            <small dir="ltr">{{ $customerPhone }}</small>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <strong>{{ number_format((float) $order->total) }}</strong>
                                    <small class="dashboard-v2__muted">تومان</small>
                                </td>

                                <td>
                                    <span class="admin-badge admin-badge--{{ $statusClasses[$order->status] ?? 'neutral' }}">
                                        {{ $statusNames[$order->status] ?? $order->status }}
                                    </span>
                                </td>

                                <td>
                                    <span class="admin-badge admin-badge--{{ $paymentClasses[$order->payment_status] ?? 'neutral' }}">
                                        @switch($order->payment_status)
                                            @case('paid') پرداخت‌شده @break
                                            @case('pending') در انتظار پرداخت @break
                                            @case('failed') ناموفق @break
                                            @case('refunded') بازپرداخت‌شده @break
                                            @default {{ $order->payment_status }}
                                        @endswitch
                                    </span>
                                </td>

                                <td>
                                    <span class="dashboard-v2__muted">
                                        <span
                                            class="admin-local-date"
                                            data-admin-date="{{ optional($order->placed_at)->toIso8601String() }}"
                                        >
                                            {{ optional($order->placed_at)->format('Y/m/d H:i') }}
                                        </span>
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="dashboard-v2__order-link"
                                    >
                                        جزئیات
                                        <span aria-hidden="true">←</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="dashboard-v2__empty dashboard-v2__empty--large">
                    <strong>هنوز سفارشی ثبت نشده است.</strong>
                    <span>به‌محض ثبت سفارش، فعالیت‌های اخیر اینجا نمایش داده می‌شوند.</span>
                </div>
            @endif
        </section>

        <section class="dashboard-v2__footer-grid">
            <a href="{{ route('admin.products.create') }}" class="dashboard-v2__quick-action">
                <span>01</span>
                <strong>محصول جدید</strong>
                <small>افزودن محصول به کاتالوگ</small>
                <b aria-hidden="true">↗</b>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="dashboard-v2__quick-action">
                <span>02</span>
                <strong>بررسی سفارش‌ها</strong>
                <small>پیگیری وضعیت سفارش‌های جاری</small>
                <b aria-hidden="true">↗</b>
            </a>

            <a href="{{ route('admin.inventory.index') }}" class="dashboard-v2__quick-action">
                <span>03</span>
                <strong>کنترل موجودی</strong>
                <small>بررسی تنوع‌های کم‌موجودی</small>
                <b aria-hidden="true">↗</b>
            </a>

            <a href="{{ route('admin.accounting.index') }}" class="dashboard-v2__quick-action">
                <span>04</span>
                <strong>حسابداری</strong>
                <small>مشاهده جریان مالی فروشگاه</small>
                <b aria-hidden="true">↗</b>
            </a>

            <a href="{{ route('admin.content.about') }}" class="dashboard-v2__quick-action">
                <span>05</span>
                <strong>محتوای سایت</strong>
                <small>ویرایش متن‌های درباره ما</small>
                <b aria-hidden="true">↗</b>
            </a>

            <a href="{{ route('admin.contact.index') }}" class="dashboard-v2__quick-action">
                <span>06</span>
                <strong>پیام‌های تماس</strong>
                <small>پیگیری درخواست‌های مشتریان</small>
                <b aria-hidden="true">↗</b>
            </a>
        </section>

    </div>
@endsection
