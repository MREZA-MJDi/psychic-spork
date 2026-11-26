@extends('layouts.store')

@section('title', 'باشگاه مشتریان — ' . ($siteBrandNameFa ?? 'جانان'))

@section('content')
<div class="store-page club-page">
    <div class="container club-page__layout">
        <header class="club-page__welcome">
            <div>
                <h1>باشگاه مشتریان جانان</h1>
                <p>از حساب کاربری سفارش‌هایت را پیگیری کن و برای خرید عمده یا پرسش دربارهٔ سفارش، مستقیم سراغ خدمات موردنیازت برو.</p>
            </div>

            <section class="club-page__account" aria-labelledby="club-account">
                <h2 id="club-account">حساب جانان</h2>
                <div class="club-page__account-actions">
                    @if($isCustomer)
                        <div>
                            <x-store.icon-link :href="route('account') . '#account-orders'" icon="orders" label="مشاهده سفارش‌ها" />
                            <span aria-hidden="true">سفارش‌ها</span>
                        </div>
                        <div>
                            <x-store.icon-link :href="route('account')" icon="user" label="حساب کاربری" />
                            <span aria-hidden="true">حساب کاربری</span>
                        </div>
                    @elseif(auth()->guest())
                        <div>
                            <x-store.icon-link :href="route('register')" icon="user-plus" label="ساخت حساب" />
                            <span aria-hidden="true">ساخت حساب</span>
                        </div>
                        <div>
                            <x-store.icon-link :href="route('login')" icon="login" label="ورود به حساب" />
                            <span aria-hidden="true">ورود به حساب</span>
                        </div>
                    @else
                        <div>
                            <x-store.icon-link :href="route('products.index')" icon="bag" label="مشاهده محصولات" />
                            <span aria-hidden="true">محصولات</span>
                        </div>
                    @endif
                </div>
            </section>
        </header>

        <section class="club-page__services" aria-labelledby="club-services">
            <h2 id="club-services">خدمات مشتریان</h2>
            <div class="club-page__service-grid">
                @foreach([
                    ['خرید از جانان', 'مدل‌ها، قیمت‌ها و سایزهای موجود را ببین.', route('products.index'), 'bag'],
                    ['خرید عمده و چکی', 'شرایط خرید عمده و درخواست اعتبار چکی را بررسی کن.', route('wholesale.show') . '#cheque-application', 'layers'],
                    ['پشتیبانی', 'برای سؤال دربارهٔ محصول یا پیگیری سفارش پیام بفرست.', route('contact'), 'message'],
                ] as [$title, $description, $url, $icon])
                    <article class="club-page__service">
                        <x-store.icon-link :href="$url" :icon="$icon" :label="$title" />
                        <div><h3>{{ $title }}</h3><p>{{ $description }}</p></div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
