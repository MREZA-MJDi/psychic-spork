<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') — {{ config('app.store_name', 'JANAN') }}</title>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="auth-brand" href="{{ route('home') }}" aria-label="بازگشت به فروشگاه">
            {{ config('app.store_name', 'JANAN') }}
        </a>
        <h1>@yield('title')</h1>

        @if($errors->any() || session('error'))
            <p class="auth-alert auth-alert--error" role="alert">{{ $errors->first() ?: session('error') }}</p>
        @endif
        @if(session('success'))
            <p class="auth-alert" role="status">{{ session('success') }}</p>
        @endif

        @yield('form')

        <footer class="auth-footer">@yield('switch')</footer>
    </main>
</body>
</html>
