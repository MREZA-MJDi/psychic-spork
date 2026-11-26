@extends('layouts.store')

@section('title', 'خرید عمده — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="wholesale-page">

    <section class="section-block section-block--compact">
        <div class="container">
            <div class="wholesale-hero wholesale-hero--reference">
                <div class="wholesale-hero__main">
                    <span class="eyebrow">JANAN / WHOLESALE</span>
                    <h1>پک‌های عمدهٔ جانان</h1>
                    <p>پک‌های آماده با ترکیب اقلام و قیمت ثبت‌شده در فروشگاه؛ انتخاب کن، موجودی را بررسی کن و مستقیم به سبد خرید اضافه کن.</p>
                    <div class="wholesale-hero__actions">
                        <a class="wholesale-button" href="#packs">مشاهدهٔ پک‌ها</a>
                        <a class="wholesale-button wholesale-button--soft" href="#cheque-application">شرایط خرید چکی</a>
                    </div>
                </div>
                <aside class="wholesale-hero__status" aria-label="وضعیت خرید عمده">
                    <span class="wholesale-status__label">وضعیت خرید</span>
                    <strong>خرید آنلاین عمده فعال است</strong>
                    <p>برای سفارش آنلاین نیازی به تأیید قبلی حساب نیست. پرداخت چکی فقط با مجوز فعال مدیر امکان‌پذیر است.</p>
                    <a href="{{ route('wholesale.catalog') }}">مشاهدهٔ کاتالوگ محصولات عمده</a>
                </aside>
            </div>

            <div class="wholesale-benefit-grid">
                <article class="wholesale-benefit"><b>پک‌های واقعی</b><span>اطلاعات هر بسته از پنل مدیریت و دیتابیس خوانده می‌شود.</span></article>
                <article class="wholesale-benefit"><b>قیمت شفاف</b><span>قیمت ثبت‌شدهٔ همان پک نمایش داده می‌شود؛ بدون تخفیف ساختگی.</span></article>
                <article class="wholesale-benefit"><b>موجودی قابل بررسی</b><span>افزودن پک فقط وقتی فعال است که اقلام آن موجودی کافی داشته باشند.</span></article>
                <article class="wholesale-benefit"><b>پرداخت چکی با مجوز</b><span>درخواست اعتبار ثبت می‌شود و پس از بررسی مدیر وضعیت آن مشخص خواهد شد.</span></article>
            </div>
        </div>
    </section>

    <section class="section-block wholesale-catalog" id="wholesale-catalog"><span id="packs" class="wholesale-anchor" aria-hidden="true"></span>
        <div class="container">
            <header class="wholesale-catalog__head">
                <div>
                    <span class="eyebrow">JANAN / WHOLESALE CATALOG</span>
                    <h2>پک‌های آمادهٔ عمده</h2>
                    <p>بسته‌هایی که در مدیریت ثبت شده‌اند، همراه با تصویر، اقلام، تعداد و قیمت واقعی.</p>
                </div>
                <a class="button button--ghost" href="{{ route('wholesale.catalog') }}">مشاهده کاتالوگ محصولات عمده</a>
            </header>

            @if($packs->isNotEmpty())
                <div class="wholesale-pack-grid">
                    @foreach($packs as $pack)
                        @include('pages.wholesale.pack-card', ['pack' => $pack])
                    @endforeach
                </div>
            @else
                <div class="customer-surface wholesale-empty">
                    <strong>هنوز پک آماده‌ای برای خرید عمده ثبت نشده است.</strong>
                    <span>می‌توانی محصولات دارای قیمت عمده را در کاتالوگ جداگانه ببینی.</span>
                </div>
            @endif

            <div class="customer-surface wholesale-empty">
                <strong>دنبال محصول تکی یا تنوع خاصی هستی؟</strong>
                <span>کاتالوگ محصولات عمده با جستجو، فیلتر و صفحه‌بندی مستقل در دسترس است.</span>
                <a class="button button--primary" href="{{ route('wholesale.catalog') }}">رفتن به کاتالوگ عمده</a>
            </div>
        </div>
    </section>

    <section class="section-block section-block--soft" id="cheque-application">
        <div class="container wholesale-content">
            <aside class="customer-surface wholesale-process">
                <span class="eyebrow">HOW IT WORKS</span>
                <h2 class="customer-panel-title">مسیر خرید عمده</h2>
                <ol>
                    <li><span>01</span><div><strong>انتخاب محصول</strong><p>پک موردنظر را انتخاب کن؛ قیمت و اقلام از اطلاعات ثبت‌شده در فروشگاه می‌آیند.</p></div></li>
                    <li><span>02</span><div><strong>پرداخت آنلاین</strong><p>خرید عمده آنلاین برای همه باز است و تأیید حساب نمی‌خواهد.</p></div></li>
                    <li><span>03</span><div><strong>درخواست پرداخت چکی</strong><p>با حساب مشتری، درخواست اعتبار چکی را ثبت کن.</p></div></li>
                    <li><span>04</span><div><strong>تأیید مدیر</strong><p>مدیر درخواست را بررسی می‌کند و در صورت تأیید، سقف اعتبار را در حساب ثبت می‌کند.</p></div></li>
                </ol>
            </aside>

            <section class="customer-surface wholesale-form-card" aria-labelledby="cheque-application-title">
                <header class="section-head">
                    <div>
                        <span class="eyebrow">CHEQUE PAYMENT</span>
                        <h2 id="cheque-application-title">درخواست اعتبار خرید چکی</h2>
                        <p>خرید آنلاین عمده برای همه باز است؛ این درخواست فقط برای فعال‌شدن پرداخت چکی است.</p>
                    </div>
                </header>

                @if(session('success'))
                    <div class="alert alert--success" role="status">{{ session('success') }}</div>
                @endif

                @if($chequePermission?->isApproved())
                    <div class="customer-inline-status">
                        <strong>مجوز پرداخت چکی فعال است.</strong>
                        <p>سقف هر سفارش: {{ $chequePermission->max_order_amount !== null ? number_format((float) $chequePermission->max_order_amount) . ' تومان' : 'بدون سقف تعیین‌شده' }}.</p>
                        <a class="button button--primary" href="{{ route('checkout') }}#payment-options">رفتن به پرداخت</a>
                    </div>
                @elseif($chequePermission?->isPending())
                    <div class="customer-inline-status" role="status">
                        <strong>درخواستت در انتظار بررسی مدیر است.</strong>
                        <p>مبلغ درخواستی: {{ number_format((float) $chequePermission->requested_amount) }} تومان.</p>
                    </div>
                @elseif($isCustomer)
                    <form method="POST" action="{{ route('wholesale.cheque.request') }}" class="checkout-form cheque-request-form">
                        @csrf
                        <label for="cheque-request-amount">مبلغ اعتبار درخواستی (تومان)</label>
                        <div class="cheque-request-form__amount">
                            <input id="cheque-request-amount" name="requested_amount" type="text" inputmode="numeric" autocomplete="off" required maxlength="20" value="{{ old('requested_amount') }}" placeholder="مثلاً ۵۰٬۰۰۰٬۰۰۰" data-money-input aria-describedby="cheque-request-amount-help">
                            <span>تومان</span>
                        </div>
                        <small id="cheque-request-amount-help">این مبلغ پیشنهاد توست؛ سقف نهایی را مدیر تأیید می‌کند.</small>
                        @error('requested_amount')<small class="form-error" role="alert">{{ $message }}</small>@enderror
                        <button class="button button--primary" type="submit">ارسال درخواست به مدیر</button>
                    </form>
                @elseif(auth()->check())
                    <div class="customer-inline-status"><strong>این درخواست برای حساب مشتری است.</strong><p>با حساب مدیریت امکان ثبت درخواست پرداخت چکی وجود ندارد.</p></div>
                @else
                    <div class="customer-inline-status">
                        <strong>برای پرداخت چکی، حساب مشتری بساز یا وارد شو.</strong>
                        <p>ثبت و تأیید درخواست فقط برای پرداخت چکی است؛ خرید آنلاین عمده نیازی به حساب ندارد.</p>
                        <div class="customer-action-strip__actions">
                            <a class="button button--primary" href="{{ route('login', ['continue' => 'cheque']) }}">ورود</a>
                            <a class="button button--ghost" href="{{ route('register', ['continue' => 'cheque']) }}">ساخت حساب</a>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </section>
</div>
@endsection
