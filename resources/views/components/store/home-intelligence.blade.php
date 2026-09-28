@props([
    'products' => collect(),
])

@php
    $product = collect($products)->first();

    $variant = $product?->variants?->first(
        fn ($item) => (bool) $item->is_active
    );

    $image = $product?->galleryMedia?->first()?->url;

    $stock = (int) ($variant?->stock ?? 0);
    $isOnSale = (bool) ($variant?->is_on_sale ?? false);

    $discount = $isOnSale
        ? max(
            1,
            round(
                (1 - (
                    $variant->effective_price /
                    max(1, (float) $variant->price)
                )) * 100
            )
        )
        : 0;

    $price = $variant?->effective_price
        ? number_format($variant->effective_price) . ' تومان'
        : 'قیمت در دسترس نیست';

    $availability = match (true) {
        $stock < 1 => 'ناموجود',
        $variant?->is_low_stock => 'موجودی محدود',
        default => 'موجود',
    };
@endphp

<section
    class="editorial-tech-section"
    aria-labelledby="home-intelligence-title"
>
    <div class="container">

        <article class="editorial-tech">

            <div class="editorial-tech__visual">

                @if($image)
                    <div class="editorial-tech__media">
                        <img
                            src="{{ $image }}"
                            alt="{{ $product?->name ?? 'محصول منتخب جانان' }}"
                            loading="lazy"
                            decoding="async"
                        >
                    </div>
                @else
                    <div class="editorial-tech__placeholder">
                        <span>JANAN</span>
                        <strong>SMART EDIT</strong>
                    </div>
                @endif

                <div class="editorial-tech__veil" aria-hidden="true"></div>

                <div class="editorial-tech__topline">
                    <span>JANAN / SMART EDIT</span>
                    <span>LIVE PRODUCT DATA</span>
                </div>

                @if($product)
                    <div class="editorial-tech__floating-card">
                        <span class="editorial-tech__floating-label">
                            انتخاب فعلی
                        </span>

                        <div class="editorial-tech__floating-main">
                            <strong>{{ $product->name }}</strong>
                            <span>{{ $discount > 0 ? $discount . '٪' : 'J' }}</span>
                        </div>

                        <div class="editorial-tech__floating-meta">
                            <span>{{ $product->brand?->name ?? 'JANAN' }}</span>
                            <span class="{{ $stock > 0 ? 'is-available' : 'is-out' }}">
                                {{ $availability }}
                            </span>
                        </div>
                    </div>
                @endif

                @if($discount > 0)
                    <span class="editorial-tech__discount">
                        {{ $discount }}٪ تخفیف
                    </span>
                @endif

                <span class="editorial-tech__vertical" aria-hidden="true">
                    REAL CATALOG SIGNAL
                </span>
            </div>

            <div class="editorial-tech__content">

                <div class="editorial-tech__content-top">
                    <span class="editorial-tech__kicker">
                        DATA / 03
                    </span>

                    <span class="editorial-tech__counter">
                        <i></i>
                        LIVE
                    </span>
                </div>

                <div class="editorial-tech__copy">
                    <span class="eyebrow">SMART COMMERCE</span>

                    <h2 id="home-intelligence-title">
                        انتخابی که
                        <em>از داده واقعی</em>
                        می‌آید.
                    </h2>

                    <p>
                        این بخش از کاتالوگ واقعی جانان تغذیه می‌شود؛
                        قیمت، موجودی و تخفیف همان محصولی هستند که همین حالا در فروشگاه ثبت شده‌اند.
                    </p>
                </div>

                <div class="editorial-tech__footer">

                    <div class="editorial-tech__metrics">
                        <div>
                            <strong>{{ $price }}</strong>
                            <span>قیمت فعلی</span>
                        </div>

                        <div>
                            <strong>{{ $availability }}</strong>
                            <span>وضعیت موجودی</span>
                        </div>

                        <div>
                            <strong>{{ $discount > 0 ? $discount . '٪' : '—' }}</strong>
                            <span>تخفیف فعال</span>
                        </div>
                    </div>

                    <a
                        href="{{ $product ? route('products.show', $product) : route('products.index') }}"
                        class="editorial-tech__cta"
                    >
                        <span>{{ $product ? 'مشاهده محصول' : 'مشاهده محصولات' }}</span>
                        <i aria-hidden="true">↗</i>
                    </a>

                </div>

                <span
                    class="editorial-tech__orb editorial-tech__orb--one"
                    aria-hidden="true"
                ></span>

                <span
                    class="editorial-tech__orb editorial-tech__orb--two"
                    aria-hidden="true"
                ></span>

            </div>

        </article>

    </div>
</section>
