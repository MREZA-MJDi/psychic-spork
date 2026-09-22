@extends('layouts.store')

@section('title', 'روش‌های ارسال — Janan')

@section('content')
<section class="page-hero page-hero--premium"><div class="container page-hero__layout"><div><span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / SHIPPING</span><h1>روش‌های<br><em>ارسال.</em></h1><p>اطلاعات عملیاتی ارسال هنوز از Backend فروشگاه دریافت نمی‌شود؛ بنابراین عدد یا وعده ساختگی نمایش نمی‌دهیم.</p></div><div class="page-hero__stat"><b>—</b><span>در انتظار پیکربندی</span></div></div></section>
<section class="section-block"><div class="container policy-modern"><div class="policy-modern__index"><span>DELIVERY SYSTEM</span><strong>01</strong><small>CONFIGURATION PENDING</small></div><div class="policy-modern__rows"><div><small>METHODS</small><strong>هنوز در Backend تعریف نشده</strong><span>روش‌های فعال ارسال</span></div><div><small>SHIPPING COST</small><strong>تا زمان ثبت تنظیمات، عددی نمایش داده نمی‌شود</strong><span>هزینه ارسال</span></div><div><small>DELIVERY WINDOW</small><strong>وابسته به تنظیمات واقعی فروشگاه</strong><span>بازه تحویل</span></div></div></div></section>
<section class="section-block section-block--soft"><div class="container page-cta-panel reveal-up"><div><span class="eyebrow">NEED HELP?</span><h2>اطلاعات دقیق را قبل از خرید چک کن.</h2><p>وقتی تنظیمات ارسال در سیستم ثبت شود، همین صفحه محل نمایش آن خواهد بود.</p></div><a class="button button--primary" href="{{ route('contact') }}">تماس با جانان ↗</a></div></section>
@endsection
