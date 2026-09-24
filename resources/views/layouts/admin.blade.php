<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'پنل مدیریت جانان')</title>

    @vite([
    'resources/css/admin.css',
    'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body>

<div class="admin-shell">


    {{-- Sidebar --}}
    <aside class="admin-sidebar">

        <div class="admin-logo">
            <span>جانان</span>
            <small>ADMIN</small>
        </div>


        <nav class="admin-menu">

            <a href="{{ route('admin.dashboard') }}"
               class="admin-link">

                <span>⌂</span>
                داشبورد

            </a>


            <a href="{{ route('admin.products.index') }}"
               class="admin-link">

                <span>◈</span>
                محصولات

            </a>


            <a href="{{ route('admin.categories.index') }}"
               class="admin-link">

                <span>▣</span>
                دسته‌بندی‌ها

            </a>


            <a href="{{ route('admin.brands.index') }}"
               class="admin-link">

                <span>◇</span>
                برندها

            </a>


            <a href="{{ route('admin.orders.index') }}"
               class="admin-link">

                <span>🛒</span>
                سفارش‌ها

            </a>


            <a href="{{ route('admin.inventory.index') }}"
               class="admin-link">

                <span>▤</span>
                انبار

            </a>


            <a href="{{ route('admin.accounting.index') }}"
               class="admin-link">

                <span>₮</span>
                حسابداری

            </a>


        </nav>


    </aside>



    {{-- Main --}}
    <main class="admin-main">


        <header class="admin-header">

            <div>
                <h1>
                    @yield('page-title','مدیریت فروشگاه')
                </h1>
            </div>


            <div class="admin-user">

                <span>
                    {{ auth()->user()->name ?? 'Admin' }}
                </span>

            </div>


        </header>



        @if(session('success'))

            <div class="alert success">
                {{ session('success') }}
            </div>

        @endif


        @if(session('error'))

            <div class="alert error">
                {{ session('error') }}
            </div>

        @endif



        <section class="admin-content">

            @yield('content')

        </section>



    </main>



</div>



{{-- Mobile bottom navigation --}}
<nav class="mobile-bottom">


    <a href="{{ route('admin.dashboard') }}">
        خانه
    </a>


    <a href="{{ route('admin.products.index') }}">
        محصول
    </a>


    <a href="{{ route('admin.orders.index') }}">
        سفارش
    </a>


    <a href="#">
        من
    </a>


</nav>



@stack('scripts')

</body>
</html>
