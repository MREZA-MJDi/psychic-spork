@php
    $accountUrl = match (true) {
        !auth()->check() => route('login'),
        auth()->user()->isAdmin() => route('admin.dashboard'),
        default => route('account'),
    };

    $isHome = request()->routeIs('home');
    $isProducts = request()->routeIs('products.*');
    $isClub = request()->routeIs('club');
    $isBrands = request()->routeIs('brands.*');
    $isAbout = request()->routeIs('about');
    $isWholesale = request()->routeIs('wholesale.show');
@endphp

<header class="store-header {{ $isHome ? 'store-header--immersive' : '' }}">

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
            id="store-mobile-menu"
            class="store-nav"
            aria-label="منوی اصلی فروشگاه"
            data-mobile-menu
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
                href="{{ route('club') }}"
                class="store-nav__link {{ $isClub ? 'is-active' : '' }}"
                @if($isClub) aria-current="page" @endif
            >
                DrClubz
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
                درباره و تماس
            </a>

            <a
                href="{{ route('wholesale.show') }}"
                class="store-nav__link {{ $isWholesale ? 'is-active' : '' }}"
                @if($isWholesale) aria-current="page" @endif
            >
                خرید عمده
            </a>
        </nav>


        {{-- Header Actions --}}
        <div class="header-actions">

            {{-- Mobile navigation --}}
            <button
                type="button"
                class="mobile-menu-toggle"
                aria-label="باز کردن منوی فروشگاه"
                aria-expanded="false"
                aria-controls="store-mobile-menu"
                data-menu-toggle
            >
                <span></span>
                <span></span>
                <span></span>
            </button>

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
            <span class="cart-trigger-wrap">
                <a
                    href="{{ route('cart') }}"
                    class="icon-button cart-button"
                    aria-label="سبد خرید"
                    data-cart-open
                >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    focusable="false"
                >
                    <path d="M5 8h14l-1.2 11H6.2L5 8Z"/>
                    <path d="M9 8a3 3 0 0 1 6 0"/>
                </svg>

                    <span class="cart-count" data-cart-count @if(($cartCount ?? 0) < 1) hidden @endif>
                        {{ $cartCount ?? 0 }}
                    </span>
                </a>

                <div
                        class="cart-quick-preview"
                    data-cart-quick-preview
                    hidden
                    aria-live="polite"
                >
                <span class="cart-quick-preview__caret" aria-hidden="true"></span>

                <div class="cart-quick-preview__media" data-cart-preview-image>
                    <span>JANAN</span>
                </div>

                <div class="cart-quick-preview__copy">
                    <small>به سبد خرید اضافه شد</small>
                    <strong data-cart-preview-name></strong>
                    <span data-cart-preview-meta></span>
                </div>

                <a
                    href="{{ route('cart') }}"
                    class="cart-quick-preview__link"
                >
                    <span>مشاهده سبد</span>
                    <i aria-hidden="true">←</i>
                </a>

                <button
                    type="button"
                    class="cart-quick-preview__close"
                    data-cart-preview-close
                    aria-label="بستن پیش‌نمایش"
                >
                    ×
                    </button>
                </div>
            </span>

        </div>

    </div>


    {{-- Smart storefront search --}}
    <div
        id="store-search-panel"
        class="search-panel"
        data-search-panel
    >
        <div class="container">
            <form
                class="search-panel__form"
                data-store-search
                data-suggestions-url="{{ route('search.suggestions') }}"
                action="{{ route('products.index') }}"
                method="GET"
                role="search"
            >
                <div class="search-panel__field">
                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <circle cx="11" cy="11" r="6.5"/>
                        <path d="m16 16 4.5 4.5"/>
                    </svg>

                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="نام محصول، برند، دسته یا SKU را جستجو کن…"
                        autocomplete="off"
                        enterkeyhint="search"
                        aria-label="جستجوی محصولات"
                        data-search-input
                        minlength="2"
                    >

                    <button
                        type="button"
                        class="search-panel__clear"
                        data-search-clear
                        aria-label="پاک کردن جستجو"
                        hidden
                    >
                        ×
                    </button>
                </div>

                <button
                    type="submit"
                    class="button button--primary search-panel__submit"
                >
                    جستجو
                    <span aria-hidden="true">↵</span>
                </button>
            </form>

            <div
                class="search-suggestions"
                data-search-results
                hidden
                aria-live="polite"
            >
                <div class="search-suggestions__head">
                    <span>جستجوی سریع</span>
                    <small data-search-status>نام محصول یا برند را وارد کن</small>
                </div>

                <div
                    class="search-suggestions__list"
                    data-search-results-list
                ></div>
            </div>
        </div>
    </div>

</header>
