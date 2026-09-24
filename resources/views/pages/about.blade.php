@extends('layouts.store')

@section('title', 'درباره جانان — Janan')

@section('content')

    <div class="about-page">

        {{-- =====================================================
             01. EDITORIAL HERO
        ====================================================== --}}

        <section class="about-hero">
            <div class="container about-hero__inner">

                <div class="about-hero__meta">
                <span class="eyebrow">
                    THE HOUSE OF {{ $siteBrandNameLatin ?? 'JANAN' }}
                </span>

                    <span class="about-hero__edition">
                    03 / 05
                </span>
                </div>

                <div class="about-hero__copy">

                    <h1>
                        انتخابی که
                        <br>
                        <em>شبیه خودت باشد.</em>
                    </h1>

                    <p>
                        جانان برای انتخاب‌هایی ساخته شده که لازم نیست بلند حرف بزنند
                        تا دیده شوند؛ ترکیبی از کیفیت، لطافت، فرم و جزئیاتی که
                        شخصیت خودشان را دارند.
                    </p>

                    <a
                        href="{{ route('products.index') }}"
                        class="about-hero__link"
                    >
                        کشف کالکشن
                        <span aria-hidden="true">↗</span>
                    </a>

                </div>

                <div
                    class="about-hero__mark"
                    aria-hidden="true"
                >
                    J
                </div>

            </div>
        </section>


        {{-- =====================================================
             02. BRAND STATEMENT
        ====================================================== --}}

        <section
            class="section-block about-statement"
            aria-labelledby="about-statement-title"
        >
            <div class="container">

                <div class="about-statement__grid">

                    <div class="about-statement__label">
                        <span>JANAN / 01</span>
                    </div>

                    <div class="about-statement__copy">

                    <span class="eyebrow">
                        A QUIET POINT OF VIEW
                    </span>

                        <h2 id="about-statement-title">
                            زیبایی،
                            <br>
                            وقتی دقیق انتخاب شود،
                            <em>آرام می‌شود.</em>
                        </h2>

                        <p>
                            ما باور داریم تجربه خرید نباید پر سر و صدا باشد.
                            باید واضح، خوش‌ساخت و دقیق باشد؛ از اولین نگاه
                            تا لحظه‌ای که انتخابت را ثبت می‌کنی.
                        </p>

                    </div>

                    <div
                        class="about-statement__side"
                        aria-hidden="true"
                    >
                        <span>01</span>
                        <i></i>
                        <span>05</span>
                    </div>

                </div>

            </div>
        </section>


        {{-- =====================================================
             03. VISUAL STORY
        ====================================================== --}}

        <section
            class="section-block section-block--soft about-story"
            aria-labelledby="about-story-title"
        >
            <div class="container">

                <div class="about-story__layout">

                    <div class="about-story__visual">

                        @if($latestProduct?->galleryMedia?->first()?->url)

                            <img
                                src="{{ $latestProduct->galleryMedia->first()->url }}"
                                alt="{{ $latestProduct->name }}"
                                loading="lazy"
                                decoding="async"
                            >

                        @else

                            <div class="about-story__placeholder">
                                <span>JANAN</span>
                                <strong>THE ART OF DETAILS</strong>
                            </div>

                        @endif

                        <span
                            class="about-story__stamp"
                            aria-hidden="true"
                        >
                        JANAN / DETAILS MATTER
                    </span>

                    </div>

                    <div class="about-story__content">

                    <span class="eyebrow">
                        THE HOUSE / 02
                    </span>

                        <h2 id="about-story-title">
                            هیچ جزئیاتی
                            <br>
                            <em>تصادفی نیست.</em>
                        </h2>

                        <p>
                            از فرم و تصویر تا فاصله میان عناصر و نحوه ارائه
                            محصولات، همه‌چیز در جانان برای ساختن یک تجربه
                            منسجم کنار هم قرار گرفته است.
                        </p>

                        <div class="about-story__facts">

                            <div>
                                <strong>01</strong>
                                <span>انتخاب دقیق</span>
                            </div>

                            <div>
                                <strong>02</strong>
                                <span>ارائه روشن</span>
                            </div>

                            <div>
                                <strong>03</strong>
                                <span>جزئیات ماندگار</span>
                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </section>


        {{-- =====================================================
             04. PRINCIPLES
        ====================================================== --}}

        <section
            class="section-block about-principles"
            aria-labelledby="about-principles-title"
        >
            <div class="container">

                <header class="section-head about-section-head">

                    <div>
                    <span class="eyebrow">
                        JANAN / PRINCIPLES
                    </span>

                        <h2 id="about-principles-title">
                            سه چیز که برای ما مهم است.
                        </h2>

                        <p>
                            پایه‌ای که انتخاب‌های جانان روی آن ساخته می‌شوند.
                        </p>
                    </div>

                    <span class="about-section-number" aria-hidden="true">
                    03
                </span>

                </header>


                <div class="about-principles__grid">

                    <article class="about-principle">

                    <span class="about-principle__number">
                        01
                    </span>

                        <div class="about-principle__symbol">
                            <span></span>
                        </div>

                        <h3>
                            انتخاب
                        </h3>

                        <p>
                            هر محصول قرار نیست فقط موجود باشد؛
                            باید دلیلی برای حضورش در جهان جانان داشته باشد.
                        </p>

                    </article>


                    <article class="about-principle about-principle--pink">

                    <span class="about-principle__number">
                        02
                    </span>

                        <div class="about-principle__symbol">
                            <span></span>
                        </div>

                        <h3>
                            جزئیات
                        </h3>

                        <p>
                            کیفیت تجربه از جزئیات کوچک شکل می‌گیرد؛
                            از تصویر محصول تا متن، فاصله‌ها و نحوه ارائه.
                        </p>

                    </article>


                    <article class="about-principle about-principle--dark">

                    <span class="about-principle__number">
                        03
                    </span>

                        <div class="about-principle__symbol">
                            <span></span>
                        </div>

                        <h3>
                            تجربه
                        </h3>

                        <p>
                            خرید باید ساده باشد، اما ساده به معنای معمولی نیست؛
                            هر مرحله باید واضح و خوش‌ساخت احساس شود.
                        </p>

                    </article>

                </div>

            </div>
        </section>


        {{-- =====================================================
             05. SYSTEM / REAL STORE
        ====================================================== --}}

        <section class="about-system">

            <div class="container about-system__inner">

                <div class="about-system__top">

                <span class="eyebrow">
                    JANAN / SYSTEM
                </span>

                    <span class="about-system__code">
                    LIVE / 04
                </span>

                </div>

                <div class="about-system__main">

                    <h2>
                        چیزی که می‌بینی،
                        <br>
                        <em>بخشی از یک فروشگاه واقعی است.</em>
                    </h2>

                    <p>
                        قیمت، موجودی، دسته‌بندی، برند و محصولات از داده‌های
                        واقعی فروشگاه می‌آیند؛ تا تجربه‌ای که می‌بینی فقط
                        یک تصویر زیبا نباشد، بلکه به خرید واقعی متصل باشد.
                    </p>

                </div>

                <div class="about-system__rail">

                    <div>
                        <strong>PRODUCT</strong>
                        <span>محصول واقعی</span>
                    </div>

                    <div>
                        <strong>COLLECTION</strong>
                        <span>دسته‌بندی واقعی</span>
                    </div>

                    <div>
                        <strong>HOUSE</strong>
                        <span>برندهای واقعی</span>
                    </div>

                    <div>
                        <strong>ORDER</strong>
                        <span>مسیر خرید واقعی</span>
                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
             06. QUOTE
        ====================================================== --}}

        <section class="about-quote">

            <div class="container">

                <div class="about-quote__inner">

                <span
                    class="about-quote__mark"
                    aria-hidden="true"
                >
                    “
                </span>

                    <blockquote>
                        چیزی که واقعاً برای شماست،
                        <br>
                        نیازی به توضیح اضافه ندارد.
                    </blockquote>

                    <span class="about-quote__caption">
                    — JANAN
                </span>

                </div>

            </div>

        </section>


        {{-- =====================================================
             07. FINAL CTA
        ====================================================== --}}

        <section class="section-block about-final">

            <div class="container">

                <div class="about-final__inner">

                    <div>

                    <span class="eyebrow">
                        DISCOVER JANAN
                    </span>

                        <h2>
                            حالا انتخابت را
                            <em>شروع کن.</em>
                        </h2>

                        <p>
                            وارد کالکشن شو و چیزی را پیدا کن که واقعاً
                            با تو حرف می‌زند.
                        </p>

                    </div>

                    <div class="about-final__actions">

                        <a
                            href="{{ route('products.index') }}"
                            class="button button--primary"
                        >
                            مشاهده محصولات
                        </a>

                        <a
                            href="{{ route('categories.index') }}"
                            class="button button--ghost"
                        >
                            کشف دسته‌بندی‌ها
                        </a>

                    </div>

                </div>

            </div>

        </section>

    </div>

@endsection
