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

<body class="auth-page">
    @php
        $isLocalDemo = app()->environment(['local', 'testing']);
        $demoEmail = config('app.admin.email');
        $demoPassword = config('app.admin.password');
    @endphp

    <main class="auth-shell">

        <section class="auth-card" aria-labelledby="login-title">

            <div class="auth-card__top">
                <a
                    href="{{ route('home') }}"
                    class="auth-brand"
                    aria-label="{{ config('app.store_name', 'Janan') }}"
                >
                    <span class="auth-brand__latin">janan</span>
                    <span class="auth-brand__fa">جانان</span>
                </a>

                <span class="auth-card__edition">
                    PRIVATE / {{ date('Y') }}
                </span>
            </div>

            <div class="auth-intro">
                <span class="eyebrow">WELCOME BACK</span>

                <h1 id="login-title">
                    خوش برگشتی.
                </h1>

                <p>
                    برای ادامه خرید یا ورود به داشبورد مدیریت، اطلاعات حساب را وارد کن.
                </p>
            </div>

            @if(session('success'))
                <div
                    class="auth-alert auth-alert--success"
                    role="status"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div
                    class="auth-alert auth-alert--error"
                    role="alert"
                >
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div
                    class="auth-alert auth-alert--error"
                    role="alert"
                >
                    {{ $errors->first() }}
                </div>
            @endif

            @if($isLocalDemo && $demoEmail && $demoPassword)
                <div class="auth-demo" role="note">
                    <div class="auth-demo__head">
                        <span class="auth-demo__badge">LOCAL DEMO</span>
                        <strong>حساب تست مدیریت</strong>
                    </div>

                    <div class="auth-demo__grid">
                        <div>
                            <small>USERNAME / EMAIL</small>
                            <code>{{ $demoEmail }}</code>
                        </div>

                        <div>
                            <small>PASSWORD</small>
                            <code>{{ $demoPassword }}</code>
                        </div>
                    </div>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('login.store') }}"
                class="auth-form"
            >
                @csrf

                <label class="auth-field">
                    <span>
                        ایمیل یا نام کاربری
                        <small class="auth-field__hint">IDENTIFIER</small>
                    </span>

                    <div class="auth-input-wrap">
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
                    <span>
                        رمز عبور
                        <small class="auth-field__hint">PASSWORD</small>
                    </span>

                    <div class="auth-input-wrap auth-password-wrap">
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
                        <input
                            type="checkbox"
                            name="remember"
                            value="1"
                        >
                        <span>مرا به خاطر بسپار</span>
                    </label>

                    <a href="{{ route('home') }}">
                        بازگشت به فروشگاه
                    </a>
                </div>

                <button
                    class="auth-button"
                    type="submit"
                >
                    ورود به جانان
                    <span aria-hidden="true">↗</span>
                </button>
            </form>

            <div class="auth-divider">
                <span></span>
                <small>OR</small>
                <span></span>
            </div>

            <p class="auth-switch">
                حساب مشتری نداری؟
                <a href="{{ route('register') }}">ساخت حساب جدید</a>
            </p>

        </section>

        <aside
            class="auth-visual"
            aria-hidden="true"
        >
            <div class="auth-visual__orb auth-visual__orb--one"></div>
            <div class="auth-visual__orb auth-visual__orb--two"></div>
            <div class="auth-visual__veil"></div>

            <div class="auth-visual__frame">
                <span class="auth-visual__frame-index">01 / JANAN</span>
                <span class="auth-visual__monogram">J</span>
                <span class="auth-visual__nasta">کالکشن جانان</span>
                <span class="auth-visual__frame-caption">PRIVATE ACCESS · STORE / ADMIN</span>
            </div>

            <div class="auth-visual__copy">
                <span>JANAN / PRIVATE STORE</span>
                <strong>برای خودت<br>انتخاب کن.</strong>
                <small>AUTHENTICATE / SHOP / MANAGE</small>
                <div class="auth-visual__line">
                    <span>یک ورود ساده، برای یک تجربه دقیق.</span>
                </div>
            </div>
        </aside>

    </main>
</body>
</html>