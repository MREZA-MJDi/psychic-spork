@extends('layouts.store')

@section('title', 'خرید عمده — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="section-block">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">JANAN / WHOLESALE</span>
                <h1>خرید عمده</h1>
                <p>خرید عمده برای همه حساب‌های مشتری آزاد است؛ تأیید مدیریت فقط برای فعال‌سازی پرداخت چکی لازم است.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif

        @if($profile?->isApproved())
            <div class="checkout-card">
                <strong>اطلاعات کسب‌وکار شما تأیید شده است.</strong>
                <p>این تأیید شرط خرید عمده نیست؛ شرایط اختصاصی سفارش شما از همین پروفایل خوانده می‌شود.</p>
            </div>
        @elseif($profile?->status === 'pending')
            <div class="checkout-card">
                <strong>اطلاعات کسب‌وکار شما در حال بررسی است.</strong>
                <p>این بررسی مانع ثبت سفارش عمده شما نمی‌شود.</p>
            </div>
        @else
            <form method="POST" action="{{ route('wholesale.apply') }}" class="checkout-card checkout-form">
                @csrf

                <div class="form-grid">
                    <label>
                        نام فروشگاه یا مجموعه *
                        <input name="business_name" value="{{ old('business_name', $profile?->business_name) }}" required>
                    </label>

                    <label>
                        نوع فعالیت
                        <input name="business_type" value="{{ old('business_type', $profile?->business_type) }}">
                    </label>

                    <label>
                        شماره تماس کاری
                        <input name="business_phone" value="{{ old('business_phone', $profile?->business_phone) }}" inputmode="tel">
                    </label>

                    <label class="form-grid__full">
                        آدرس کاری
                        <textarea name="business_address" rows="4">{{ old('business_address', $profile?->business_address) }}</textarea>
                    </label>
                </div>

                <button class="button button--primary" type="submit">
                    ثبت اطلاعات کسب‌وکار
                </button>
            </form>
        @endif

        <div class="checkout-card" style="margin-top:18px;">
            <div class="section-head">
                <div>
                    <span class="eyebrow">CHEQUE ACCESS</span>
                    <h2>پرداخت با چک</h2>
                </div>
            </div>

            @if($chequePermission?->isApproved())
                <strong>پرداخت چکی برای این حساب فعال است.</strong>
                <p>حین تسویه، فقط برای سفارش عمده می‌توانی گزینه چک را انتخاب کنی.</p>
            @elseif($chequePermission?->isPending())
                <strong>درخواست مجوز چک در انتظار بررسی است.</strong>
                <p>بعد از تأیید مدیریت، گزینه پرداخت با چک در تسویه فعال می‌شود.</p>
            @else
                <strong>برای پرداخت با چک، مجوز جداگانه لازم است.</strong>
                <p>خرید عمده همین حالا آزاد است؛ فقط دسترسی پرداخت چکی نیاز به تأیید مدیریت دارد.</p>

                <form method="POST" action="{{ route('wholesale.cheque.request') }}" style="margin-top:14px;">
                    @csrf
                    <button class="button button--ghost" type="submit">درخواست مجوز چک</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
