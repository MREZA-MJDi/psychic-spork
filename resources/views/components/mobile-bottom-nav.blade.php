@props([
    'context' => 'store',
])

@php
    $isAdmin = $context === 'admin';
@endphp

<nav
    class="mobile-bottom-nav mobile-bottom-nav--{{ $isAdmin ? 'admin' : 'store' }} {{ $isAdmin ? 'admin-mobile-nav' : 'store-mobile-bottom' }}"
    aria-label="{{ $isAdmin ? 'دسترسی سریع مدیریت' : 'منوی سریع فروشگاه' }}"
>
    @if($isAdmin)
        <a
            href="{{ route('admin.dashboard') }}"
            class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            aria-label="داشبورد"
        >
            <span class="mobile-bottom-nav__icon">⌂</span>
            <span>خانه</span>
        </a>

        <a
            href="{{ route('admin.products.index') }}"
            class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
            aria-label="محصولات"
        >
            <span class="mobile-bottom-nav__icon">◈</span>
            <span>محصول</span>
        </a>

        <a
            href="{{ route('admin.orders.index') }}"
            class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
            aria-label="سفارش‌ها"
        >
            <span class="mobile-bottom-nav__icon">◫</span>
            <span>سفارش</span>
        </a>

        <a
            href="{{ route('admin.contact.index') }}"
            class="{{ request()->routeIs('admin.contact.*') ? 'active' : '' }}"
            aria-label="پیام‌ها"
        >
            <span class="mobile-bottom-nav__icon">✉</span>
            <span>پیام</span>
        </a>

        <a
            href="{{ route('admin.profile.edit') }}"
            class="{{ request()->routeIs('admin.profile.*') ? 'active' : '' }}"
            aria-label="پروفایل"
        >
            <span class="mobile-bottom-nav__icon">◉</span>
            <span>پروفایل</span>
        </a>
    @else
        <a
            class="{{ request()->routeIs('home') ? 'is-active' : '' }}"
            href="{{ route('home') }}"
            aria-label="خانه"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
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
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7 7h10l1 13H6L7 7Z"/>
                <path d="M9 7a3 3 0 0 1 6 0"/>
            </svg>
            <span>محصولات</span>
        </a>

        <a
            class="{{ request()->routeIs('club') ? 'is-active' : '' }}"
            href="{{ route('club') }}"
            aria-label="باشگاه مشتریان DrClubz"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 21s-7-4.35-9-8.4C1.4 9.4 3.5 6 7 6c2 0 3.4 1.1 5 2.9C13.6 7.1 15 6 17 6c3.5 0 5.6 3.4 4 6.6-2 4.05-9 8.4-9 8.4Z"/>
                <path d="M8.5 3.5 10 5"/>
                <path d="m15.5 3.5-1.5 1.5"/>
            </svg>
            <span>باشگاه</span>
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
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="8" r="3.2"/>
                <path d="M5.5 20c.8-3.4 3-5.2 6.5-5.2s5.7 1.8 6.5 5.2"/>
            </svg>
            <span>حساب</span>
        </a>

        <a
            class="{{ request()->routeIs('cart') ? 'is-active' : '' }}"
            href="{{ route('cart') }}"
            aria-label="سبد خرید"
            data-cart-open
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 8h14l-1.2 11H6.2L5 8Z"/>
                <path d="M9 8a3 3 0 0 1 6 0"/>
            </svg>

            <b
                class="cart-count"
                data-cart-count
                @if(($cartCount ?? 0) < 1) hidden @endif
            >
                {{ $cartCount ?? 0 }}
            </b>

            <span>سبد</span>
        </a>
    @endif
</nav>
