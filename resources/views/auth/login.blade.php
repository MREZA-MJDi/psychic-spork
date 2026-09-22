<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ورود — JANAN</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css'])
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-card">
            <a href="{{ url('/') }}" class="auth-brand" aria-label="Janan">
                <span class="auth-brand__latin">janan</span>
                <span class="auth-brand__fa">جانان</span>
            </a>

            <div class="auth-intro">
                <span class="eyebrow">WELCOME BACK</span>
                <h1>خوش برگشتی</h1>
                <p>برای ادامه خرید، وارد حساب کاربری خودت شو.</p>
            </div>

            @if(session('success'))
                <div class="auth-alert auth-alert--success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="auth-alert auth-alert--error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf

                <label>
                    ایمیل
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                </label>

                <label>
                    رمز عبور
                    <input type="password" name="password" placeholder="رمز عبور" autocomplete="current-password" required>
                </label>

                <div class="auth-form__row">
                    <label class="auth-check">
                        <input type="checkbox" name="remember" value="1">
                        <span>مرا به خاطر بسپار</span>
                    </label>
                    <a href="{{ url('/') }}">بازگشت به فروشگاه</a>
                </div>

                <button class="auth-button" type="submit">ورود به جانان</button>
            </form>

            <p class="auth-switch">
                حساب نداری؟
                <a href="{{ route('register') }}">ثبت‌نام کن</a>
            </p>
        </section>

        <aside class="auth-visual" aria-hidden="true">
            <div class="auth-visual__orb auth-visual__orb--one"></div>
            <div class="auth-visual__orb auth-visual__orb--two"></div>
            <div class="auth-visual__veil"></div>

            <div class="auth-visual__frame">
                <span class="auth-visual__frame-index">01 / JANAN</span>
                <span class="auth-visual__monogram">J</span>
                <span class="auth-visual__nasta">کالکشن جانان</span>
                <span class="auth-visual__frame-caption">EST. 2025 · INNER / FORM / FEMININE</span>
            </div>

            <div class="auth-visual__copy">
                <span>JANAN / PRIVATE STORE</span>
                <strong>برای خودت<br>انتخاب کن.</strong>
                <small>AUTHENTICATE / SHOP / LOVE</small>
                <div class="auth-visual__line"><span>اینجا انتخاب، شخصی است.</span></div>
            </div>
        </aside>
    </main>
</body>
</html>
