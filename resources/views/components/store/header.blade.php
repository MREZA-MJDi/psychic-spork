@php
    $accountUrl = match (true) {
        !auth()->check() => route('login'),
        auth()->user()->isAdmin() => route('admin.dashboard'),
        default => route('account'),
    };

    $isHome = request()->routeIs('home');
    $isProducts = request()->routeIs('products.*');
    $isCategories = request()->routeIs('categories.*');
    $isBrands = request()->routeIs('brands.*');
    $isAbout = request()->routeIs('about');
    $isContact = request()->routeIs('contact');
@endphp

<header class="store-header">

    <div class="container store-header__inner">

        {{-- Brand --}}
        <a
            href="{{ route('home') }}"
            class="brand"
            aria-label="{{ $siteBrandNameLatin ?? 'Janan' }}"
        >
            <span class="brand__latin">
                {{ $siteBrandNameLatin ?? 'Janan' }}
            </span>

            <span class="brand__nastaliq">
                {{ $siteBrandNameFa ?? 'جانان' }}
            </span>
        </a>


        {{-- Desktop Navigation --}}
        <nav
            class="store-nav"
            aria-label="منوی اصلی فروشگاه"
        >
            <a
                href="{{ route('home') }}"
                class="store-nav__link {{ $isHome ? 'is-active' : '' }}"
                @if($isHome) aria-current="page" @endif
            >
                خانه
            </a>

            <a
                href="{{ route('products.index') }}"
                class="store-nav__link {{ $isProducts ? 'is-active' : '' }}"
                @if($isProducts) aria-current="page" @endif
            >
                محصولات
            </a>

            <a
                href="{{ route('categories.index') }}"
                class="store-nav__link {{ $isCategories ? 'is-active' : '' }}"
                @if($isCategories) aria-current="page" @endif
            >
                دسته‌بندی‌ها
            </a>

            <a
                href="{{ route('brands.index') }}"
                class="store-nav__link {{ $isBrands ? 'is-active' : '' }}"
                @if($isBrands) aria-current="page" @endif
            >
                برندها
            </a>

            <a
                href="{{ route('about') }}"
                class="store-nav__link {{ $isAbout ? 'is-active' : '' }}"
                @if($isAbout) aria-current="page" @endif
            >
                درباره ما
            </a>

            <a
                href="{{ route('contact') }}"
                class="store-nav__link {{ $isContact ? 'is-active' : '' }}"
                @if($isContact) aria-current="page" @endif
            >
                تماس با ما
            </a>
        </nav>


        {{-- Header Actions --}}
        <div class="header-actions">

            {{-- Search --}}
            <button
                type="button"
                class="icon-button"
                aria-label="جستجوی محصولات"
                aria-expanded="false"
                aria-controls="store-search-panel"
                data-search-toggle
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    focusable="false"
                >
                    <circle cx="11" cy="11" r="6.5"/>
                    <path d="m16 16 4.5 4.5"/>
                </svg>
            </button>


            {{-- Account --}}
            <a
                href="{{ $accountUrl }}"
                class="icon-button header-account"
                aria-label="حساب کاربری"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    focusable="false"
                >
                    <circle cx="12" cy="8" r="3.2"/>
                    <path d="M5.5 20c.8-3 3.4-5.2 6.5-5.2s5.7 2.2 6.5 5.2"/>
                </svg>
            </a>


            {{-- Cart --}}
            <a
                href="{{ route('cart') }}"
                class="icon-button cart-button"
                aria-label="سبد خرید"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    focusable="false"
                >
                    <path d="M5 8h14l-1.2 11H6.2L5 8Z"/>
                    <path d="M9 8a3 3 0 0 1 6 0"/>
                </svg>

                @if(($cartCount ?? 0) > 0)
                    <span class="cart-count">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

        </div>

    </div>


    {{-- Search Panel --}}
    <div
        id="store-search-panel"
        class="search-panel"
        data-search-panel
    >
        <div class="container">

            <form
                class="search-panel__form"
                action="{{ route('products.index') }}"
                method="GET"
            >
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="نام محصول را جستجو کنید..."
                    autocomplete="off"
                    enterkeyhint="search"
                    aria-label="جستجوی محصولات"
                >

                <button
                    type="submit"
                    class="button button--primary"
                >
                    جستجو
                </button>
            </form>

        </div>
    </div>

</header>
