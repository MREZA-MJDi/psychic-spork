@extends('layouts.store')

@section('content')
<div class="store-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / FAQ</span>
                <h1>سوالات<br><em>متداول.</em></h1>
                <p>
                    پاسخ‌های کوتاه و مستقیم درباره مسیر خرید و داده‌هایی که همین حالا در فروشگاه فعال‌اند.
                </p>
            </div>

            <div class="page-hero__stat">
                <b>03</b>
                <span>COMMON QUESTIONS</span>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container store-page__faq">

            <div class="store-page__intro">
                <span class="eyebrow">NEED TO KNOW</span>
                <h2>قبل از خرید، این سه نکته را بدان.</h2>
            </div>

            <div class="faq-shell">
                <details class="faq-item">
                    <summary>
                        <span><small>01</small> قیمت و موجودی از Backend می‌آیند؟</span>
                        <i>+</i>
                    </summary>
                    <p>
                        بله. صفحات عمومی قیمت، تخفیف و وضعیت موجودی محصولات فعال را از داده‌های فروشگاه می‌خوانند.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>
                        <span><small>02</small> افزودن به سبد چگونه انجام می‌شود؟</span>
                        <i>+</i>
                    </summary>
                    <p>
                        با انتخاب «افزودن به سبد خرید»، سبد به‌صورت پنجره باز می‌شود و می‌توانی تعداد را تغییر بدهی یا ادامه پرداخت را انتخاب کنی.
                    </p>
                </details>

                <details class="faq-item">
                    <summary>
                        <span><small>03</small> شرایط ارسال و مرجوعی قطعی هستند؟</span>
                        <i>+</i>
                    </summary>
                    <p>
                        فقط اطلاعاتی که واقعاً در تنظیمات فروشگاه ثبت شده نمایش داده می‌شود؛ از نمایش وعده یا عدد ساختگی خودداری شده است.
                    </p>
                </details>
            </div>
        </div>
    </section>
</div>
@endsection
