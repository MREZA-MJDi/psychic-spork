@extends('layouts.store')

@section('content')
<div class="store-page contact-page">

    <section class="page-hero page-hero--premium contact-page__hero">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / CONTACT</span>
                <h1>
                    یک پیام،
                    <br>
                    <em>یک شروع خوب.</em>
                </h1>
                <p>
                    سوالی درباره سفارش داری، قبل از خرید نیاز به راهنمایی داری یا می‌خواهی موضوعی را با پشتیبانی در میان بگذاری؟
                    از همین‌جا پیام بفرست.
                </p>
            </div>

            <div class="about-page__mark contact-page__mark" aria-hidden="true">
                <span>↗</span>
                <small>SUPPORT<br>CHANNEL</small>
            </div>
        </div>
    </section>

    <section class="store-page__section">
        <div class="container">

            @if(session('success'))
                <div class="contact-alert contact-alert--success" role="status">
                    <strong>پیام ثبت شد.</strong>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="contact-alert contact-alert--error" role="alert">
                    <strong>ارسال انجام نشد.</strong>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="contact-alert contact-alert--error" role="alert">
                    <strong>اطلاعات فرم را بررسی کن.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="contact-layout">

                <section class="contact-form-card">
                    <div class="contact-form-card__head">
                        <div>
                            <span class="eyebrow">SEND A MESSAGE</span>
                            <h2>در چه موردی کمکت کنیم؟</h2>
                            <p>
                                اطلاعات تماس را کامل کن تا پیام بدون واسطه وارد سیستم پشتیبانی جانان شود.
                            </p>
                        </div>

                        <span class="contact-form-card__count">01</span>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('contact.submit') }}"
                        class="contact-form"
                    >
                        @csrf

                        <div class="contact-form__grid">
                            <label class="contact-field">
                                <span>نام و نام خانوادگی <b>*</b></span>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name', auth()->user()?->name) }}"
                                    autocomplete="name"
                                    required
                                >
                            </label>

                            <label class="contact-field">
                                <span>ایمیل</span>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email', auth()->user()?->email) }}"
                                    autocomplete="email"
                                >
                            </label>

                            <label class="contact-field">
                                <span>شماره تماس</span>
                                <input
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone', auth()->user()?->phone) }}"
                                    autocomplete="tel"
                                    dir="ltr"
                                >
                            </label>

                            <label class="contact-field">
                                <span>موضوع</span>
                                <input
                                    type="text"
                                    name="subject"
                                    value="{{ old('subject') }}"
                                    autocomplete="off"
                                >
                            </label>

                            <label class="contact-field contact-field--full">
                                <span>پیام <b>*</b></span>
                                <textarea
                                    name="message"
                                    rows="8"
                                    maxlength="5000"
                                    required
                                >{{ old('message') }}</textarea>
                            </label>
                        </div>

                        <div class="contact-form__foot">
                            <p>
                                اطلاعات ارسالی فقط برای پیگیری همین درخواست استفاده می‌شود.
                            </p>

                            <button class="button button--primary" type="submit">
                                ارسال پیام
                                <span aria-hidden="true">↗</span>
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="contact-info-card">
                    <div>
                        <span class="eyebrow">JANAN / SUPPORT</span>
                        <h2>راه‌های ارتباط</h2>
                        <p>
                            هر روشی که برایت راحت‌تر است، از همان مسیر با جانان در ارتباط باش.
                        </p>
                    </div>

                    <div class="contact-info-list">

                        <div class="contact-info-item">
                            <span>01</span>
                            <div>
                                <small>PHONE</small>
                                @if($contactStore['phone'] ?? null)
                                    <a href="tel:{{ preg_replace('/\\s+/', '', $contactStore['phone']) }}" dir="ltr">
                                        {{ $contactStore['phone'] }}
                                    </a>
                                @else
                                    <strong>شماره تماس ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <span>02</span>
                            <div>
                                <small>EMAIL</small>
                                @if($contactStore['email'] ?? null)
                                    <a href="mailto:{{ $contactStore['email'] }}" dir="ltr">
                                        {{ $contactStore['email'] }}
                                    </a>
                                @else
                                    <strong>ایمیل ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <span>03</span>
                            <div>
                                <small>ADDRESS</small>
                                @if($contactStore['address'] ?? null)
                                    <strong>{{ $contactStore['address'] }}</strong>
                                @else
                                    <strong>آدرس ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <span>04</span>
                            <div>
                                <small>WORKING HOURS</small>
                                @if($contactStore['working_hours'] ?? null)
                                    <strong>{{ $contactStore['working_hours'] }}</strong>
                                @else
                                    <strong>ساعات پاسخگویی ثبت نشده</strong>
                                @endif
                            </div>
                        </div>

                    </div>

                    <div class="contact-info-card__note">
                        <span aria-hidden="true">↗</span>
                        <p>
                            اگر درباره یک سفارش مشخص پیام می‌دهی، موضوع یا شماره سفارش را هم بنویس تا پیگیری سریع‌تر شود.
                        </p>
                    </div>
                </aside>

            </div>
        </div>
    </section>

    <section class="store-page__section store-page__section--soft">
        <div class="container">
            <div class="contact-next">
                <div>
                    <span class="eyebrow">BEFORE YOU SEND</span>
                    <h2>برای پاسخ بهتر، دقیق بنویس.</h2>
                    <p>
                        موضوع کوتاه، توضیح روشن و شماره سفارش در صورت وجود، کمک می‌کند درخواستت سریع‌تر بررسی شود.
                    </p>
                </div>

                <a class="button button--dark" href="{{ route('faq') }}">
                    سوالات متداول
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
