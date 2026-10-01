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
            request()->routeIs('cart', 'checkout', 'checkout.success', 'account')
                ? 'noindex,nofollow'
                : 'index,follow'
        );

        $isHome = request()->routeIs('home');
    @endphp

    <title>{{ $seo['title'] }}</title>
    <meta name="description" content="{{ $seo['description'] }}">
    <meta name="robots" content="{{ $seo['robots'] }}">
    <link rel="canonical" href="{{ $seo['canonical'] }}">

    <meta property="og:type" content="{{ request()->routeIs('products.show') ? 'product' : 'website' }}">
    <meta property="og:title" content="{{ $seo['title'] }}">
    <meta property="og:description" content="{{ $seo['description'] }}">
    <meta property="og:url" content="{{ $seo['canonical'] }}">
    @if(!empty($seo['image']))
        <meta property="og:image" content="{{ $seo['image'] }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['title'] }}">
    <meta name="twitter:description" content="{{ $seo['description'] }}">
    @if(!empty($seo['image']))
        <meta name="twitter:image" content="{{ $seo['image'] }}">
    @endif

    @if(!empty($seo['schema']))
        <script type="application/ld+json">
            {!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif

    @php
        $storeAssets = [
            'resources/css/store.css',
            'resources/js/app.js',
            'resources/js/store-cart.js',
            'resources/js/store-search.js',
            'resources/js/store-customer-uiux.js',
        ];

        if (request()->routeIs('wholesale.show')) {
            $storeAssets[] = 'resources/css/wholesale.css';
        }

        if (request()->routeIs('products.show')) {
            $storeAssets[] = 'resources/js/product-show.js';
        }

        if ($isHome) {
            $storeAssets[] = 'resources/css/home.css';
            $storeAssets[] = 'resources/css/editorial-hero.css';
            $storeAssets[] = 'resources/js/editorial-hero.js';

            if (!app()->environment('testing')) {
                $storeAssets[] = 'resources/js/home-product-carousel.js';
            }
        }

        if (!app()->environment('testing')) {
            $storeAssets[] = 'resources/css/store-responsive.css';
        }
    @endphp

    @vite($storeAssets)

</head>

<body class="store-body customer-store">
<div class="site-shell">
    <x-store.header />

    <main class="site-main">
        @yield('content')
    </main>

    <x-store.footer />
    <x-store.cart-drawer />
    <x-mobile-bottom-nav context="store" />
</div>
</body>
</html>
