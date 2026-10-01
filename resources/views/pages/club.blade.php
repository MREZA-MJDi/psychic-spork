@extends('layouts.store')

@section('content')
<div class="store-page club-page">
    <section class="page-hero page-hero--premium club-page__hero">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / DRCLUBZ</span>
                <h1>باشگاه<br><em>مشتریان.</em></h1>
                <p>
                    DrClubz مسیر اختصاصی اعضای جانان برای دسترسی سریع‌تر به خرید، سفارش‌ها و تجربه مشتری است.
                </p>
            </div>

            <div class="page-hero__stat">
                <b>DR</b>
                <span>Janan Customer Club</span>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">
            <div class="club-page__grid">
                <article class="club-card club-card--primary">
                    <span class="club-card__eyebrow">DRCLUBZ / MEMBER ACCESS</span>
                    <h2>{{ auth()->check() ? 'خوش آمدی؛ باشگاهت از همین‌جا شروع می‌شود.' : 'عضو DrClubz شو.' }}</h2>
                    <p>
                        {{ auth()->check()
                            ? 'از حساب کاربری می‌توانی سفارش‌ها و اطلاعات خریدت را یک‌جا پیگیری کنی.'
                            : 'برای ورود به تجربه شخصی‌سازی‌شده باشگاه، وارد حساب شو یا یک حساب جدید بساز.' }}
                    </p>
                    <div class="club-card__actions">
                        @auth
                            <a class="button button--primary" href="{{ route('account') }}">حساب من <span aria-hidden="true">↗</span></a>
                            <a class="button button--ghost" href="{{ route('products.index') }}">کشف محصولات</a>
                        @else
                            <a class="button button--primary" href="{{ route('login') }}">ورود به DrClubz <span aria-hidden="true">↗</span></a>
                            <a class="button button--ghost" href="{{ route('register') }}">ثبت‌نام</a>
                        @endauth
                    </div>
                </article>

                <div class="club-page__stack">
                    <article class="club-card">
                        <span class="club-card__number">01</span>
                        <strong>حساب و سفارش‌ها</strong>
                        <p>اطلاعات واقعی حساب و سفارش‌ها از Backend فروشگاه نمایش داده می‌شوند.</p>
                    </article>
                    <article class="club-card">
                        <span class="club-card__number">02</span>
                        <strong>خرید سریع‌تر</strong>
                        <p>از باشگاه مستقیماً به کاتالوگ، محصول و سبد خرید برگرد.</p>
                    </article>
                    <article class="club-card">
                        <span class="club-card__number">03</span>
                        <strong>مزایای باشگاه</strong>
                        <p>امتیاز و پاداش فقط پس از اتصال منطق واقعی Loyalty نمایش داده خواهد شد؛ عدد نمایشی نداریم.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
