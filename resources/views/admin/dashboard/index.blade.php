@extends('layouts.admin')

@section('title', 'داشبورد جانان')
@section('page-title', 'داشبورد')

@push('styles')
    @vite('resources/css/admin-dashboard.css')
@endpush

@section('content')
@php
    $number = [\App\Support\PersianNumber::class, 'format'];
    $queues = [
        ['سفارش‌های در جریان', $pendingOrders, route('admin.orders.index'), 'در انتظار، تأییدشده یا در حال آماده‌سازی'],
        ['موجودی در محدودهٔ هشدار', $lowStock, route('admin.inventory.index'), 'کدهای فعال کالا'],
        ['پیام‌های خوانده‌نشده', $unreadContactMessages, route('admin.contact.index'), 'پیام‌های پشتیبانی'],
        ['چک‌های نیازمند پیگیری', $chequesAwaitingReview, route('admin.cheques.index'), $number($chequesAwaitingReviewAmount) . ' تومان در مرحلهٔ بررسی'],
        ['درخواست‌های خرید عمده', $pendingWholesaleApplications, route('admin.wholesale.index', ['status' => 'pending']), 'در انتظار تصمیم'],
        ['درخواست‌های خرید چکی', $pendingChequePermissions ?? 0, route('admin.cheques.index', ['tab' => 'requests']), 'در انتظار تأیید مدیر'],
    ];
    $qualityItems = [
        ['محصول فعال بدون عکس', $productsMissingImage, route('admin.products.index', ['quality' => 'missing-image'])],
        ['محصول فعال با سئوی ناقص', $productsMissingSeo, route('admin.products.index', ['quality' => 'missing-seo'])],
        ['دستهٔ فعال با سئوی ناقص', $categoriesMissingSeo, route('admin.categories.index')],
        ['برند فعال با سئوی ناقص', $brandsMissingSeo, route('admin.brands.index')],
    ];
    $paymentNames = ['paid' => 'پرداخت‌شده', 'pending' => 'در انتظار', 'failed' => 'ناموفق', 'refunded' => 'برگشت‌خورده'];
    $hasSales = $daily->contains(fn ($day) => (float) $day['income'] !== 0.0);
@endphp

<div class="janan-overview">
    <header class="janan-overview__intro">
        <div>
            <h1>نمای کلی فروشگاه</h1>
            <p>سفارش‌ها، موجودی و کارهای نیازمند پیگیری جانان</p>
        </div>
        <div class="janan-overview__actions">
            <a class="janan-overview__button" href="{{ route('admin.nila.index') }}">ورود فایل هلو</a>
            <a class="janan-overview__button janan-overview__button--primary" href="{{ route('admin.products.create') }}">افزودن محصول</a>
        </div>
    </header>

    <div class="janan-overview__work">
        <section class="janan-overview__card janan-overview__queues" aria-labelledby="work-title">
            <header class="janan-overview__section-head">
                <h2 id="work-title">نیازمند پیگیری</h2>
                <p>وضعیت فعلی فروشگاه</p>
            </header>
            <ul class="janan-overview__queue-list">
                @foreach($queues as [$label, $value, $url, $description])
                    <li>
                        <a href="{{ $url }}">
                            <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
                            <b @class(['janan-overview__count', 'janan-overview__count--attention' => $value > 0])>{{ $number($value) }}</b>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="janan-overview__card janan-overview__orders" aria-labelledby="orders-title">
            <header class="janan-overview__section-head">
                <div><h2 id="orders-title">آخرین سفارش‌ها</h2><p>بر اساس زمان ثبت</p></div>
                <a href="{{ route('admin.orders.index') }}">همهٔ سفارش‌ها</a>
            </header>
            @if($recentOrders->isEmpty())
                <div class="janan-overview__empty">هنوز سفارشی ثبت نشده است.</div>
            @else
                <ul class="janan-overview__order-list">
                    @foreach($recentOrders as $order)
                        <li>
                            <a href="{{ route('admin.orders.show', $order) }}">
                                <span>
                                    <strong>{{ $order->user?->name ?? $order->customer_name ?? 'مشتری' }}</strong>
                                    <small>سفارش <bdi>{{ \App\Support\PersianNumber::digits($order->order_number) }}</bdi></small>
                                    @if($order->placed_at)
                                        <small><time datetime="{{ $order->placed_at->toIso8601String() }}" data-admin-date="{{ $order->placed_at->toIso8601String() }}">{{ $order->placed_at->format('Y/m/d H:i') }}</time></small>
                                    @endif
                                </span>
                                <span class="janan-overview__order-status">
                                    <strong>{{ $statusNames[$order->status] ?? $order->status }}</strong>
                                    <small>{{ $paymentNames[$order->payment_status] ?? $order->payment_status }}</small>
                                </span>
                                <span class="janan-overview__order-amount"><strong>{{ $number($order->total) }}</strong><small>تومان</small></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="janan-overview__card janan-overview__report" aria-labelledby="sales-title">
        <header class="janan-overview__section-head">
            <div><h2 id="sales-title">فروش و دریافت‌ها</h2><p>فروش از دفتر کل و سفارش‌ها از تاریخ ثبت محاسبه می‌شوند.</p></div>
            <form class="janan-overview__period" action="{{ route('admin.dashboard') }}" method="GET">
                <label for="dashboard-period">بازهٔ گزارش</label>
                <select id="dashboard-period" name="period">
                    @foreach(collect([7, 30, 60, 90, (int) $period])->unique()->sort() as $days)
                        <option value="{{ $days }}" @selected((int) $period === $days)>{{ $number($days) }} روز اخیر</option>
                    @endforeach
                </select>
                <button class="janan-overview__button" type="submit">نمایش</button>
            </form>
        </header>
        <dl class="janan-overview__metrics">
            <div><dt>خالص فروش</dt><dd>{{ $number($revenue) }} <small>تومان</small></dd><small>پس از کسر مرجوعی‌ها</small></div>
            <div><dt>خالص جریان نقدی</dt><dd>{{ $number($netCash) }} <small>تومان</small></dd><small>ورودی منهای خروجی نقد و بانک و هزینه‌های ثبت‌شده</small></div>
            <div><dt>سفارش‌های ثبت‌شده</dt><dd>{{ $number($ordersCount) }}</dd><small>{{ $number($paidOrdersCount) }} سفارش پرداخت‌شده</small></div>
        </dl>

        @if($hasSales)
            <figure class="janan-overview__chart">
                <figcaption>خالص فروش روزانه <span>ثبت فروش ↑ · کاهش خالص ↓</span></figcaption>
                <div class="janan-overview__plot" aria-hidden="true">
                    @foreach($daily as $day)
                        @php
                            $height = abs($day['income']) / max($maxIncome, 1) * 100;
                        @endphp
                        <div class="janan-overview__plot-day">
                            <div @class(['janan-overview__plot-bar', 'janan-overview__plot-bar--negative' => $day['income'] < 0]) style="--bar-height: {{ $height }}%"></div>
                        </div>
                    @endforeach
                </div>
                <div class="janan-overview__chart-dates" dir="ltr" aria-hidden="true">
                    <time data-admin-date="{{ $daily->first()['date'] }}">{{ $daily->first()['label'] }}</time>
                    <time data-admin-date="{{ $daily->last()['date'] }}">{{ $daily->last()['label'] }}</time>
                </div>
            </figure>
        @else
            <p class="janan-overview__empty">در این بازه خالص فروش روزانه‌ای برای نمایش وجود ندارد.</p>
        @endif

        <details class="janan-overview__details">
            <summary>جدول فروش روزانه و جزئیات بازه</summary>
            <dl class="janan-overview__facts">
                <div><dt>هزینهٔ ثبت‌شده</dt><dd>{{ $number($expenses) }} تومان</dd></div>
                <div><dt>مرجوعی ثبت‌شده در دفتر کل</dt><dd>{{ $number($salesReturns) }} تومان</dd></div>
                <div><dt>خالص گردش نقد و بانک</dt><dd>{{ $number($settledFunds) }} تومان</dd></div>
                @foreach($statusNames as $status => $label)
                    <div><dt>{{ $label }}</dt><dd>{{ $number($orderBreakdown[$status] ?? 0) }} سفارش</dd></div>
                @endforeach
            </dl>
            <table class="janan-overview__table">
                <thead><tr><th scope="col">روز</th><th scope="col">خالص فروش (تومان)</th><th scope="col">سفارش</th></tr></thead>
                <tbody>
                    @foreach($daily as $day)
                        <tr><td><time data-admin-date="{{ $day['date'] }}">{{ $day['label'] }}</time></td><td>{{ $number($day['income']) }}</td><td>{{ $number($day['orders']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    </section>

    <div class="janan-overview__work janan-overview__work--catalog">
        <section class="janan-overview__card" aria-labelledby="catalog-title">
            <header class="janan-overview__section-head">
                <div><h2 id="catalog-title">محصولات و گزینه‌ها</h2><p>هر مدل می‌تواند چند گزینهٔ سایز یا رنگ داشته باشد.</p></div>
                <a href="{{ route('admin.products.index') }}">فهرست محصولات</a>
            </header>
            <dl class="janan-overview__facts">
                <div><dt>مدل ثبت‌شده</dt><dd>{{ $number($catalogProducts) }}</dd></div>
                <div><dt>گزینهٔ فعال</dt><dd>{{ $number($catalogVariants) }}</dd></div>
                <div><dt>گزینه با قیمت عمده</dt><dd>{{ $number($catalogWholesalePricedVariants) }}</dd></div>
                <div><dt>دسته‌بندی</dt><dd>{{ $number($catalogCategories) }}</dd></div>
                <div><dt>برند</dt><dd>{{ $number($catalogBrands) }}</dd></div>
                <div><dt>حساب مشتری</dt><dd>{{ $number($customers) }}</dd></div>
            </dl>
            <div class="janan-overview__catalog-link">
                <a href="{{ route('admin.nila.index') }}">بررسی فایل هلو و تفاوت ردیف‌ها با مدل‌ها</a>
            </div>
        </section>

        <section class="janan-overview__card" aria-labelledby="content-title">
            <header class="janan-overview__section-head">
                <div><h2 id="content-title">آمادگی صفحه‌های فروشگاه</h2><p>تصویر، عنوان و توضیح برای جستجو</p></div>
            </header>
            <ul class="janan-overview__queue-list">
                @foreach($qualityItems as [$label, $value, $url])
                    <li>
                        <a href="{{ $url }}">
                            <strong>{{ $label }}</strong><b @class(['janan-overview__count', 'janan-overview__count--attention' => $value > 0])>{{ $number($value) }}</b>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <div class="janan-overview__work janan-overview__work--catalog">
        <section class="janan-overview__card" aria-labelledby="stock-title">
            <header class="janan-overview__section-head">
                <div><h2 id="stock-title">کمترین موجودی</h2><p>ارزش برآوردی موجودی به قیمت فروش {{ $number($inventoryValue) }} تومان</p></div>
                <a href="{{ route('admin.inventory.index') }}">انبار</a>
            </header>
            <ul class="janan-overview__compact-list">
                @forelse($lowStockVariants as $variant)
                    <li><span><strong>{{ $variant->product?->name ?? 'محصول' }}</strong><small><bdi>{{ $variant->sku }}</bdi></small></span><b>{{ $number($variant->stock) }} عدد</b></li>
                @empty
                    <li class="janan-overview__empty">هیچ کد فعالی در محدودهٔ هشدار نیست.</li>
                @endforelse
            </ul>
        </section>
        <section class="janan-overview__card" aria-labelledby="top-title">
            <header class="janan-overview__section-head">
                <div><h2 id="top-title">پرفروش‌های بازه</h2><p>بر اساس تعداد فروش در سفارش‌های پرداخت‌شده</p></div>
            </header>
            <ul class="janan-overview__compact-list">
                @forelse($topProducts as $product)
                    <li><a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a><b>{{ $number($product->sales_quantity) }} عدد</b></li>
                @empty
                    <li class="janan-overview__empty">در این بازه محصولی شرایط گزارش پرفروش‌ها را ندارد.</li>
                @endforelse
            </ul>
        </section>
    </div>
    <section class="janan-overview__card" aria-labelledby="recent-products-title">
        <header class="janan-overview__section-head">
            <div><h2 id="recent-products-title">آخرین مدل‌های ثبت‌شده</h2><p>مدل‌های تازه و وضعیت نمایش آن‌ها در فروشگاه</p></div>
            <a href="{{ route('admin.products.index') }}">همهٔ محصولات</a>
        </header>
        <ul class="janan-overview__compact-list">
            @forelse($recentProducts as $product)
                <li>
                    <div>
                        <a href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a>
                        @if($product->category)<small>{{ $product->category->name }}</small>@endif
                    </div>
                    <b>{{ $product->is_active ? 'فعال' : 'غیرفعال' }}</b>
                </li>
            @empty
                <li class="janan-overview__empty">هنوز مدلی ثبت نشده است.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
