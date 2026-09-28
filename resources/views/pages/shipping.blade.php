@extends('layouts.store')

@section('content')
<div class="store-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / SHIPPING</span>
                <h1>روش‌های<br><em>ارسال.</em></h1>
                <p>
                    جزئیات ارسال باید از تنظیمات واقعی فروشگاه تامین شود؛ بنابراین قبل از پیکربندی، عدد یا زمان ساختگی نمایش داده نمی‌شود.
                </p>
            </div>

            <div class="page-hero__stat">
                <b>01</b>
                <span>DELIVERY SYSTEM</span>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">

            <div class="store-page__intro">
                <span class="eyebrow">DELIVERY / POLICY</span>
                <h2>اطلاعات ارسال را از منبع واقعی بخوان.</h2>
            </div>

            <div class="store-page__steps">
                <article class="store-page__step">
                    <b>01</b>
                    <div>
                        <strong>روش‌های ارسال</strong>
                        <p>روش‌های فعال ارسال هنوز در Backend فروشگاه تعریف نشده‌اند.</p>
                    </div>
                </article>

                <article class="store-page__step">
                    <b>02</b>
                    <div>
                        <strong>هزینه ارسال</strong>
                        <p>تا زمان ثبت تنظیمات واقعی، مبلغی به‌صورت فرضی نمایش داده نمی‌شود.</p>
                    </div>
                </article>

                <article class="store-page__step">
                    <b>03</b>
                    <div>
                        <strong>بازه تحویل</strong>
                        <p>پس از پیکربندی، زمان تحویل واقعی می‌تواند همین‌جا نمایش داده شود.</p>
                    </div>
                </article>
            </div>

            <div class="page-cta-panel" style="margin-top: 18px;">
                <div>
                    <span class="eyebrow">NEED HELP?</span>
                    <h2>سوالی درباره ارسال داری؟</h2>
                    <p>برای اطلاعات واقعی فروشگاه با پشتیبانی در تماس باش.</p>
                </div>

                <a class="button button--primary" href="{{ route('contact') }}">
                    تماس با جانان
                    <span aria-hidden="true">↗</span>
                </a>
            </div>

        </div>
    </section>
</div>
@endsection
