@extends('layouts.auth')
@section('title', 'ساخت حساب')

@section('form')
    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="name">نام و نام خانوادگی</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
        </div>
        <div class="auth-field">
            <label for="phone">شماره موبایل</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="09123456789"
                autocomplete="tel" inputmode="tel" maxlength="16" required>
        </div>
        <div class="auth-field">
            <label for="email">ایمیل <small>(اختیاری)</small></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email">
        </div>
        @include('auth.password', ['name' => 'password', 'label' => 'رمز عبور (حداقل ۸ کاراکتر)', 'autocomplete' => 'new-password'])
        @include('auth.password', ['name' => 'password_confirmation', 'label' => 'تکرار رمز عبور', 'autocomplete' => 'new-password'])
        <button class="auth-button" type="submit">ساخت حساب</button>
    </form>
@endsection

@section('switch')
    حساب داری؟ <a href="{{ route('login', request()->query()) }}">وارد شو</a>
@endsection
