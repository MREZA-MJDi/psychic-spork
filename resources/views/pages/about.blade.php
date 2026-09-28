@extends('layouts.store')

@section('content')
<div class="store-page about-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / ABOUT</span>
                <h1>
                    انتخاب خوب،
                    <br>
                    <em>از شناخت شروع می‌شود.</em>
                </h1>
                <p>
                    جانان یک فروشگاه آنلاین برای انتخاب آگاهانه‌تر است؛
                    محصول را واضح می‌بینی، اطلاعاتش را مقایسه می‌کنی و بدون پیچیدگی به خرید می‌رسی.
                </p>

                <div class="store-page__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">
                        مشاهده محصولات
                        <span aria-hidden="true">↗</span>
                    </a>

                    <a class="button button--ghost" href="{{ route('contact') }}">
                        با ما صحبت کن
                    </a>
                </div>
            </div>

            <div class="about-page__mark" aria-hidden="true">
                <span>J</span>
                <small>THE HOUSE<br>OF JANAN</small>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">

            <div class="about-story">
                <div class="about-story__lead">
                    <span class="eyebrow">A CLEARER WAY TO SHOP</span>
                    <h2>
                        قرار نیست برای پیدا کردن یک محصول خوب،
                        بین صفحه‌های شلوغ گم شوی.
                    </h2>
                </div>

                <div class="about-story__copy">
                    <p>
                        جانان با یک ایده ساده ساخته شده: تجربه خرید باید سریع، قابل فهم و قابل اعتماد باشد.
                        برای همین ساختار فروشگاه حول سه چیز می‌چرخد؛
                        ارائه روشن اطلاعات، مسیر ساده انتخاب و اتصال مستقیم به داده‌های واقعی فروشگاه.
                    </p>

                    <p>
                        از محصول و دسته‌بندی تا برند، سبد خرید و پرداخت، هر بخش بخشی از یک مسیر واحد است؛
                        نه چند صفحه جدا از هم.
                    </p>
                </div>
            </div>

            <div class="about-stat-grid" aria-label="آمار فعلی فروشگاه">
                <div class="about-stat-card">
                    <small>ACTIVE PRODUCTS</small>
                    <strong>{{ number_format($aboutStats['products'] ?? 0) }}</strong>
                    <span>محصول فعال در کاتالوگ</span>
                </div>

                <div class="about-stat-card">
                    <small>CATEGORIES</small>
                    <strong>{{ number_format($aboutStats['categories'] ?? 0) }}</strong>
                    <span>دسته‌بندی فعال</span>
                </div>

                <div class="about-stat-card">
                    <small>BRANDS</small>
                    <strong>{{ number_format($aboutStats['brands'] ?? 0) }}</strong>
                    <span>برند فعال</span>
                </div>

                <div class="about-stat-card about-stat-card--dark">
                    <small>JANAN / LIVE DATA</small>
                    <strong>01</strong>
                    <span>یک مسیر یکپارچه از کشف تا سفارش</span>
                </div>
            </div>
        </div>
    </section>

    <section class="about-principles">
        <div class="container">
            <div class="about-principles__head">
                <div>
                    <span class="eyebrow">WHAT MATTERS</span>
                    <h2>چیزی که پشت ظاهر فروشگاه قرار دارد.</h2>
                </div>

                <p>
                    طراحی فقط زمانی ارزش دارد که استفاده از فروشگاه را بهتر کند.
                    به همین دلیل، هر تصمیم بصری جانان باید به یک تصمیم کاربردی هم وصل باشد.
                </p>
            </div>

            <div class="about-principles__grid">
                <article class="about-principle">
                    <span>01</span>
                    <div>
                        <h3>شفافیت</h3>
                        <p>
                            نام، قیمت، موجودی، مشخصات و مسیر خرید باید همان‌جایی دیده شوند که کاربر به آن‌ها نیاز دارد.
                        </p>
                    </div>
                </article>

                <article class="about-principle">
                    <span>02</span>
                    <div>
                        <h3>سادگی</h3>
                        <p>
                            کم کردن مراحل اضافه، پیدا کردن محصول را سریع‌تر می‌کند و تصمیم‌گیری را سبک‌تر نگه می‌دارد.
                        </p>
                    </div>
                </article>

                <article class="about-principle">
                    <span>03</span>
                    <div>
                        <h3>جزئیات</h3>
                        <p>
                            فاصله‌ها، تایپوگرافی، حالت‌های تعاملی و بازخوردهای کوچک بخشی از خود محصول دیجیتال هستند.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="store-page__section store-page__section--soft">
        <div class="container">

            <div class="about-live">
                <div class="about-live__copy">
                    <span class="eyebrow">JANAN / LIVE CATALOG</span>
                    <h2>
                        محتوای فروشگاه از داده واقعی می‌آید،
                        نه متن نمایشی.
                    </h2>
                    <p>
                        موجودی و ساختار کاتالوگ از Backend خوانده می‌شوند تا صفحه درباره ما هم بخشی از همان تجربه واقعی فروشگاه باشد.
                    </p>

                    <div class="store-page__actions">
                        <a class="button button--dark" href="{{ route('products.index') }}">
                            رفتن به کاتالوگ
                            <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                </div>

                <div class="about-live__product">
                    @if($latestProduct)
                        <span class="about-live__product-label">LATEST ACTIVE PRODUCT</span>
                        <strong>{{ $latestProduct->name }}</strong>

                        <div class="about-live__meta">
                            <span>{{ $latestProduct->category?->name ?? 'بدون دسته‌بندی' }}</span>
                            <span>{{ $latestProduct->brand?->name ?? 'بدون برند' }}</span>
                        </div>

                        <a href="{{ route('products.show', $latestProduct) }}" class="text-link">
                            مشاهده محصول
                            <span aria-hidden="true">↗</span>
                        </a>
                    @else
                        <span class="about-live__product-label">CATALOG STATUS</span>
                        <strong>هنوز محصول فعالی ثبت نشده است.</strong>
                        <p>به‌محض اضافه شدن محصول، اطلاعات واقعی این بخش هم نمایش داده می‌شود.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">
            <div class="page-cta-panel about-page__cta">
                <div>
                    <span class="eyebrow">START HERE</span>
                    <h2>از کشف شروع کن.</h2>
                    <p>
                        محصولی که دنبالش هستی را پیدا کن یا مستقیم با تیم جانان در ارتباط باش.
                    </p>
                </div>

                <div class="store-page__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">کشف محصولات</a>
                    <a class="button button--ghost" href="{{ route('contact') }}">تماس با ما</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
