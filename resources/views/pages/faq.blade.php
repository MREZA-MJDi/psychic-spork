@extends('layouts.store')

@section('title', 'سوالات متداول — Janan')

@section('content')
<section class="page-hero page-hero--premium"><div class="container page-hero__layout"><div><span class="eyebrow">05 / FAQ</span><h1>سوالات<br><em>متداول.</em></h1><p>پاسخ‌های فعلی درباره ساختار فروشگاه و داده‌هایی که همین حالا از Backend خوانده می‌شوند.</p></div><div class="page-hero__stat"><b>03</b><span>پرسش فعال</span></div></div></section>
<section class="section-block"><div class="container faq-shell faq-shell--premium">
<details class="faq-item reveal-up"><summary><span><small>01</small>آیا قیمت محصولات از Backend می‌آید؟</span><i>+</i></summary><p>بله. صفحات عمومی قیمت، تخفیف و وضعیت موجودی محصولات فعال را از مدل Product می‌خوانند.</p></details>
<details class="faq-item reveal-up"><summary><span><small>02</small>آیا محتوای Hero با محصولات فروشگاه هماهنگ است؟</span><i>+</i></summary><p>بله. Hero در صفحه اصلی از محصولات فعال و برندهای مرتبط فروشگاه داده می‌گیرد و در هر بار نمایش، ترکیب محصولات به‌صورت پویا انتخاب می‌شود.</p></details>
<details class="faq-item reveal-up"><summary><span><small>03</small>شرایط ارسال و مرجوعی کجاست؟</span><i>+</i></summary><p>مسیرهای آن‌ها ساخته شده‌اند؛ جزئیات عملیاتی عمداً تا زمان ثبت اطلاعات واقعی فروشگاه، به‌صورت فرضی نمایش داده نمی‌شوند.</p></details>
</div></section>
@endsection
