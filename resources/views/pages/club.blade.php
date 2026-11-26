@extends('layouts.store')

@section('title', 'باشگاه مشتریان DrClubz — ' . ($siteBrandNameFa ?? 'جانان'))

@section('content')
<div class="store-page club-page">
    <section class="club-landing">
        <div class="container">
            <div class="club-landing__hero">
                <div class="club-landing__intro">
                    <span class="eyebrow">JANAN / DRCLUBZ</span>
                    <h1>باشگاه مشتریان<br><em>جانان.</em></h1>
                    <p>یک مسیر ساده برای دسترسی به حساب، سفارش‌ها و خدمات مشتریان جانان؛ همه‌چیز متصل به حساب واقعی فروشگاه.</p>
                    <div class="club-landing__actions">
                        @if($isCustomer)
                            <a class="button button--primary" href="{{ route('account') }}">رفتن به حساب کاربری <span aria-hidden="true">↗</span></a>
                            <a class="button button--ghost" href="{{ route('products.index') }}">ادامه خرید</a>
                        @elseif(auth()->guest())
                            <a class="button button--primary" href="{{ route('register') }}">عضویت در جانان</a>
                            <a class="button button--ghost" href="{{ route('login') }}">ورود به حساب</a>
                        @else
                            <a class="button button--primary" href="{{ route('home') }}">بازگشت به فروشگاه</a>
                        @endif
                    </div>
                </div>
                <aside class="club-landing__member" aria-label="وضعیت دسترسی باشگاه">
                    <span class="club-landing__member-kicker">MEMBER ACCESS</span>
                    @if($isCustomer)
                        <span class="club-landing__member-icon" aria-hidden="true">✓</span>
                        <strong>حساب مشتری فعال است</strong>
                        <p>آخرین سفارش‌ها و وضعیت آن‌ها را در حساب مشتری ببین.</p>
                        <a href="{{ route('account') }}#account-orders">مشاهده سفارش‌ها <span aria-hidden="true">←</span></a>
                    @elseif(auth()->guest())
                        <span class="club-landing__member-icon" aria-hidden="true">＋</span>
                        <strong>هنوز عضو نشده‌ای؟</strong>
                        <p>حساب بساز تا سفارش‌ها و اطلاعات خریدت در یک جا ثبت و قابل پیگیری باشند.</p>
                        <a href="{{ route('register') }}">ساخت حساب مشتری <span aria-hidden="true">←</span></a>
                    @else
                        <span class="club-landing__member-icon" aria-hidden="true">i</span>
                        <strong>دسترسی مشتری</strong>
                        <p>بخش باشگاه مشتریان برای حساب‌های مشتری فروشگاه طراحی شده است.</p>
                    @endif
                </aside>
            </div>

            <div class="club-landing__features">
                <article class="club-feature">
                    <span>01</span>
                    <div><strong>حساب یکپارچه</strong><p>اطلاعات حساب از همان حساب کاربری فروشگاه خوانده می‌شود.</p></div>
                </article>
                <article class="club-feature">
                    <span>02</span>
                    <div><strong>پیگیری سفارش‌ها</strong><p>وضعیت سفارش‌ها را از پنل مشتری دنبال کن؛ اطلاعات ساختگی نمایش داده نمی‌شود.</p></div>
                </article>
                <article class="club-feature">
                    <span>03</span>
                    <div><strong>خرید آسان‌تر</strong><p>از باشگاه به محصولات، سبد خرید و خدمات فروشگاه دسترسی مستقیم داری.</p></div>
                </article>
            </div>

            <section class="club-landing__rewards" aria-labelledby="club-rewards-title">
                <div>
                    <span class="eyebrow">DRCLUBZ / BENEFITS</span>
                    <h2 id="club-rewards-title">مزایای باشگاه، بر پایه اطلاعات واقعی</h2>
                    <p>امتیاز، سطح عضویت و هدیه زمانی نمایش داده می‌شوند که سرویس مرجع باشگاه مشتریان به جانان متصل و اطلاعات آن تأیید شده باشد. در حال حاضر هیچ امتیاز یا جایزه نمایشی به حساب مشتری اضافه نمی‌شود.</p>
                </div>
                <div class="club-landing__rewards-status">
                    <span aria-hidden="true">✳</span>
                    <strong>اتصال مزایای وفاداری در انتظار تنظیم سرویس مرجع</strong>
                    <small>حساب و سفارش‌های فروشگاه هم‌اکنون از Backend جانان استفاده می‌کنند.</small>
                </div>
            </section>
        </div>
    </section>
</div>
@endsection
