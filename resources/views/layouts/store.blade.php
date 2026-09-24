<!DOCTYPE html>

<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    @php
        $brandName = $siteBrandNameLatin ?? 'Janan';

        $seo = $seo ?? app(\App\Services\SeoService::class)->page(
            "{$brandName} — فروشگاه آنلاین",
            "فروشگاه آنلاین {$brandName}.",
            url()->current(),
            request()->routeIs(
                'cart',
                'checkout',
                'checkout.success',
                'account'
            )
                ? 'noindex,nofollow'
                : 'index,follow'
        );

        $isHome = request()->routeIs('home');
    @endphp

    <title>{{ $seo['title'] }}</title>

    <meta
        name="description"
        content="{{ $seo['description'] }}"
    >

    <meta
        name="robots"
        content="{{ $seo['robots'] }}"
    >

    <link
        rel="canonical"
        href="{{ $seo['canonical'] }}"
    >

    <meta
        property="og:type"
        content="{{ request()->routeIs('products.show') ? 'product' : 'website' }}"
    >

    <meta
        property="og:title"
        content="{{ $seo['title'] }}"
    >

    <meta
        property="og:description"
        content="{{ $seo['description'] }}"
    >

    <meta
        property="og:url"
        content="{{ $seo['canonical'] }}"
    >

    @if(!empty($seo['image']))
        <meta
            property="og:image"
            content="{{ $seo['image'] }}"
        >
    @endif

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="{{ $seo['title'] }}"
    >

    <meta
        name="twitter:description"
        content="{{ $seo['description'] }}"
    >

    @if(!empty($seo['image']))
        <meta
            name="twitter:image"
            content="{{ $seo['image'] }}"
        >
    @endif

    @if(!empty($seo['schema']))
        <script type="application/ld+json">
            {!! json_encode(
                $seo['schema'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) !!}
        </script>
    @endif

    {{-- Global storefront assets --}}
    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    {{-- Homepage-only assets --}}
    @if($isHome)
        @vite([
        'resources/css/editorial-hero.css',
        'resources/js/editorial-hero.js',
        ])
    @endif

</head>

<body class="store-body">

<div class="site-shell">


    <x-store.header />

    <main class="site-main">
        @yield('content')
    </main>

    <x-store.footer />

    <nav
        class="store-mobile-bottom"
        aria-label="منوی سریع فروشگاه"
    >
        <a
            class="{{ request()->routeIs('home') ? 'is-active' : '' }}"
            href="{{ route('home') }}"
            aria-label="خانه"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M4 10.5 12 4l8 6.5"/>
                <path d="M6.5 9.5V20h11V9.5"/>
                <path d="M10 20v-6h4v6"/>
            </svg>

            <span>خانه</span>
        </a>

        <a
            class="{{ request()->routeIs('products.*') ? 'is-active' : '' }}"
            href="{{ route('products.index') }}"
            aria-label="محصولات"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M7 7h10l1 13H6L7 7Z"/>
                <path d="M9 7a3 3 0 0 1 6 0"/>
            </svg>

            <span>محصولات</span>
        </a>

        <a
            class="{{ request()->routeIs('categories.*') ? 'is-active' : '' }}"
            href="{{ route('categories.index') }}"
            aria-label="دسته‌بندی‌ها"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M5 5h6v6H5z"/>
                <path d="M13 5h6v6h-6z"/>
                <path d="M5 13h6v6H5z"/>
                <path d="M13 13h6v6h-6z"/>
            </svg>

            <span>دسته‌ها</span>
        </a>

        <a
            class="{{ request()->routeIs('account', 'admin.*') ? 'is-active' : '' }}"
            href="{{
            !auth()->check()
                ? route('login')
                : (
                    auth()->user()->isAdmin()
                        ? route('admin.dashboard')
                        : route('account')
                )
        }}"
            aria-label="حساب کاربری"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle
                    cx="12"
                    cy="8"
                    r="3.2"
                />

                <path d="M5.5 20c.8-3.4 3-5.2 6.5-5.2s5.7 1.8 6.5 5.2"/>
            </svg>

            <span>حساب</span>
        </a>

        <a
            class="{{ request()->routeIs('cart') ? 'is-active' : '' }}"
            href="{{ route('cart') }}"
            aria-label="سبد خرید"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M5 8h14l-1.2 11H6.2L5 8Z"/>
                <path d="M9 8a3 3 0 0 1 6 0"/>
            </svg>

            @if(($cartCount ?? 0) > 0)
                <b>{{ $cartCount }}</b>
            @endif

            <span>سبد</span>
        </a>
    </nav>


</div>

</body>
</html>
