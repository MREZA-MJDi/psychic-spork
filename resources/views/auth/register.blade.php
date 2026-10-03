<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>ثبت‌نام — {{ config('app.store_name', 'JANAN') }}</title>

    @vite([
        'resources/css/auth.css',
        'resources/js/app.js',
    ])
</head>

<body class="auth-page" data-auth-page="register">
    <main class="auth-app">
        <header class="auth-topbar">
            <a href="{{ route('home') }}" class="auth-brand" aria-label="بازگشت به جانان">
                <span class="auth-brand__mark" aria-hidden="true">J</span>
                <span>
                    <strong>janan</strong>
                    <small>STORE / PRIVATE ACCESS</small>
                </span>
            </a>

            <div class="auth-topbar__meta" aria-label="وضعیت سامانه">
                <span class="auth-status"><i aria-hidden="true"></i> ثبت‌نام مشتری</span>
                <span class="auth-index">02 / 02</span>
            </div>
        </header>

        <div class="auth-canvas">
            <div class="auth-grid" aria-hidden="true"></div>
            <div class="auth-orbit auth-orbit--one" aria-hidden="true"></div>
            <div class="auth-orbit auth-orbit--two" aria-hidden="true"></div>

            <section class="auth-workspace" aria-labelledby="register-title">
                <div class="auth-workspace__rail" aria-hidden="true">
                    <span>AUTH</span>
                    <span>02</span>
                    <span class="auth-workspace__line"></span>
                    <span>JANAN</span>
                </div>

                <div class="auth-panel">
                    <div class="auth-panel__head">
                        <div>
                            <span class="auth-kicker">CREATE / ACCOUNT</span>
                            <h1 id="register-title">فضای خودت را بساز.</h1>
                        </div>

                        <span class="auth-panel__code">A-02</span>
                    </div>

                    <p class="auth-lead">
                        با یک حساب جانان، سفارش‌ها، پروفایل و مسیر خریدت را یک‌جا مدیریت کن.
                    </p>

                    @if($errors->any())
                        <div class="auth-alert auth-alert--error" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="auth-alert auth-alert--error" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                        @csrf

                        <label class="auth-field">
                            <span class="auth-field__label">
                                نام و نام خانوادگی
                                <small>YOUR NAME</small>
                            </span>
                            <div class="auth-input-wrap">
                                <span class="auth-input-index" aria-hidden="true">01</span>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="مثلاً سارا احمدی"
                                    autocomplete="name"
                                    maxlength="120"
                                    required
                                    autofocus
                                >
                            </div>
                        </label>

                        <label class="auth-field">
                            <span class="auth-field__label">
                                    شماره موبایل
                                    <small>MOBILE NUMBER</small>
                            </span>
                            <div class="auth-input-wrap">
                                <span class="auth-input-index" aria-hidden="true">02</span>
                                <input
                                    type="tel"
                                    name="phone"
                                    value="{{ old('phone') }}"
                                    placeholder="09123456789"
                                    autocomplete="tel"
                                    inputmode="tel"
                                    maxlength="16"
                                    required
                                >
                            </div>
                        </label>

                        <label class="auth-field">
                            <span class="auth-field__label">
                                ایمیل
                                <small>OPTIONAL</small>
                            </span>
                            <div class="auth-input-wrap">
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="برای اطلاع‌رسانی (اختیاری)"
                                    autocomplete="email"
                                >
                            </div>
                        </label>

                        <div class="auth-form__two-col">
                            <label class="auth-field">
                                <span class="auth-field__label">
                                    رمز عبور
                                    <small>SECURE PASSWORD</small>
                                </span>
                                <div class="auth-input-wrap auth-password-wrap">
                                    <input
                                        type="password"
                                        name="password"
                                        placeholder="حداقل ۸ کاراکتر"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                        data-password-input
                                        data-password-meter-source
                                    >
                                    <button
                                        class="auth-password-toggle"
                                        type="button"
                                        data-password-toggle
                                        aria-label="نمایش رمز عبور"
                                        aria-pressed="false"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="auth-meter" aria-hidden="true"><span data-password-meter></span></div>
                            </label>

                            <label class="auth-field">
                                <span class="auth-field__label">
                                    تکرار رمز
                                    <small>CONFIRM</small>
                                </span>
                                <div class="auth-input-wrap auth-password-wrap">
                                    <input
                                        type="password"
                                        name="password_confirmation"
                                        placeholder="تکرار رمز"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                        data-password-input
                                    >
                                    <button
                                        class="auth-password-toggle"
                                        type="button"
                                        data-password-toggle
                                        aria-label="نمایش رمز عبور"
                                        aria-pressed="false"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/>
                                            <circle cx="12" cy="12" r="2.5"/>
                                        </svg>
                                    </button>
                                </div>
                            </label>
                        </div>

                        <button class="auth-button" type="submit">
                            <span>ساخت حساب</span>
                            <b aria-hidden="true">↗</b>
                        </button>
                    </form>

                    <footer class="auth-panel__foot">
                        <span>NEW ACCOUNT / CUSTOMER ACCESS</span>
                        <span>قبلاً حساب ساختی؟ <a href="{{ route('login', request()->query()) }}">وارد شو</a></span>
                    </footer>
                </div>

                <aside class="auth-context" aria-label="مسیرهای دسترسی">
                    <img src="{{ asset('images/auth.webp') }}" alt="" aria-hidden="true" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0">
                    <span class="auth-context__kicker">START HERE</span>
                    <strong>یک حساب،<br>یک تجربه شخصی.</strong>

                    <div class="auth-context__items">
                        <a href="{{ route('login', request()->query()) }}" class="auth-context__item">
                            <span>01</span>
                            <div>
                                <strong>ورود</strong>
                                <small>حساب مشتری / مدیریت</small>
                            </div>
                            <b aria-hidden="true">↗</b>
                        </a>

                        <div class="auth-context__item is-active">
                            <span>02</span>
                            <div>
                                <strong>ثبت‌نام</strong>
                                <small>شروع با حساب مشتری</small>
                            </div>
                            <b aria-hidden="true">↗</b>
                        </div>
                    </div>

                    <div class="auth-context__note">
                        <i aria-hidden="true"></i>
                        <span>حساب‌های جدید به‌صورت پیش‌فرض مشتری هستند و به بخش مدیریت دسترسی ندارند.</span>
                    </div>
                </aside>
            </section>
        </div>
    </main>
</body>
</html>
