@extends('layouts.auth')
@section('title', 'ورود به حساب')

@section('form')
    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="phone">شماره موبایل</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="09123456789"
                autocomplete="tel" inputmode="tel" maxlength="16" required>
        </div>
        @include('auth.password', ['name' => 'password', 'label' => 'رمز عبور', 'autocomplete' => 'current-password'])
        <label class="auth-remember">
            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
            مرا به خاطر بسپار
        </label>
        <button class="auth-button" type="submit">ورود به حساب</button>
    </form>

    @if($demoCredentials)
        <section class="auth-demo" aria-label="اطلاعات ورود مدیر در محیط لوکال">
            <div class="auth-demo__top"><div><strong>ورود مدیر همین محیط</strong><span class="auth-demo__hint">فقط در حالت لوکال یا تست نمایش داده می‌شود.</span></div></div>
            <div class="auth-demo__credentials">
                <div class="auth-demo__credential"><span>شماره موبایل</span><code>{{ $demoCredentials['phone'] }}</code></div>
                <div class="auth-demo__credential"><span>رمز عبور</span><code>{{ $demoCredentials['password'] }}</code></div>
            </div>
            <button type="button" class="auth-demo__fill" data-demo-fill data-demo-phone="{{ $demoCredentials['phone'] }}" data-demo-password="{{ $demoCredentials['password'] }}">پر کردن فرم ورود</button>
        </section>
    @endif
@endsection

@section('switch')
    حساب نداری؟ <a href="{{ route('register', request()->query()) }}">ثبت‌نام کن</a>
@endsection
