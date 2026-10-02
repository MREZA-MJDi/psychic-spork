@extends('layouts.store')

@section('title','دسته‌بندی‌ها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="catalog-page catalog-page--categories">

    <section class="catalog-masthead catalog-masthead--directory">
        <div class="container">
            <div class="catalog-masthead__top">
                <span class="eyebrow">JANAN / COLLECTIONS / 02</span>

                <div class="catalog-masthead__stat">
                    <strong>{{ number_format($categories->count()) }}</strong>
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
                <div class="collection-grid">
                    @foreach($categories as $category)
                        <a
                            href="{{ route('categories.show',$category) }}"
                            class="collection-card"
                        >
                            <div class="collection-card__image">
                                <x-store.image
                                    :src="$category->coverMedia?->url"
                                    :alt="$category->name"
                                    fallback-class="card-image-placeholder"
                                    :fallback="$category->name"
                                />

                                <span class="collection-card__number">
                                    {{ sprintf('%02d', $loop->iteration) }}
                                </span>

                                <span class="collection-card__veil" aria-hidden="true"></span>
                            </div>

                            <div class="collection-card__body">
                                <div>
                                    <small>JANAN / COLLECTION</small>
                                    <h2>{{ $category->name }}</h2>
                                    <p>
                                        {{ $category->description ?: 'منتخب محصولات این دسته را ببینید.' }}
                                    </p>
                                </div>

                                <div class="collection-card__meta">
                                    <span>{{ number_format($category->active_products_count) }} محصول</span>
                                    <b aria-hidden="true">↗</b>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <h2>دسته‌بندی فعالی وجود ندارد.</h2>
                </div>
            @endif

        </div>
    </section>

</div>
@endsection
