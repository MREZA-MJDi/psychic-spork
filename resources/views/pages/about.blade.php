@extends('layouts.store')

@section('title', 'درباره جانان — Janan')

@section('content')
<section class="page-hero page-hero--premium"><div class="container page-hero__layout"><div><span class="eyebrow">03 / THE HOUSE OF {{ $siteBrandNameLatin ?? 'JANAN' }}</span><h1>جایی برای انتخابی<br><em>که شبیه خودت باشد.</em></h1><p>جنان یک فروشگاه Laravel-based است؛ محصول، دسته‌بندی، برند و وضعیت فروش از Backend مدیریت می‌شوند و تجربه خرید روی همین داده‌ها ساخته شده است.</p></div><div class="page-hero__word" aria-hidden="true">J</div></div></section>
<section class="section-block"><div class="container manifesto-grid">
<article class="manifest-card manifesto-card--wide reveal-up"><span class="eyebrow">01 / IDENTITY</span><h2>سادگی لوکس، حرکت دقیق.</h2><p>هویت Janan بر پایه فضای روشن، تایپوگرافی فارسی یکپارچه، جزئیات ظریف و Motion کنترل‌شده ساخته شده است؛ نه شلوغی بصری.</p></article>
<article class="manifest-card manifest-card--dark reveal-up"><span class="eyebrow">02 / SYSTEM</span><h2>هر چیزی که می‌بینی، قابل مدیریت است.</h2><p>محصولات فعال، قیمت، موجودی، دسته‌بندی، برند و محتوای Hero از همان داده‌ای می‌آیند که در پنل مدیریت فروشگاه ثبت شده است.</p></article>
<article class="manifest-card reveal-up"><span class="eyebrow">03 / EXPERIENCE</span><h2>تجربه‌ای که روی موبایل هم جدی است.</h2><p>Gridهای واکنش‌گرا، منوی موبایل، Motion کم‌مصرف و حالت Reduced Motion برای دسترسی بهتر در نظر گرفته شده‌اند.</p></article>
</div></section>
<section class="section-block section-block--soft"><div class="container page-cta-panel reveal-up"><div><span class="eyebrow">EXPLORE JANAN</span><h2>از کالکشن واقعی شروع کن.</h2><p>محصولات فعال فروشگاه را ببین و انتخابت را از داده‌های واقعی Backend انجام بده.</p></div><div class="page-cta-panel__actions"><a class="button button--primary" href="{{ route('products.index') }}">مشاهده محصولات</a><a class="button button--ghost" href="{{ route('categories.index') }}">دسته‌بندی‌ها</a></div></div></section>
@endsection
