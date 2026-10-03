@extends('layouts.store')

@section('title','برندها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="catalog-page catalog-page--brands">

    <section class="catalog-masthead catalog-masthead--directory catalog-masthead--brands">
        <div class="container">
            <div class="catalog-masthead__top">
                <span class="eyebrow">{{ $siteBrandNameLatin }} / BRANDS / 03</span>

                <div class="catalog-masthead__stat">
                    <strong>{{ number_format($brands->count()) }}</strong>
                    <span>برند فعال</span>
                </div>
            </div>

            <div class="catalog-masthead__content">
                <div>
                    <h1>برندهای منتخب جانه جانان.</h1>
                    <p>
                        هر برند، زبان طراحی و شخصیت خودش را دارد. از اینجا وارد دنیای هرکدام شو.
                    </p>
                </div>

                <nav class="catalog-local-nav" aria-label="بخش‌های فروشگاه">
                    <a href="{{ route('products.index') }}">محصولات</a>
                    <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
                    <a href="{{ route('brands.index') }}" aria-current="page">برندها</a>
                </nav>
            </div>
        </div>
    </section>

    <section class="directory-stage directory-stage--brands">
        <div class="container">

            <div class="customer-action-strip customer-action-strip--spaced">
                <div class="customer-action-strip__copy">
                    <small>{{ $siteBrandNameLatin }} / BRAND MAP</small>
                    <strong>هویت برند را ببین و بعد مستقیم وارد محصولاتش شو.</strong>
                </div>
                <div class="customer-action-strip__actions">
                    <a class="button button--primary" href="{{ route('products.index') }}">همه محصولات</a>
                    <a class="button button--ghost" href="{{ route('categories.index') }}">دسته‌ها</a>
                </div>
            </div>

            <header class="directory-intro">
                <div>
                    <span class="eyebrow">MEET THE HOUSES</span>
                    <h2>نام‌ها، لوگوها، شخصیت‌ها.</h2>
                </div>

                <p>لوگو، معرفی کوتاه و تعداد محصولات هر برند را یک‌جا ببین؛ برای ورود به فهرست محصولات روی کارت بزن.</p>
            </header>

            @if($brands->isNotEmpty())
                <div class="brand-mosaic">
                    @foreach($brands as $brand)
                        <a
                            class="brand-mosaic__item"
                            href="{{ route('brands.show',$brand) }}"
                        >
                            <div class="brand-mosaic__top">
                                <span>{{ sprintf('%02d', $loop->iteration) }}</span>
                                <span>BRAND / {{ strtoupper($brand->slug) }}</span>
                            </div>

                            <div class="brand-mosaic__logo">
                                <x-store.image
                                    :src="$brand->logoMedia?->url"
                                    :alt="$brand->name"
                                    fallback-tag="span"
                                    :fallback="mb_substr($brand->name, 0, 1)"
                                />
                            </div>

                            <div class="brand-mosaic__bottom">
                                <div>
                                    <h2>{{ $brand->name }}</h2>
                                    <p>
                                        {{ $brand->description ?: 'معرفی این برند هنوز تکمیل نشده است.' }}
                                    </p>
                                </div>

                                <div class="brand-mosaic__meta">
                                    <small>{{ number_format($brand->active_products_count) }} محصول</small>
                                    <b aria-hidden="true">↗</b>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <h2>هنوز برندی فعال نشده.</h2>
                </div>
            @endif

        </div>
    </section>

</div>
@endsection
