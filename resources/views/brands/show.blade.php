@extends('layouts.store')

@section('content')
<div class="brand-profile-page">

    {{-- 01. Brand identity --}}
    <section class="page-hero page-hero--premium page-hero--brand">
        <div class="container brand-hero">

            <div class="brand-hero__logo">
                @if($brand->logoMedia?->url)
                    <img
                        src="{{ $brand->logoMedia->url }}"
                        alt="لوگوی {{ $brand->name }}"
                        fetchpriority="high"
                    >
                @else
                    <span>{{ mb_substr($brand->name, 0, 1) }}</span>
                @endif
            </div>

            <div class="brand-hero__copy">
                <a
                    class="page-kicker"
                    href="{{ route('brands.index') }}"
                >
                    ↖ بازگشت به برندها
                </a>

                <span class="eyebrow">
                    {{ $brandProfile['eyebrow'] }}
                </span>

                <h1>{{ $brand->name }}</h1>

                <p>
                    {{ $brandProfile['position'] }}
                </p>

                <div class="brand-hero__meta">
                    <span>{{ number_format($products->total()) }} محصول فعال</span>
                    <i></i>
                    <span>JANAN SELECT</span>
                </div>
            </div>

        </div>
    </section>

    {{-- 02. Brand overview --}}
    <section class="brand-profile__section">
        <div class="container">

            <div class="brand-profile__intro">
                <span class="eyebrow">BRAND / OVERVIEW</span>

                <h2>
                    {{ $brand->name }} دقیقاً چه هویتی دارد؟
                </h2>

                <p>
                    {{ $brandProfile['summary'] }}
                </p>
            </div>

        </div>
    </section>

    {{-- 03. Strengths --}}
    <section class="brand-profile__section brand-profile__section--soft">
        <div class="container">

            <header class="section-head">
                <div>
                    <span class="eyebrow">WHAT STANDS OUT</span>

                    <h2>
                        ویژگی‌هایی که موقع انتخاب باید ببینی.
                    </h2>

                    <p>
                        به‌جای شعار، این بخش روی ویژگی‌هایی تمرکز دارد که در معرفی برند و محصولاتش قابل بررسی‌اند.
                    </p>
                </div>
            </header>

            <div class="brand-profile__grid">
                @foreach($brandProfile['strengths'] as $item)
                    <article class="brand-profile__card">
                        <span class="brand-profile__number">
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>

                        <h3>{{ $item['title'] }}</h3>

                        <p>
                            {{ $item['text'] }}
                        </p>
                    </article>
                @endforeach
            </div>

        </div>
    </section>

    {{-- 04. Considerations --}}
    <section class="brand-profile__section">
        <div class="container">

            <div class="brand-profile__comparison">

                <div class="brand-profile__comparison-intro">
                    <span class="eyebrow">BEFORE YOU BUY</span>

                    <h2>
                        نقاطی که باید با دقت بیشتری بررسی شوند.
                    </h2>

                    <p>
                        این بخش را عمداً به‌عنوان «ضعف قطعی برند» معرفی نکرده‌ایم؛
                        چون کیفیت، تن‌خور و راحتی می‌تواند بین مدل‌های مختلف یک برند متفاوت باشد.
                    </p>
                </div>

                <div class="brand-profile__considerations">
                    @foreach($brandProfile['considerations'] as $item)
                        <article class="brand-profile__consideration">
                            <span>
                                {{ sprintf('%02d', $loop->iteration) }}
                            </span>

                            <div>
                                <h3>{{ $item['title'] }}</h3>

                                <p>
                                    {{ $item['text'] }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>

            </div>

        </div>
    </section>

    {{-- 05. Market position --}}
    <section class="brand-profile__market">
        <div class="container">

            <div class="brand-profile__market-head">
                <div>
                    <span class="eyebrow">MARKET / CONTEXT</span>

                    <h2>
                        {{ $brandProfile['market']['title'] }}
                    </h2>

                    <p>
                        {{ $brandProfile['market']['text'] }}
                    </p>
                </div>

                <span class="brand-profile__market-mark" aria-hidden="true">
                    {{ mb_substr($brand->name, 0, 1) }}
                </span>
            </div>

            <div class="brand-profile__market-grid">
                @foreach($brandProfile['market']['dimensions'] as $dimension)
                    <div class="brand-profile__market-item">
                        <span>{{ $dimension['label'] }}</span>
                        <strong>{{ $dimension['value'] }}</strong>
                    </div>
                @endforeach
            </div>

            <div class="brand-profile__market-note">
                <span class="brand-profile__market-note-label">
                    JANAN EDITORIAL NOTE
                </span>

                <p>
                    برای مقایسه برندها، به‌جای رتبه‌بندی کلی، بهتر است محصول مشخص،
                    سایز، متریال، نوع ساخت و قیمت همان مدل را مقایسه کنی.
                </p>
            </div>

        </div>
    </section>

    {{-- 06. Products --}}
    <section class="section-block catalog-stage brand-profile__products">
        <div class="container">

            <header class="section-head">
                <div>
                    <span class="eyebrow">JANAN / CATALOG</span>

                    <h2>
                        محصولات {{ $brand->name }}
                    </h2>

                    <p>
                        اطلاعات قیمت، موجودی و مشخصات از محصولات فعال فروشگاه خوانده می‌شود.
                    </p>
                </div>

                <a
                    class="text-link"
                    href="{{ route('products.index', ['brand' => $brand->slug]) }}"
                >
                    مشاهده همه محصولات
                    <span aria-hidden="true">↗</span>
                </a>
            </header>

            @if($products->isNotEmpty())
                <div class="product-grid product-grid--editorial">
                    @foreach($products as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>

                @if($products->hasPages())
                    <nav
                        class="store-pagination"
                        aria-label="صفحه‌بندی محصولات {{ $brand->name }}"
                    >
                        {{ $products->onEachSide(1)->links() }}
                    </nav>
                @endif
            @else

                <div class="empty-state">
                    <span class="eyebrow">NO ACTIVE PRODUCTS</span>

                    <h2>
                        در حال حاضر محصول فعالی از این برند موجود نیست.
                    </h2>

                    <a
                        class="button button--primary"
                        href="{{ route('brands.index') }}"
                    >
                        بازگشت به برندها
                    </a>
                </div>

            @endif

        </div>
    </section>

</div>
@endsection
