@extends('layouts.store')

@section('title', 'شرایط مرجوعی — Janan')

@section('content')
<section class="page-hero page-hero--premium"><div class="container page-hero__layout"><div><span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / RETURNS</span><h1>شرایط<br><em>مرجوعی.</em></h1><p>قوانین مرجوعی هنوز به‌صورت داده‌محور در Backend ثبت نشده‌اند؛ این صفحه سیاست فرضی تولید نمی‌کند.</p></div><div class="page-hero__stat"><b>03</b><span>مرحله تعریف سیاست</span></div></div></section>
<section class="section-block"><div class="container returns-modern"><div class="returns-modern__rail"><span>POLICY FLOW</span><strong>RETURN</strong></div><div class="returns-modern__steps">
<article class="policy-step reveal-up"><span>01</span><div><small>POLICY SOURCE</small><h2>منبع سیاست</h2><p>قوانین واقعی باید از تنظیمات یا محتوای مدیریتی فروشگاه تامین شوند.</p></div></article>
<article class="policy-step reveal-up"><span>02</span><div><small>ELIGIBILITY</small><h2>شرایط پذیرش</h2><p>جزئیات واجد شرایط بودن سفارش‌ها هنوز در سیستم تعریف نشده است.</p></div></article>
<article class="policy-step reveal-up"><span>03</span><div><small>PROCESS</small><h2>فرآیند پیگیری</h2><p>پس از ثبت سیاست واقعی، مراحل درخواست و پیگیری در این صفحه نمایش داده می‌شوند.</p></div></article>
</div></div></section>
@endsection
