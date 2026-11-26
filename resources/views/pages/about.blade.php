@extends('layouts.store')

@section('content')
<div class="store-page about-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / ABOUT</span>
                <h1>
{!! nl2br(e($about['hero_title'] ?? 'انتخاب خوب،\nاز شناخت شروع می‌شود.')) !!}
                </h1>
                <p>
                    {{ $about['hero_description'] ?? '' }}
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

    <section class="section-block section-block--compact">
        <div class="container">
            <div class="customer-action-strip">
                <div class="customer-action-strip__copy"><small>JANAN / START HERE</small><strong>کاتالوگ، دسته‌ها و ارتباط؛ همه در یک مسیر.</strong></div>
                <div class="customer-action-strip__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">کشف محصولات</a>
                    <a class="button button--ghost" href="#contact">ارتباط با جانان</a>
                </div>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">

            <div class="about-story">
                <div class="about-story__lead">
                    <span class="eyebrow">A CLEARER WAY TO SHOP</span>
                    <h2>
                        {{ $about['story_title'] ?? '' }}
                    </h2>
                </div>

                <div class="about-story__copy">
                    <p>
                        {{ $about['story_text_1'] ?? '' }}
                    </p>

                    <p>
                        {{ $about['story_text_2'] ?? '' }}
                    </p>
                </div>
            </div>

            <div class="about-stat-grid" aria-label="آمار فعلی فروشگاه">
                <div class="about-stat-card">
                    <small>ACTIVE PRODUCTS</small>
                    <strong>{{ \App\Support\PersianNumber::format($aboutStats['products'] ?? 0) }}</strong>
                    <span>محصول فعال در کاتالوگ</span>
                </div>

                <div class="about-stat-card">
                    <small>CATEGORIES</small>
                    <strong>{{ \App\Support\PersianNumber::format($aboutStats['categories'] ?? 0) }}</strong>
                    <span>دسته‌بندی فعال</span>
                </div>

                <div class="about-stat-card">
                    <small>BRANDS</small>
                    <strong>{{ \App\Support\PersianNumber::format($aboutStats['brands'] ?? 0) }}</strong>
                    <span>برند فعال</span>
                </div>

                <div class="about-stat-card about-stat-card--dark">
                    <small>JANAN / LIVE DATA</small>
                    <strong>۰۱</strong>
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
                    <span>۰۱</span>
                    <div>
                        <h3>{{ $about['principle_1_title'] ?? 'شفافیت' }}</h3>
                        <p>{{ $about['principle_1_text'] ?? '' }}</p>
                    </div>
                </article>

                <article class="about-principle">
                    <span>۰۲</span>
                    <div>
                        <h3>{{ $about['principle_2_title'] ?? 'سادگی' }}</h3>
                        <p>{{ $about['principle_2_text'] ?? '' }}</p>
                    </div>
                </article>

                <article class="about-principle">
                    <span>۰۳</span>
                    <div>
                        <h3>{{ $about['principle_3_title'] ?? 'جزئیات' }}</h3>
                        <p>{{ $about['principle_3_text'] ?? '' }}</p>
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
                        <strong>{{ \App\Support\PersianNumber::digits($latestProduct->name) }}</strong>

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

    <section id="contact" class="store-page__section contact-page contact-page--refined" aria-labelledby="contact-title">
        <div class="container">
            <header class="contact-intro">
                <div><small>پشتیبانی جانان</small><h2 id="contact-title">تماس با ما</h2><p>برای راهنمایی خرید، پیگیری سفارش یا همکاری، از مسیر مناسب با ما در ارتباط باشید.</p></div>
                <a class="button button--ghost" href="{{ route('faq') }}">سوالات متداول</a>
            </header>

            @if(session('success'))
                <div class="contact-alert contact-alert--success" role="status">
                    <strong>پیام ثبت شد.</strong>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="contact-alert contact-alert--error" role="alert">
                    <strong>ارسال انجام نشد.</strong>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="contact-alert contact-alert--error" role="alert">
                    <strong>اطلاعات فرم را بررسی کن.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="contact-layout">
                <section class="contact-form-card">
                    <div class="contact-form-card__head">
                        <div>
                            <h2>ارسال پیام</h2>
                            <p>
                                برای پرسش دربارهٔ محصول، سفارش یا خرید عمده به پشتیبانی پیام بده.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('contact.submit') }}" class="contact-form">
                        @csrf

                        <div class="contact-form__grid">
                            <label class="contact-field">
                                <span>نام و نام خانوادگی <b>*</b></span>
                                <input type="text" name="name" value="{{ old('name', auth()->user()?->name) }}" autocomplete="name" maxlength="120" required>
                            </label>

                            <label class="contact-field">
                                <span>ایمیل</span>
                                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" autocomplete="email" maxlength="255">
                            </label>

                            <label class="contact-field">
                                <span>شماره تماس</span>
                                <input type="tel" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" autocomplete="tel" maxlength="30" dir="ltr">
                            </label>

                            <label class="contact-field">
                                <span>موضوع</span>
                                <input type="text" name="subject" value="{{ old('subject') }}" autocomplete="off" maxlength="255">
                            </label>

                            <label class="contact-field contact-field--full">
                                <span>پیام <b>*</b></span>
                                <textarea name="message" rows="7" maxlength="5000" required>{{ old('message') }}</textarea>
                            </label>
                        </div>

                        <div class="contact-form__foot">
                            <p>اطلاعات این فرم فقط برای پیگیری همین درخواست استفاده می‌شود.</p>
                            <button class="button button--primary" type="submit">
                                ارسال پیام
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="contact-side"><section class="contact-info-card">
                    <div>
                        <h2>راه‌های ارتباط</h2>
                        <p>اگر تماس مستقیم برایت راحت‌تر است، از یکی از مسیرهای زیر استفاده کن.</p>
                    </div>

                    <div class="contact-info-list">
                        <div class="contact-info-item">
                            <div>
                                <small>شماره تماس</small>
                                @if($contactStore['phone'] ?? null)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $contactStore['phone']) }}" dir="ltr">{{ $contactStore['phone'] }}</a>
                                @else
                                    <strong>شماره تماس ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div>
                                <small>ایمیل</small>
                                @if($contactStore['email'] ?? null)
                                    <a href="mailto:{{ $contactStore['email'] }}" dir="ltr">{{ $contactStore['email'] }}</a>
                                @else
                                    <strong>ایمیل ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div>
                                <small>نشانی</small>
                                @if($contactStore['address'] ?? null)
                                    <strong>{{ $contactStore['address'] }}</strong>
                                @else
                                    <strong>آدرس ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div>
                                <small>ساعت پاسخ‌گویی</small>
                                <strong>{{ $contactStore['working_hours'] ?? 'ساعت کاری ثبت نشده' }}</strong>
                            </div>
                        </div>
                    </div>
                </section>
                @if(filled($contactStore['address'] ?? null))
                    @php($mapQuery = rawurlencode($contactStore['address']))
                    <section class="contact-map-card" aria-labelledby="contact-map-title">
                        <div><h3 id="contact-map-title">موقعیت فروشگاه</h3><p>{{ $contactStore['address'] }}</p></div>
                        <iframe title="نقشه نشانی جانان" src="https://maps.google.com/maps?q={{ $mapQuery }}&amp;output=embed" loading="lazy" referrerpolicy="no-referrer" allowfullscreen></iframe>
                        <a class="button button--ghost" href="https://www.google.com/maps/search/?api=1&amp;query={{ $mapQuery }}" target="_blank" rel="noopener noreferrer">مشاهده نشانی در گوگل مپ</a>
                    </section>
                @endif
                </aside>
            </div>
            <section class="contact-trust" aria-label="راهنمای خرید و پیگیری سفارش">
                <a href="{{ route('shipping') }}"><strong>روش‌های ارسال</strong><small>اطلاعات ارسال سفارش‌ها</small></a>
                <a href="{{ route('returns') }}"><strong>شرایط بازگشت</strong><small>مرور سیاست‌های بازگشت کالا</small></a>
                <a href="{{ route('account') }}"><strong>پیگیری سفارش</strong><small>ورود به حساب و مشاهده سفارش‌ها</small></a>
            </section>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">
            <div class="page-cta-panel about-page__cta">
                <div>
                    <span class="eyebrow">START HERE</span>
                    <h2>{{ $about['cta_title'] ?? 'از کشف شروع کن.' }}</h2>
                    <p>{{ $about['cta_text'] ?? '' }}</p>
                </div>

                <div class="store-page__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">کشف محصولات</a>
                    <a class="button button--ghost" href="#contact">تماس با ما</a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
