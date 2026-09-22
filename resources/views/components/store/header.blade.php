@php
    $path = request()->path();
    $isHome = $path === '/' || $path === '';
    $accountUrl = !auth()->check()
        ? route('login')
        : (auth()->user()->isAdmin() ? route('admin.dashboard') : route('account'));
@endphp

<header class="store-header">
    <div class="container store-header__inner">

        <a href="{{ route('home') }}" class="brand" aria-label="Janan">
            <span class="brand__latin">{{ $siteBrandNameLatin ?? 'Janan' }}</span>
            <span class="brand__nastaliq">{{ $siteBrandNameFa ?? 'جانان' }}</span>
        </a>

        <nav class="store-nav" aria-label="منوی اصلی">
            <a href="{{ route('home') }}" class="store-nav__link {{ $isHome ? 'is-active' : '' }}">خانه</a>
            <a href="{{ route('products.index') }}" class="store-nav__link {{ str_starts_with($path, 'products') ? 'is-active' : '' }}">محصولات</a>
            <a href="{{ route('categories.index') }}" class="store-nav__link {{ str_starts_with($path, 'categories') ? 'is-active' : '' }}">دسته‌بندی‌ها</a>
            <a href="{{ route('brands.index') }}" class="store-nav__link {{ str_starts_with($path, 'brands') ? 'is-active' : '' }}">برندها</a>
            <a href="{{ route('about') }}" class="store-nav__link {{ str_starts_with($path, 'about') ? 'is-active' : '' }}">درباره ما</a>
            <a href="{{ route('contact') }}" class="store-nav__link {{ str_starts_with($path, 'contact') ? 'is-active' : '' }}">تماس با ما</a>
        </nav>

        <div class="header-actions">
            <button class="icon-button" type="button" aria-label="جستجو" aria-expanded="false" data-search-toggle>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
            </button>

            <a href="{{ $accountUrl }}" class="icon-button header-account" aria-label="حساب کاربری">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.2"/><path d="M5.5 20c.8-3 3.4-5.2 6.5-5.2s5.7 2.2 6.5 5.2"/></svg>
            </a>

            <a href="{{ route('cart') }}" class="icon-button cart-button" aria-label="سبد خرید">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1.2 11H6.2L5 8Z"/><path d="M9 8a3 3 0 0 1 6 0"/></svg>
                @if(($cartCount ?? 0) > 0)<span class="cart-count">{{ $cartCount }}</span>@endif
            </a>
        </div>

    </div>

    <div class="search-panel" data-search-panel>
        <div class="container">
            <form class="search-panel__form" action="{{ route('products.index') }}" method="GET">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="نام محصول را جستجو کنید..." autocomplete="off">
                <button class="button button--primary" type="submit">جستجو</button>
            </form>
        </div>
    </div>
</header>
