@extends('layouts.store')

@section('content')
<div class="store-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / ABOUT</span>
                <h1>انتخابی که<br><em>شبیه خودت باشد.</em></h1>
                <p>
                    جانان برای تجربه‌ای روشن، آرام و دقیق ساخته شده؛
                    از دیدن محصول تا انتخاب و ثبت سفارش.
                </p>

                <div class="store-page__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">
                        کشف محصولات
                        <span aria-hidden="true">↗</span>
                    </a>

                    <a class="button button--ghost" href="{{ route('contact') }}">
                        ارتباط با جانان
                    </a>
                </div>
            </div>

            <div class="page-hero__stat">
                <b>01</b>
                <span>THE HOUSE / JANAN</span>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">

            <div class="store-page__intro">
                <span class="eyebrow">A QUIET POINT OF VIEW</span>

                <h2>
                    تجربه خرید باید ساده باشد؛
                    نه ساده‌انگارانه.
                </h2>

                <p>
                    ساختار جانان بر پایه انتخاب دقیق، ارائه روشن و مسیر خرید قابل فهم طراحی شده است.
                    اطلاعات محصول از داده‌های واقعی فروشگاه می‌آید و هر بخش برای کاهش اصطکاک در تصمیم‌گیری ساخته شده است.
                </p>
            </div>

            <div class="store-page__grid">

                <article class="store-page__card">
                    <span class="store-page__number">01</span>
                    <h3>انتخاب</h3>
                    <p>
                        محصولات، دسته‌بندی‌ها و برندها در یک ساختار مشخص قرار گرفته‌اند تا پیدا کردن گزینه مناسب سریع و قابل مقایسه باشد.
                    </p>
                </article>

                <article class="store-page__card">
                    <span class="store-page__number">02</span>
                    <h3>شفافیت</h3>
                    <p>
                        قیمت، موجودی، مشخصات و مسیر خرید تا حد امکان در همان صفحه‌ای که تصمیم می‌گیری در دسترس است.
                    </p>
                </article>

                <article class="store-page__card store-page__card--dark">
                    <span class="store-page__number">03</span>
                    <h3>تجربه</h3>
                    <p>
                        ظاهر لوکس فقط برای نمایش نیست؛ باید همراه با رفتار درست، سرعت مناسب و دسترسی واضح به خرید باشد.
                    </p>
                </article>

            </div>
        </div>
    </section>

    <section class="store-page__section store-page__section--soft">
        <div class="container">

            <div class="store-page__intro">
                <span class="eyebrow">JANAN / SYSTEM</span>
                <h2>فروشگاه واقعی، نه فقط یک ویترین.</h2>
                <p>
                    قیمت و موجودی از Backend می‌آیند و مسیرهای دسته‌بندی، برند، سبد خرید و پرداخت به همان داده‌ها متصل هستند.
                </p>
            </div>

            @if($latestProduct)
                <div class="store-page__grid store-page__grid--two">
                    <article class="store-page__card">
                        <span class="store-page__number">LIVE PRODUCT</span>
                        <h3>{{ $latestProduct->name }}</h3>
                        <p>
                            آخرین محصول در دسترس فروشگاه، با همان داده‌ای که در مسیر خرید استفاده می‌شود.
                        </p>
                        <div class="store-page__facts">
                            <div class="store-page__fact">
                                <strong>{{ $latestProduct->category?->name ?? 'Janan' }}</strong>
                                <span>دسته</span>
                            </div>
                            <div class="store-page__fact">
                                <strong>{{ $latestProduct->brand?->name ?? '—' }}</strong>
                                <span>برند</span>
                            </div>
                        </div>
                    </article>

                    <article class="store-page__card store-page__card--dark">
                        <span class="store-page__number">DISCOVER</span>
                        <h3>از یک انتخاب درست شروع کن.</h3>
                        <p>
                            وارد کالکشن شو، محصول‌ها را مقایسه کن و در صورت نیاز مستقیماً به سبد خرید اضافه کن.
                        </p>
                        <div class="store-page__actions">
                            <a class="button button--primary" href="{{ route('products.index') }}">
                                مشاهده کالکشن
                            </a>
                        </div>
                    </article>
                </div>
            @endif

        </div>
    </section>
</div>
@endsection
