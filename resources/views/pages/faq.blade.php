@extends('layouts.store')

@section('title', 'سوالات متداول — Janan')

@section('content')
<section class="page-hero page-hero--motion">
    <div class="container">
        <span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / FAQ</span>
        <h1>سوالات متداول</h1>
        <p>ساختار FAQ آماده است؛ فقط سوال و جواب‌های واقعی کسب‌وکار باید از سمت مدیریت ثبت شوند.</p>
    </div>
</section>

<section class="section-block">
    <div class="container faq-shell">
        <details class="faq-item reveal-up">
            <summary><span>آیا قیمت محصولات از Backend می‌آید؟</span><i>+</i></summary>
            <p>بله. صفحات عمومی قیمت، تخفیف و وضعیت موجودی محصولات فعال را از مدل Product می‌خوانند.</p>
        </details>
        <details class="faq-item reveal-up" style="--delay:.06s">
            <summary><span>آیا محتوای Hero با محصولات فروشگاه هماهنگ است؟</span><i>+</i></summary>
            <p>بله. Hero در صفحه اصلی از محصولات فعال و برندهای مرتبط فروشگاه داده می‌گیرد و در هر بار نمایش، ترکیب محصولات به‌صورت پویا انتخاب می‌شود.</p>
        </details>
        <details class="faq-item reveal-up" style="--delay:.12s">
            <summary><span>شرایط ارسال و مرجوعی کجاست؟</span><i>+</i></summary>
            <p>مسیرهای آن‌ها ساخته شده‌اند؛ جزئیات عملیاتی عمداً تا زمان ثبت اطلاعات واقعی فروشگاه، به‌صورت فرضی نمایش داده نمی‌شوند.</p>
        </details>
    </div>
</section>
@endsection