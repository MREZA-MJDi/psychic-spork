@extends('layouts.store')

@section('title', 'حساب کاربری — Janan')

@section('content')
<section class="page-hero page-hero--motion account-page-hero">
    <div class="container">

        <h1>حساب کاربری</h1>
        <p>{{ $user->name }} عزیز، خوش آمدی. سفارش‌ها، وضعیت چک و درخواست اعتبارت را از همین‌جا پیگیری کن.</p>
    </div>
</section>

<section class="section-block section-block--soft">
    <div class="container account-page">
        <div class="customer-action-strip account-action-strip">
            <div class="customer-action-strip__copy">

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

                <h2 id="account-cheque-title">وضعیت درخواست خرید چکی</h2>
                @if($chequePermission?->isApproved())
                    <p>درخواستت تأیید شده است. سقف مجاز هر سفارش: <strong>{{ $chequePermission->max_order_amount !== null ? \App\Support\PersianNumber::format((float) $chequePermission->max_order_amount) . ' تومان' : 'بدون سقف تعیین‌شده' }}</strong>.</p>
                @elseif($chequePermission?->isPending())
                    <p>درخواست {{ \App\Support\PersianNumber::format((float) $chequePermission->requested_amount) }} تومانی ثبت شده و منتظر بررسی مدیر است.</p>
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

                <h2>{{ \App\Support\PersianNumber::digits($user->name) }}</h2>
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

        <div class="account-card account-card--soft" id="account-orders">

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
                                <b>{{ \App\Support\PersianNumber::format($order->total) }} تومان</b>
                                <span>{{ \App\Support\PersianNumber::format($order->items_count) }} قلم ·
                                    {{ $statusNames[$order->status] ?? $order->status }}
                                </span>
                                @if($order->wholesalePacks->isNotEmpty())
                                    <small>پک عمده:
                                        {{ $order->wholesalePacks->map(fn ($pack) => $pack->pack_name . ' × ' . $pack->quantity)->implode('، ') }}
                                    </small>
                                @endif
                                @if($order->chequePayment)
                                    <small class="account-order__cheque-status">
                                        {{ $chequeStatusNames[$order->chequePayment->status] ?? $order->chequePayment->status }}
                                    </small>
                                    @if(in_array($order->chequePayment->status, ['awaiting_original', 'rejected'], true) && $order->chequePayment->review_note)
                                        <small>{{ $order->chequePayment->review_note }}</small>
                                    @endif
                                @endif
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
