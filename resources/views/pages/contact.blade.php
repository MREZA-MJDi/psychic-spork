@extends('layouts.store')

@section('content')
<div class="store-page">

    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / CONTACT</span>
                <h1>با جانان<br><em>در ارتباط باش.</em></h1>
                <p>
                    برای پشتیبانی، پیگیری یا ارسال پیام، فرم زیر را کامل کن.
                    اطلاعات تماس فروشگاه فقط از تنظیمات واقعی نمایش داده می‌شود.
                </p>
            </div>

            <div class="page-hero__stat">
                <b>04</b>
                <span>CONTACT / SUPPORT</span>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container store-page__grid store-page__grid--two">

            <section class="store-page__card">
                <span class="eyebrow">CONTACT FORM</span>
                <h2>پیامت را بفرست.</h2>

                <form
                    method="POST"
                    action="{{ route('contact.submit') }}"
                    class="checkout-form"
                >
                    @csrf

                    <div class="form-grid">
                        <label>
                            نام و نام خانوادگی
                            <input name="name" value="{{ old('name', auth()->user()?->name) }}" required>
                        </label>

                        <label>
                            ایمیل
                            <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}">
                        </label>

                        <label>
                            شماره تماس
                            <input name="phone" value="{{ old('phone', auth()->user()?->phone) }}">
                        </label>

                        <label>
                            موضوع
                            <input name="subject" value="{{ old('subject') }}">
                        </label>

                        <label class="form-grid__full">
                            پیام
                            <textarea name="message" rows="7" required>{{ old('message') }}</textarea>
                        </label>
                    </div>

                    <button class="button button--primary" type="submit">
                        ارسال پیام
                        <span aria-hidden="true">↗</span>
                    </button>
                </form>
            </section>

            <aside class="store-page__card store-page__card--dark">
                <span class="eyebrow">JANAN / STUDIO</span>
                <h2>راه‌های ارتباط</h2>

                <div class="store-page__steps">
                    @if($contactStore['phone'] ?? null)
                        <div class="store-page__step">
                            <b>01</b>
                            <div>
                                <strong>تلفن</strong>
                                <p dir="ltr">{{ $contactStore['phone'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if($contactStore['email'] ?? null)
                        <div class="store-page__step">
                            <b>02</b>
                            <div>
                                <strong>ایمیل</strong>
                                <p dir="ltr">{{ $contactStore['email'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if($contactStore['address'] ?? null)
                        <div class="store-page__step">
                            <b>03</b>
                            <div>
                                <strong>آدرس</strong>
                                <p>{{ $contactStore['address'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if($contactStore['working_hours'] ?? null)
                        <div class="store-page__step">
                            <b>04</b>
                            <div>
                                <strong>ساعات پاسخگویی</strong>
                                <p>{{ $contactStore['working_hours'] }}</p>
                            </div>
                        </div>
                    @endif

                    @if(!array_filter($contactStore ?? []))
                        <div class="store-page__step">
                            <b>—</b>
                            <div>
                                <strong>اطلاعات تماس هنوز پیکربندی نشده است.</strong>
                                <p>مقادیر واقعی را در تنظیمات محیطی فروشگاه ثبت کن.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </aside>

        </div>
    </section>
</div>
@endsection
