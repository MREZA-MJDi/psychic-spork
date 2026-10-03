@extends('layouts.store')

@section('title', 'حساب کاربری — Janan')

@section('content')
<section class="page-hero page-hero--motion account-page-hero">
    <div class="container">
        <span class="eyebrow">MY JANAN</span>
        <h1>حساب کاربری</h1>
        <p>{{ $user->name }} عزیز، خوش آمدی. سفارش‌ها و درخواست اعتبار چکی‌ات را از همین‌جا پیگیری کن.</p>
    </div>
</section>

<section class="section-block section-block--soft">
    <div class="container account-page">
        <div class="customer-action-strip account-action-strip">
            <div class="customer-action-strip__copy">
                <small>MY JANAN / QUICK ACTIONS</small>
                <strong>برای خرید چکی، درخواست اعتبار ثبت کن؛ خرید عمده آنلاین هم برای همه باز است.</strong>
            </div>
            <div class="customer-action-strip__actions">
                <a class="button button--primary" href="{{ route('wholesale.show') }}#cheque-application">درخواست خرید چکی</a>
                <a class="button button--ghost" href="{{ route('wholesale.show') }}">خرید عمده آنلاین</a>
                <a class="button button--ghost" href="{{ route('products.index') }}">محصولات تکی</a>
            </div>
        </div>

        <section class="account-cheque-card" aria-labelledby="account-cheque-title">
            <div class="account-cheque-card__copy">
                <span class="eyebrow">CHEQUE PURCHASE</span>
                <h2 id="account-cheque-title">وضعیت درخواست خرید چکی</h2>
                @if($chequePermission?->isApproved())
                    <p>درخواستت تأیید شده است. سقف مجاز هر سفارش: <strong>{{ $chequePermission->max_order_amount !== null ? number_format((float) $chequePermission->max_order_amount) . ' تومان' : 'بدون سقف تعیین‌شده' }}</strong>.</p>
                @elseif($chequePermission?->isPending())
                    <p>درخواست {{ number_format((float) $chequePermission->requested_amount) }} تومانی ثبت شده و منتظر بررسی مدیر است.</p>
                @else
                    <p>برای فعال‌شدن پرداخت چکی، مبلغ اعتبار مدنظرت را ثبت کن. بعد از تأیید مدیر، گزینه پرداخت چکی در تسویه‌حساب فعال می‌شود.</p>
                @endif
            </div>
            @if($chequePermission?->isApproved())
                <a class="button button--primary" href="{{ route('checkout') }}#payment-options">ادامه به پرداخت</a>
            @elseif($chequePermission?->isPending())
                <a class="button button--ghost" href="{{ route('wholesale.show') }}#cheque-application">دیدن درخواست</a>
            @else
                <a class="button button--primary" href="{{ route('wholesale.show') }}#cheque-application">ثبت درخواست چکی</a>
            @endif
        </section>

        <div class="account-card">
            <div>
                <span class="eyebrow">PROFILE</span>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->phone }}{{ $user->email ? ' · ' . $user->email : '' }}</p>
            </div>

            @if(session('success'))
                <div class="auth-alert auth-alert--success">{{ session('success') }}</div>
            @endif

            <div class="account-actions">
                @if($user->isAdmin())
                    <a class="button button--dark" href="{{ route('admin.dashboard') }}">ورود به داشبورد مدیریت</a>
                @endif
                <a class="button button--primary" href="{{ url('/products') }}">ادامه خرید</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button--ghost" type="submit">خروج از حساب</button>
                </form>
            </div>
        </div>

        <div class="account-card account-card--soft">
            <span class="eyebrow">ORDER HISTORY</span>
            <h3>سفارش‌های اخیر</h3>
            @if($orders->isNotEmpty())
                <div class="account-orders">
                    @foreach($orders as $order)
                        <article class="account-order">
                            <div>
                                <strong>{{ $order->order_number }}</strong>
                                <span>{{ optional($order->placed_at)->format('Y/m/d H:i') }}</span>
                            </div>
                            <div>
                                <b>{{ number_format($order->total) }} تومان</b>
                                <span>{{ number_format($order->items_count) }} قلم ·
                                    {{ $statusNames[$order->status] ?? $order->status }}
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p>هنوز سفارشی از این حساب ثبت نشده است. از کالکشن شروع کن.</p>
            @endif
        </div>
    </div>
</section>
@endsection
