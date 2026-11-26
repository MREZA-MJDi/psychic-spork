@extends('layouts.store')

@section('title','دسته‌بندی‌ها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="catalog-page catalog-page--categories">

    <section class="catalog-masthead catalog-masthead--directory">
        <div class="container">
            <div class="catalog-masthead__top">
                <span class="eyebrow">JANAN / COLLECTIONS / 02</span>

                <div class="catalog-masthead__stat">
                    <strong>{{ \App\Support\PersianNumber::format($categories->total()) }}</strong>
                    <span>دسته فعال</span>
                </div>
            </div>

            <div class="catalog-masthead__content">
                <div>
                    <h1>دسته‌ای که به سبک تو نزدیک‌تر است.</h1>
                    <p>
                        کالکشن‌های جانان را بر اساس فرم، کاربرد و حال‌وهوای انتخابت مرور کن.
                    </p>
                </div>

                <nav class="catalog-local-nav" aria-label="بخش‌های فروشگاه">
                    <a href="{{ route('products.index') }}">محصولات</a>
                    <a href="{{ route('categories.index') }}" aria-current="page">دسته‌بندی‌ها</a>
                    <a href="{{ route('brands.index') }}">برندها</a>
                </nav>
            </div>
        </div>
    </section>

    <section class="directory-stage directory-stage--collections">
        <div class="container">

            <div class="customer-action-strip customer-action-strip--spaced">
                <div class="customer-action-strip__copy">
                    <small>JANAN / COLLECTION MAP</small>
                    <strong>دسته را انتخاب کن و مستقیم وارد محصولات همان مسیر شو.</strong>
                </div>
                <div class="customer-action-strip__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">همه محصولات</a>
                    <a class="button button--ghost" href="{{ route('brands.index') }}">برندها</a>
                </div>
            </div>

            <header class="directory-intro">
                <div>
                    <span class="eyebrow">BROWSE BY FEEL</span>
                    <h2>انتخاب را از تصویر شروع کن.</h2>
                </div>

                <p>
                    هر دسته یک مسیر مستقل برای رسیدن به محصولات مرتبط دارد.
                </p>
            </header>

            @if($categories->isNotEmpty())
                <div class="janan-category-grid">
                    @foreach($categories as $category)
                        <x-store.category-card :category="$category" />
                    @endforeach
                </div>

                @if($categories->hasPages())
                    <nav class="store-pagination" aria-label="صفحه‌بندی دسته‌بندی‌ها">
                        {{ $categories->onEachSide(1)->links('vendor.pagination.store') }}
                    </nav>
                @endif
            @else
                <div class="empty-state">
                    <h2>دسته‌بندی فعالی وجود ندارد.</h2>
                </div>
            @endif

        </div>
    </section>

</div>
@endsection
