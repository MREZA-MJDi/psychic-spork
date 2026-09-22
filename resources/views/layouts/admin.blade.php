<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @hasSection('title')
            @yield('title') | Janan
        @else
            پنل مدیریت | Janan
        @endif
    </title>

    <link
        rel="stylesheet"
        href="{{ asset('css/admin.css') }}"
    >

    @stack('styles')
</head>

<body class="admin-body">

<div class="admin-shell">

    {{-- =====================================================
         SIDEBAR
         ===================================================== --}}

    <aside
        class="admin-sidebar"
        id="adminSidebar"
    >

        <div class="admin-sidebar__brand">
            <a
                href="{{ route('admin.dashboard') }}"
                class="admin-brand"
            >
                <span class="admin-brand__mark">
                    J
                </span>

                <span class="admin-brand__content">
                    <strong>Janan</strong>
                    <small>پنل مدیریت</small>
                </span>
            </a>
        </div>

        <nav
            class="admin-nav"
            aria-label="منوی مدیریت"
        >

            {{-- اصلی --}}
            <div class="admin-nav__section">

                <span class="admin-nav__label">
                    اصلی
                </span>

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ⌂
                    </span>

                    <span>
                        داشبورد
                    </span>
                </a>

            </div>

            {{-- فروشگاه --}}
            <div class="admin-nav__section">

                <span class="admin-nav__label">
                    فروشگاه
                </span>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ▦
                    </span>

                    <span>
                        محصولات
                    </span>
                </a>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ◇
                    </span>

                    <span>
                        دسته‌بندی‌ها
                    </span>
                </a>

                <a
                    href="{{ route('admin.brands.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.brands.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ✦
                    </span>

                    <span>
                        برندها
                    </span>
                </a>

                <a
                    href="{{ route('admin.inventory.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.inventory.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ▤
                    </span>

                    <span>
                        موجودی
                    </span>
                </a>

            </div>

            {{-- فروش --}}
            <div class="admin-nav__section">

                <span class="admin-nav__label">
                    فروش
                </span>

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ▱
                    </span>

                    <span>
                        سفارش‌ها
                    </span>
                </a>

                <a
                    href="{{ route('admin.customers.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ♙
                    </span>

                    <span>
                        مشتریان
                    </span>
                </a>

                <a
                    href="{{ route('admin.accounting.index') }}"
                    class="admin-nav__link {{ request()->routeIs('admin.accounting.*') ? 'is-active' : '' }}"
                >
                    <span class="admin-nav__icon">
                        ▣
                    </span>

                    <span>
                        حسابداری
                    </span>
                </a>

            </div>

        </nav>

        {{-- Sidebar Footer --}}
        <div class="admin-sidebar__footer">

            <a
                href="{{ route('home') }}"
                class="admin-sidebar__store-link"
                target="_blank"
                rel="noopener"
            >
                <span>↗</span>

                <span>
                    مشاهده فروشگاه
                </span>
            </a>

            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="admin-logout"
                >
                    <span>⇥</span>

                    <span>
                        خروج از حساب
                    </span>
                </button>
            </form>

        </div>

    </aside>


    {{-- =====================================================
         MAIN
         ===================================================== --}}

    <div class="admin-main">

        {{-- Header --}}
        <header class="admin-header">

            <div class="admin-header__start">

                <button
                    type="button"
                    class="admin-menu-toggle"
                    id="adminMenuToggle"
                    aria-label="باز کردن منوی مدیریت"
                    aria-controls="adminSidebar"
                    aria-expanded="false"
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="admin-header__page">

                    <span class="admin-header__eyebrow">
                        Janan Admin
                    </span>

                    <h1>
                        @yield('page-title', 'داشبورد')
                    </h1>

                </div>

            </div>

            <div class="admin-header__actions">

                <a
                    href="{{ route('home') }}"
                    class="admin-header__store"
                    target="_blank"
                    rel="noopener"
                >
                    فروشگاه

                    <span>
                        ↗
                    </span>
                </a>

                <div class="admin-user">

                    <span class="admin-user__avatar">
                        {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </span>

                    <span class="admin-user__info">

                        <strong>
                            {{ auth()->user()->name ?? 'مدیر' }}
                        </strong>

                        <small>
                            مدیر فروشگاه
                        </small>

                    </span>

                </div>

            </div>

        </header>


        {{-- Content --}}
        <main class="admin-content">

            @if(session('success'))
                <div class="admin-alert admin-alert--success">

                    <span class="admin-alert__icon">
                        ✓
                    </span>

                    <span>
                        {{ session('success') }}
                    </span>

                </div>
            @endif

            @if(session('error'))
                <div class="admin-alert admin-alert--error">

                    <span class="admin-alert__icon">
                        !
                    </span>

                    <span>
                        {{ session('error') }}
                    </span>

                </div>
            @endif

            @if($errors->any())
                <div class="admin-alert admin-alert--error">

                    <span class="admin-alert__icon">
                        !
                    </span>

                    <div>
                        @foreach($errors->all() as $error)
                            <div>
                                {{ $error }}
                            </div>
                        @endforeach
                    </div>

                </div>
            @endif

            @yield('content')

        </main>


        {{-- Footer --}}
        <footer class="admin-footer">

            <span>
                © {{ now()->year }} Janan
            </span>

            <span>
                پنل مدیریت فروشگاه
            </span>

        </footer>

    </div>

</div>


{{-- Mobile overlay --}}
<div
    class="admin-overlay"
    id="adminOverlay"
></div>


<script>
    (() => {
        const body = document.body;
        const toggle = document.getElementById('adminMenuToggle');
        const overlay = document.getElementById('adminOverlay');

        if (!toggle) {
            return;
        }

        const openMenu = () => {
            body.classList.add('admin-menu-open');
            toggle.setAttribute('aria-expanded', 'true');
        };

        const closeMenu = () => {
            body.classList.remove('admin-menu-open');
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.addEventListener('click', () => {
            if (body.classList.contains('admin-menu-open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        overlay?.addEventListener('click', closeMenu);

        document
            .querySelectorAll('.admin-nav__link')
            .forEach((link) => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 960) {
                        closeMenu();
                    }
                });
            });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 960) {
                closeMenu();
            }
        });
    })();
</script>

@stack('scripts')

</body>
</html>
