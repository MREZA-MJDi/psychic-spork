@extends('layouts.store')

@section('title', 'باشگاه مشتریان DrClubz — ' . ($siteBrandNameFa ?? 'جانان'))

@section('content')
<div class="store-page club-page">
    <section class="club-landing">
        <div class="container">
            <header class="club-landing__intro">
                <h1>باشگاه مشتریان جانان</h1>
                <p>سفارش‌هایت را پیگیری کن و به خدمات جانان دسترسی داشته باش.</p>
                <div class="club-landing__actions">
                    @if($isCustomer)
                        <a class="button button--primary" href="{{ route('account') }}#account-orders">مشاهده سفارش‌ها</a>
                        <a class="button button--ghost" href="{{ route('account') }}">حساب کاربری</a>
                    @elseif(auth()->guest())
                        <a class="button button--primary" href="{{ route('register') }}">ساخت حساب</a>
                        <a class="button button--ghost" href="{{ route('login') }}">ورود به حساب</a>
                    @else
                        <a class="button button--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                    @endif
                </div>
            </header>

            <nav class="club-landing__features" aria-label="خدمات جانان">
                <a class="club-feature" href="{{ route('products.index') }}">
                    <div><strong>خرید از جانان</strong><p>محصولات را بر اساس دسته‌بندی یا برند پیدا کن.</p></div>
                </a>
                <a class="club-feature" href="{{ route('wholesale.show') }}#cheque-application">
                    <div><strong>خرید عمده و چکی</strong><p>شرایط خرید عمده و درخواست اعتبار چکی را ببین.</p></div>
                </a>
                <a class="club-feature" href="{{ route('contact') }}">
                    <div><strong>پشتیبانی</strong><p>برای پرسش دربارهٔ محصول یا سفارش به جانان پیام بده.</p></div>
                </a>
            </nav>
        </div>
    </section>
</div>
@endsection
