@extends('layouts.store')

@section('title', 'حساب کاربری — Janan')

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">MY JANAN</span>
        <h1>حساب کاربری</h1>
        <p>{{ auth()->user()->name }} عزیز، خوش آمدی. اینجا فضای شخصی جانان برای مدیریت حساب و ادامه خرید توست.</p>
    </div>
</section>

<section class="section-block section-block--soft">
    <div class="container account-page">
        <div class="account-card">
            <div>
                <span class="eyebrow">PROFILE</span>
                <h2>{{ auth()->user()->name }}</h2>
                <p>{{ auth()->user()->email }}</p>
            </div>

            @if(session('success'))
                <div class="auth-alert auth-alert--success">{{ session('success') }}</div>
            @endif

            <div class="account-actions">
                @if(auth()->user()->is_admin)
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
                                    {{ ['pending'=>'در انتظار','confirmed'=>'تأیید شده','preparing'=>'در حال آماده‌سازی','shipped'=>'ارسال شده','delivered'=>'تحویل شده','cancelled'=>'لغو شده','returned'=>'مرجوعی'][$order->status] ?? $order->status }}
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
