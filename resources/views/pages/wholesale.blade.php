@extends('layouts.store')

@section('title', 'خرید عمده — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="section-block">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">JANAN / WHOLESALE</span>
                <h1>خرید عمده</h1>
                <p>دسترسی عمده پس از بررسی و تأیید مدیریت فعال می‌شود.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif

        @if($profile?->isApproved())
            <div class="checkout-card">
                <strong>حساب عمده شما فعال است.</strong>
                <p>می‌توانید سفارش‌های عمده را با شرایط اختصاصی حساب خود ثبت کنید.</p>
            </div>
        @elseif($profile?->status === 'pending')
            <div class="checkout-card">
                <strong>درخواست شما در حال بررسی است.</strong>
                <p>پس از تأیید مدیریت، دسترسی خرید عمده فعال خواهد شد.</p>
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
                    ارسال درخواست خرید عمده
                </button>
            </form>
        @endif
    </div>
</section>
@endsection
