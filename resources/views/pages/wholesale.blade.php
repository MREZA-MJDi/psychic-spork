@extends('layouts.store')

@section('title', 'خرید عمده — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="wholesale-page">

    <section class="section-block section-block--compact">
        <div class="container">
            <div class="wholesale-hero">
                <div class="customer-surface wholesale-hero__main">
                    <span class="eyebrow">JANAN / WHOLESALE</span>
                    <h1>برای خرید عمده، یک مسیر حرفه‌ای داشته باش.</h1>
                    <p>
                        حساب عمده برای فروشگاه‌ها و مجموعه‌های تجاری طراحی شده است.
                        درخواستت را ثبت کن، بعد از بررسی مدیریت شرایط اختصاصی حساب برایت فعال می‌شود.
                    </p>

                    <div class="customer-action-strip customer-action-strip--spaced-top">
                        <div class="customer-action-strip__copy">
                            <small>YOUR BUSINESS ACCESS</small>
                            <strong>وضعیت حساب را همین‌جا ببین.</strong>
                        </div>
                        <div class="customer-action-strip__actions">
                            <a class="button button--ghost" href="{{ route('products.index') }}">دیدن کاتالوگ</a>
                            @if($profile?->isApproved())
                                <a class="button button--primary" href="{{ route('checkout') }}">شروع سفارش</a>
                            @else
                                <a class="button button--primary" href="#wholesale-form">درخواست دسترسی</a>
                            @endif
                        </div>
                    </div>
                </div>

                <aside class="customer-surface wholesale-hero__side wholesale-status">
                    <span class="wholesale-status__label">CURRENT STATUS</span>

                    @if(session('success'))
                        <div class="alert alert--success" role="status">{{ session('success') }}</div>
                    @endif

                    @if($profile?->isApproved())
                        <div class="wholesale-status__state">فعال</div>
                        <div class="wholesale-status__hint">
                            حساب عمده شما تأیید شده و می‌توانید از شرایط عمده برای سفارش استفاده کنید.
                        </div>
                    @elseif($profile?->status === 'pending')
                        <div class="wholesale-status__state">در حال بررسی</div>
                        <div class="wholesale-status__hint">
                            درخواست ثبت شده و بعد از بررسی مدیریت، نتیجه از همین حساب قابل پیگیری است.
                        </div>
                    @elseif($profile?->status === 'suspended')
                        <div class="wholesale-status__state">تعلیق‌شده</div>
                        <div class="wholesale-status__hint">
                            دسترسی عمده این حساب فعلاً متوقف شده است. برای پیگیری با پشتیبانی ارتباط بگیر.
                        </div>
                    @else
                        <div class="wholesale-status__state">هنوز فعال نیست</div>
                        <div class="wholesale-status__hint">
                            فرم پایین را کامل کن تا بررسی دسترسی عمده شروع شود.
                        </div>
                    @endif
                </aside>
            </div>

            <div class="wholesale-benefit-grid">
                <article class="wholesale-benefit">
                    <span>01</span>
                    <div>
                        <strong>قیمت اختصاصی</strong>
                        <p>قیمت‌گذاری عمده فقط روی حساب و محصولاتی اعمال می‌شود که شرایط آن را دارند.</p>
                    </div>
                </article>

                <article class="wholesale-benefit">
                    <span>02</span>
                    <div>
                        <strong>شرایط حساب</strong>
                        <p>حداقل مبلغ و تعداد سفارش از پروفایل عمده حساب شما خوانده می‌شود.</p>
                    </div>
                </article>

                <article class="wholesale-benefit">
                    <span>03</span>
                    <div>
                        <strong>فرآیند کنترل‌شده</strong>
                        <p>تأیید دسترسی توسط مدیریت انجام می‌شود و وضعیت حساب شفاف باقی می‌ماند.</p>
                    </div>
                </article>

                <article class="wholesale-benefit">
                    <span>04</span>
                    <div>
                        <strong>سفارش واقعی</strong>
                        <p>بعد از تأیید، سفارش عمده از همان checkout فروشگاه ثبت می‌شود.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section-block section-block--soft" id="wholesale-form">
        <div class="container wholesale-content">
            <section class="customer-surface wholesale-form-card">
                <header class="section-head">
                    <div>
                        <span class="eyebrow">BUSINESS PROFILE</span>
                        <h2>{{ $profile?->isApproved() ? 'اطلاعات حساب عمده' : 'درخواست دسترسی عمده' }}</h2>
                        <p>
                            اطلاعات کسب‌وکار را دقیق وارد کن تا بررسی سریع‌تر و بدون رفت‌وبرگشت انجام شود.
                        </p>
                    </div>
                </header>

                @if($profile?->isApproved())
                    <div class="customer-action-strip">
                        <div class="customer-action-strip__copy">
                            <small>WHOLESALE ACCOUNT</small>
                            <strong>حساب شما آماده سفارش عمده است.</strong>
                        </div>
                        <div class="customer-action-strip__actions">
                            <a class="button button--primary" href="{{ route('products.index') }}">انتخاب محصولات</a>
                            <a class="button button--ghost" href="{{ route('checkout') }}">رفتن به تسویه</a>
                        </div>
                    </div>
                @elseif($profile?->status === 'pending')
                    <div class="customer-inline-status">
                        <strong>درخواست شما قبلاً ثبت شده است.</strong>
                        <p>
                            تا مشخص شدن نتیجه، ارسال دوباره درخواست لازم نیست.
                        </p>
                    </div>
                @else
                    <form method="POST" action="{{ route('wholesale.apply') }}" class="checkout-form">
                        @csrf

                        <div class="form-grid">
                            <label>
                                نام فروشگاه یا مجموعه *
                                <input name="business_name" value="{{ old('business_name', $profile?->business_name) }}" required autocomplete="organization">
                            </label>

                            <label>
                                نوع فعالیت
                                <input name="business_type" value="{{ old('business_type', $profile?->business_type) }}">
                            </label>

                            <label>
                                شماره تماس کاری
                                <input name="business_phone" value="{{ old('business_phone', $profile?->business_phone) }}" inputmode="tel" autocomplete="tel">
                            </label>

                            <label class="form-grid__full">
                                آدرس کاری
                                <textarea name="business_address" rows="4">{{ old('business_address', $profile?->business_address) }}</textarea>
                            </label>
                        </div>

                        <button class="button button--primary" type="submit">
                            ارسال درخواست خرید عمده
                            <span aria-hidden="true">↗</span>
                        </button>
                    </form>
                @endif
            </section>

            <aside class="customer-surface wholesale-process">
                <span class="eyebrow">HOW IT WORKS</span>
                <h2 class="customer-panel-title">چهار قدم تا خرید عمده</h2>

                <ol>
                    <li>
                        <span>01</span>
                        <div>
                            <strong>ثبت اطلاعات</strong>
                            <p>فرم کسب‌وکار را کامل و دقیق ارسال کن.</p>
                        </div>
                    </li>
                    <li>
                        <span>02</span>
                        <div>
                            <strong>بررسی مدیریت</strong>
                            <p>اطلاعات برای فعال‌سازی حساب عمده بررسی می‌شود.</p>
                        </div>
                    </li>
                    <li>
                        <span>03</span>
                        <div>
                            <strong>فعال‌سازی</strong>
                            <p>بعد از تأیید، قیمت‌ها و شرایط عمده قابل استفاده می‌شوند.</p>
                        </div>
                    </li>
                    <li>
                        <span>04</span>
                        <div>
                            <strong>ثبت سفارش</strong>
                            <p>محصولات را انتخاب کن و checkout را با نوع خرید عمده کامل کن.</p>
                        </div>
                    </li>
                </ol>
            </aside>
        </div>
    </section>

</div>
@endsection
