<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>ورود — {{ config('app.store_name', 'JANAN') }}</title>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="auth-page" data-auth-page="login">
<main class="auth-app">
    <header class="auth-topbar">
        <a href="{{ route('home') }}" class="auth-brand" aria-label="بازگشت به جانان">
            <span class="auth-brand__mark" aria-hidden="true">J</span>
            <span><strong>janan</strong><small>STORE / PRIVATE ACCESS</small></span>
        </a>
        <div class="auth-topbar__meta"><span class="auth-status"><i aria-hidden="true"></i> ورود امن</span><span class="auth-index">01 / 02</span></div>
    </header>
    <div class="auth-canvas">
        <div class="auth-grid" aria-hidden="true"></div><div class="auth-orbit auth-orbit--one" aria-hidden="true"></div><div class="auth-orbit auth-orbit--two" aria-hidden="true"></div>
        <section class="auth-workspace" aria-labelledby="login-title">
            <div class="auth-workspace__rail" aria-hidden="true"><span>AUTH</span><span>01</span><span class="auth-workspace__line"></span><span>JANAN</span></div>
            <div class="auth-panel">
                <div class="auth-panel__head"><div><span class="auth-kicker">SIGN IN / ACCOUNT</span><h1 id="login-title">خوش برگشتی.</h1></div><span class="auth-panel__code">A-01</span></div>
                <p class="auth-lead">برای دیدن سفارش‌ها و ادامه مسیر خریدت وارد حساب جانان شو.</p>
                @if($errors->any())<div class="auth-alert auth-alert--error" role="alert">{{ $errors->first() }}</div>@endif
                @if(session('error'))<div class="auth-alert auth-alert--error" role="alert">{{ session('error') }}</div>@endif
                @if(session('success'))<div class="auth-alert" role="status">{{ session('success') }}</div>@endif
                <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                    @csrf
                    <label class="auth-field"><span class="auth-field__label">شماره موبایل <small>MOBILE NUMBER</small></span><div class="auth-input-wrap"><span class="auth-input-index" aria-hidden="true">01</span><input type="tel" name="phone" value="{{ old('phone') }}" placeholder="09123456789" autocomplete="tel" inputmode="tel" maxlength="16" required autofocus></div></label>
                    <label class="auth-field"><span class="auth-field__label">رمز عبور <small>YOUR PASSWORD</small></span><div class="auth-input-wrap auth-password-wrap"><input type="password" name="password" placeholder="رمز عبورت را وارد کن" autocomplete="current-password" required data-password-input><button class="auth-password-toggle" type="button" data-password-toggle aria-label="نمایش رمز عبور" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg></button></div></label>
                    <label class="auth-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>مرا به خاطر بسپار</span></label>
                    <button class="auth-button" type="submit"><span>ورود به حساب</span><b aria-hidden="true">↗</b></button>
                </form>
                @if($demoCredentials)
                    <section class="auth-demo" aria-label="اطلاعات ورود مدیر در محیط لوکال">
                        <div class="auth-demo__top"><div><strong>ورود مدیر همین محیط</strong><span class="auth-demo__hint">فقط در حالت لوکال یا تست نمایش داده می‌شود.</span></div></div>
                        <div class="auth-demo__credentials">
                            <div class="auth-demo__credential"><span>PHONE</span><code>{{ $demoCredentials['phone'] }}</code></div>
                            <div class="auth-demo__credential"><span>PASSWORD</span><code>{{ $demoCredentials['password'] }}</code></div>
                        </div>
                        <button type="button" class="auth-demo__fill" data-demo-fill data-demo-phone="{{ $demoCredentials['phone'] }}" data-demo-password="{{ $demoCredentials['password'] }}">پر کردن فرم ورود</button>
                    </section>
                @endif
                <footer class="auth-panel__foot"><span>SECURE CUSTOMER ACCESS</span><span>حساب نداری؟ <a href="{{ route('register', request()->query()) }}">ثبت‌نام کن</a></span></footer>
            </div>
            <aside class="auth-context" aria-label="مسیرهای دسترسی"><img src="{{ asset('images/auth1.webp') }}" alt="" aria-hidden="true" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0"><span class="auth-context__kicker">START HERE</span><strong>ساده و امن،<br>برای ادامه خرید.</strong><div class="auth-context__items"><div class="auth-context__item is-active"><span>01</span><div><strong>ورود</strong><small>حساب مشتری / مدیریت</small></div><b aria-hidden="true">↗</b></div><a href="{{ route('register', request()->query()) }}" class="auth-context__item"><span>02</span><div><strong>ثبت‌نام</strong><small>شروع با حساب مشتری</small></div><b aria-hidden="true">↗</b></a></div><div class="auth-context__note"><i aria-hidden="true"></i><span>حساب تازه‌ساخته‌شده مشتری است؛ دسترسی مدیریت فقط برای مدیران مجاز است.</span></div></aside>
        </section>
    </div>
</main>
</body>
</html>
