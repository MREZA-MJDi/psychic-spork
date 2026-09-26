<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ثبت‌نام — JANAN</title>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="auth-page" data-auth-page>
<main class="auth-shell">
    <section class="auth-card">
        <a href="{{ route('home') }}" class="auth-brand" aria-label="Janan">
            <span class="auth-brand__latin">janan</span>
            <span class="auth-brand__fa">جانان</span>
        </a>

        <div class="auth-intro">
            <span class="eyebrow">JOIN JANAN / NEW ACCOUNT</span>
            <h1>حساب جدید بساز.</h1>
            <p>یک فضای شخصی برای انتخاب‌های بعدی‌ات بساز؛ اطلاعات حساب به‌صورت واقعی در سیستم ذخیره می‌شوند.</p>
        </div>

        @if($errors->any())
            <div class="auth-alert auth-alert--error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="auth-form">
            @csrf

            <label class="auth-field">
                <span>نام و نام خانوادگی <small class="auth-field__hint">YOUR NAME</small></span>
                <div class="auth-input-wrap">
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="مثلاً سارا احمدی" autocomplete="name" maxlength="120" required autofocus>
                </div>
            </label>

            <label class="auth-field">
                <span>ایمیل <small class="auth-field__hint">EMAIL</small></span>
                <div class="auth-input-wrap">
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
                </div>
            </label>

            <label class="auth-field">
                <span>رمز عبور <small class="auth-field__hint">SECURE PASSWORD</small></span>
                <div class="auth-input-wrap auth-password-wrap">
                    <input type="password" name="password" placeholder="رمز امن" autocomplete="new-password" minlength="8" required data-password-input data-password-meter-source>
                    <button class="auth-password-toggle" type="button" data-password-toggle aria-label="نمایش رمز عبور" aria-pressed="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                    </button>
                </div>
                <div class="auth-meter" aria-hidden="true"><span class="auth-meter__bar" data-password-meter></span></div>
            </label>

            <label class="auth-field">
                <span>تکرار رمز عبور <small class="auth-field__hint">CONFIRM</small></span>
                <div class="auth-input-wrap auth-password-wrap">
                    <input type="password" name="password_confirmation" placeholder="تکرار رمز عبور" autocomplete="new-password" minlength="8" required data-password-input>
                    <button class="auth-password-toggle" type="button" data-password-toggle aria-label="نمایش رمز عبور" aria-pressed="false">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                    </button>
                </div>
            </label>

            <button class="auth-button" type="submit">ساخت حساب جانان <span aria-hidden="true">↗</span></button>
        </form>

        <p class="auth-switch">
            قبلاً حساب ساختی؟
            <a href="{{ route('login') }}">وارد شو</a>
        </p>
    </section>

    <aside class="auth-visual" aria-hidden="true">
        <span class="auth-visual__orb auth-visual__orb--one"></span>
        <span class="auth-visual__orb auth-visual__orb--two"></span>
        <div class="auth-visual__veil"></div>
        <div class="auth-visual__copy">
            <span>JANAN / YOUR NEXT CHAPTER</span>
            <strong>شروع یک<br>انتخاب تازه.</strong>
            <small>CREATE / DISCOVER / WEAR</small>
            <div class="auth-visual__line">A SOFT INTERFACE WITH SHARP DETAILS</div>
        </div>
    </aside>
</main>
</body>
</html>