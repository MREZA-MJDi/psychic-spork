@extends('layouts.store')

@section('title', 'شرایط مرجوعی — Janan')

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / RETURNS</span>
        <h1>شرایط مرجوعی</h1>
        <p>قوانین مرجوعی فروشگاه هنوز به‌صورت داده‌محور در Backend ثبت نشده‌اند؛ بنابراین این صفحه سیاست فرضی تولید نمی‌کند.</p>
    </div>
</section>

<section class="section-block">
    <div class="container policy-timeline">
        <div class="policy-step reveal-up">
            <span>01</span>
            <div>
                <small>POLICY SOURCE</small>
                <h2>منبع سیاست</h2>
                <p>قوانین واقعی باید از تنظیمات یا محتوای مدیریتی فروشگاه تامین شوند.</p>
            </div>
        </div>
        <div class="policy-step reveal-up" style="--delay:.08s">
            <span>02</span>
            <div>
                <small>ELIGIBILITY</small>
                <h2>شرایط پذیرش</h2>
                <p>جزئیات واجد شرایط بودن سفارش‌ها هنوز در سیستم تعریف نشده است.</p>
            </div>
        </div>
        <div class="policy-step reveal-up" style="--delay:.16s">
            <span>03</span>
            <div>
                <small>PROCESS</small>
                <h2>فرآیند پیگیری</h2>
                <p>پس از ثبت سیاست واقعی، مراحل درخواست و پیگیری در این صفحه نمایش داده می‌شوند.</p>
            </div>
        </div>
    </div>
</section>
@endsection