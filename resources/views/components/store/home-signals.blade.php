@props([
    'recentProducts' => collect(),
    'popularProducts' => collect(),
])

@php
    $recentProducts = collect($recentProducts)->take(4)->values();
    $popularProducts = collect($popularProducts)->take(4)->values();
@endphp

<section
    class="home-signals-section"
    aria-labelledby="home-signals-title"
>
    <div class="container">

        <header class="home-signals__head">
            <div>
                <span class="eyebrow">JANAN / STORE PULSE / 05</span>
                <h2 id="home-signals-title">آنچه همین حالا در فروشگاه جریان دارد.</h2>
                <p>تازه‌های کاتالوگ و خریدهای ثبت‌شده در ۳۰ روز اخیر.</p>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                همه محصولات
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        <div class="home-signals__grid">

            <section class="home-signal-panel home-signal-panel--new" aria-labelledby="home-new-title">
                <div class="home-signal-panel__head">
                    <div>
                        <span class="home-signal-panel__index">01</span>
                        <h3 id="home-new-title">تازه اضافه شده</h3>
                    </div>

                    <span class="home-signal-panel__meta">
                        {{ number_format($recentProducts->count()) }} محصول
                    </span>
                </div>

                @if($recentProducts->isNotEmpty())
                    <div class="home-signal-list">
                        @foreach($recentProducts as $product)
                            @php
                                $variant = $product->primaryActiveVariant;
                                $image = $product->primaryGalleryMedia?->url;
                            @endphp

                            <a
                                href="{{ route('products.show', $product) }}"
                                class="home-signal-item"
                            >
                                <span class="home-signal-item__media">
                                    <x-store.image
                                        :src="$image"
                                        :alt="$product->name"
                                        fallback-tag="span"
                                        fallback-class="home-signal-item__fallback"
                                        fallback="JANAN"
                                    />
                                </span>

                                <span class="home-signal-item__copy">
                                    <small>{{ $product->brand?->name ?? $product->category?->name ?? 'JANAN' }}</small>
                                    <strong>{{ $product->name }}</strong>

                                    @if($variant?->effective_price)
                                        <span>{{ number_format($variant->effective_price) }} تومان</span>
                                    @endif
                                </span>

                                <span class="home-signal-item__arrow" aria-hidden="true">↗</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="home-signal-empty">هنوز محصول تازه‌ای ثبت نشده است.</div>
                @endif
            </section>

            <section class="home-signal-panel home-signal-panel--popular" aria-labelledby="home-popular-title">
                <div class="home-signal-panel__head">
                    <div>
                        <span class="home-signal-panel__index">02</span>
                        <h3 id="home-popular-title">پرفروش‌های ۳۰ روز اخیر</h3>
                    </div>

                    <span class="home-signal-panel__meta">
                        {{ number_format($popularProducts->count()) }} محصول
                    </span>
                </div>

                @if($popularProducts->isNotEmpty())
                    <div class="home-signal-list">
                        @foreach($popularProducts as $product)
                            @php
                                $variant = $product->primaryActiveVariant;
                                $image = $product->primaryGalleryMedia?->url;
                                $sales = (int) ($product->sales_quantity ?? 0);
                            @endphp

                            <a
                                href="{{ route('products.show', $product) }}"
                                class="home-signal-item home-signal-item--popular"
                            >
                                <span class="home-signal-item__media">
                                    <x-store.image
                                        :src="$image"
                                        :alt="$product->name"
                                        fallback-tag="span"
                                        fallback-class="home-signal-item__fallback"
                                        fallback="JANAN"
                                    />
                                </span>

                                <span class="home-signal-item__copy">
                                    <small>{{ $product->category?->name ?? 'JANAN COLLECTION' }}</small>
                                    <strong>{{ $product->name }}</strong>

                                    <span>
                                        {{ number_format($sales) }} فروش ثبت‌شده
                                    </span>
                                </span>

                                <span class="home-signal-item__rank" aria-hidden="true">
                                    {{ sprintf('%02d', $loop->iteration) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="home-signal-empty">هنوز فروش ثبت‌شده‌ای برای نمایش وجود ندارد.</div>
                @endif
            </section>

        </div>

    </div>
</section>
