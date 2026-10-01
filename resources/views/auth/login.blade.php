<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>ورود — {{ config('app.store_name', 'JANAN') }}</title>

    @vite([
        'resources/css/auth.css',
        'resources/js/app.js',
    ])
</head>

<body class="auth-page" data-auth-page="login">
    @php
        $isLocalDemo = app()->environment(['local', 'testing']);
        $demoEmail = config('app.admin.email');
        $demoPassword = config('app.admin.password');
    @endphp

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
                <span class="auth-status"><i aria-hidden="true"></i> سیستم فعال</span>
                <span class="auth-index">01 / 02</span>
            </div>
        </header>

        <div class="auth-canvas">
            <div class="auth-grid" aria-hidden="true"></div>
            <div class="auth-orbit auth-orbit--one" aria-hidden="true"></div>
            <div class="auth-orbit auth-orbit--two" aria-hidden="true"></div>

            <section class="auth-workspace" aria-labelledby="login-title">
                <div class="auth-workspace__rail" aria-hidden="true">
                    <span>AUTH</span>
                    <span>01</span>
                    <span class="auth-workspace__line"></span>
                    <span>JANAN</span>
                </div>

                <div class="auth-panel">
                    <div class="auth-panel__head">
                        <div>
                            <span class="auth-kicker">WELCOME / BACK</span>
                            <h1 id="login-title">خوش برگشتی.</h1>
                        </div>

                        <span class="auth-panel__code">A-01</span>
                    </div>

                    <p class="auth-lead">
                        یک ورود، دو مسیر: حساب مشتری برای خرید و حساب مدیریت برای کنترل فروشگاه.
                    </p>

                    @if(session('success'))
                        <div class="auth-alert auth-alert--success" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="auth-alert auth-alert--error" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="auth-alert auth-alert--error" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if($isLocalDemo && $demoEmail && $demoPassword)
                        <section class="auth-demo" aria-label="دسترسی سریع مدیریت">
                            <div class="auth-demo__top">
                                <div>
                                    <span class="auth-demo__eyebrow">LOCAL CONTROL</span>
                                    <strong>ورود سریع به داشبورد</strong>
                                </div>
                                <button type="button" class="auth-demo__fill" data-demo-fill>
                                    پر کردن خودکار
                                    <span aria-hidden="true">↗</span>
                                </button>
                            </div>

                            <div class="auth-demo__credentials">
                                <div class="auth-demo__credential">
                                    <span>EMAIL</span>
                                    <code data-demo-email>{{ $demoEmail }}</code>
                                    <button type="button" data-copy-target="email" aria-label="کپی ایمیل مدیریت">کپی</button>
                                </div>
                                <div class="auth-demo__credential">
                                    <span>PASSWORD</span>
                                    <code data-demo-password>{{ $demoPassword }}</code>
                                    <button type="button" data-copy-target="password" aria-label="کپی رمز مدیریت">کپی</button>
                                </div>
                            </div>
                        </section>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                        @csrf

                        <label class="auth-field">
                            <span class="auth-field__label">
                                ایمیل یا نام کاربری
                                <small>IDENTIFIER</small>
                            </span>

                            <div class="auth-input-wrap">
                                <span class="auth-input-index" aria-hidden="true">01</span>
                                <input
                                    type="text"
                                    name="identifier"
                                    value="{{ old('identifier', $isLocalDemo ? $demoEmail : '') }}"
                                    placeholder="you@example.com"
                                    autocomplete="username"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    maxlength="255"
                                    required
                                    autofocus
                                >
                            </div>
                        </label>

                        <label class="auth-field">
                            <span class="auth-field__label">
                                رمز عبور
                                <small>PASSWORD</small>
                            </span>

                            <div class="auth-input-wrap auth-password-wrap">
                                <span class="auth-input-index" aria-hidden="true">02</span>
                                <input
                                    type="password"
                                    name="password"
                                    value="{{ $isLocalDemo ? $demoPassword : '' }}"
                                    placeholder="رمز عبور"
                                    autocomplete="current-password"
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

                        <div class="auth-form__row">
                            <label class="auth-check">
                                <input type="checkbox" name="remember" value="1">
                                <span>مرا به خاطر بسپار</span>
                            </label>

                            <a href="{{ route('home') }}">بازگشت به فروشگاه</a>
                        </div>

                        <button class="auth-button" type="submit">
                            <span>ورود به جانان</span>
                            <b aria-hidden="true">↗</b>
                        </button>
                    </form>

                    <footer class="auth-panel__foot">
                        <span>SECURE SESSION / CSRF PROTECTED</span>
                        <span>حساب مشتری نداری؟ <a href="{{ route('register') }}">حساب جدید بساز</a></span>
                    </footer>
                </div>

                <aside class="auth-context" aria-label="مسیرهای دسترسی">
                    <span class="auth-context__kicker">CHOOSE YOUR SPACE</span>
                    <strong>فضای درست<br>برای کار درست.</strong>

                    <div class="auth-context__items">
                        <div class="auth-context__item is-active">
                            <span>01</span>
                            <div>
                                <strong>ورود</strong>
                                <small>مشتری / مدیریت</small>
                            </div>
                            <b aria-hidden="true">↗</b>
                        </div>

                        <a href="{{ route('register') }}" class="auth-context__item">
                            <span>02</span>
                            <div>
                                <strong>ثبت‌نام</strong>
                                <small>ساخت حساب مشتری</small>
                            </div>
                            <b aria-hidden="true">↗</b>
                        </a>
                    </div>

                    <div class="auth-context__note">
                        <i aria-hidden="true"></i>
                        <span>مجوزهای مدیریتی فقط برای حساب‌هایی که <b>is_admin</b> فعال دارند برقرار است.</span>
                    </div>
                </aside>
            </section>
        </div>
    </main>
</body>
</html>
