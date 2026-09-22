@extends('layouts.store')

@section('title', 'روش‌های ارسال — Janan')

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / SHIPPING</span>
        <h1>روش‌های ارسال</h1>
        <p>این صفحه به‌صورت شفاف نشان می‌دهد که اطلاعات عملیاتی ارسال هنوز از Backend فروشگاه دریافت نمی‌شود؛ بنابراین عدد یا وعده ساختگی نمایش نمی‌دهیم.</p>
    </div>
</section>

<section class="section-block">
    <div class="container policy-shell">
        <div class="policy-index reveal-up">
            <span class="eyebrow">DELIVERY SYSTEM</span>
            <strong>اطلاعات ارسال</strong>
            <span>در انتظار پیکربندی</span>
        </div>

        <div class="policy-content reveal-up" style="--delay:.08s">
            <div class="policy-row">
                <span>روش‌های فعال</span>
                <strong>هنوز در Backend تعریف نشده</strong>
            </div>
            <div class="policy-row">
                <span>هزینه ارسال</span>
                <strong>تا زمان ثبت تنظیمات، عددی نمایش داده نمی‌شود</strong>
            </div>
            <div class="policy-row">
                <span>بازه تحویل</span>
                <strong>وابسته به تنظیمات واقعی فروشگاه</strong>
            </div>
        </div>
    </div>
</section>

<section class="section-block section-block--soft">
    <div class="container page-cta-panel reveal-up">
        <div>
            <span class="eyebrow">NEED HELP?</span>
            <h2>قبل از نهایی‌کردن خرید، اطلاعات دقیق را چک کن.</h2>
            <p>وقتی تنظیمات ارسال در سیستم ثبت شود، همین صفحه محل نمایش آن خواهد بود.</p>
        </div>
        <a class="button button--primary" href="{{ route('contact') }}">تماس با جانان</a>
    </div>
</section>
@endsection