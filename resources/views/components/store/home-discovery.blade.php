@props([
    'products' => collect(),
])

@php
    $products = collect($products)->filter()->values();

    $primary = $products->first();
    $secondary = $products->skip(1)->first();

    $variant = $primary?->primaryActiveVariant;

    $image = $primary?->primaryGalleryMedia?->url;
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
    class="home-discovery-section"
    aria-labelledby="home-discovery-title"
>
    <div class="container">

        <header class="home-discovery__head">
            <div>
                <span class="eyebrow">JANAN / SMART DISCOVERY / 05</span>

                <h2 id="home-discovery-title">
                    انتخاب هوشمند،
                    بر پایه کاتالوگ واقعی.
                </h2>

                <p>
                    یک لایه محتوایی سبک و پویا که از همان داده‌های واقعی فروشگاه برای
                    نمایش انتخاب فعلی، وضعیت موجودی و مسیر ادامه خرید استفاده می‌کند.
                </p>
            </div>

            <a
                href="{{ $primary ? route('products.show', $primary) : route('products.index') }}"
                class="text-link"
            >
                {{ $primary ? 'مشاهده انتخاب' : 'مشاهده محصولات' }}
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        <div class="home-discovery__grid">

            <a
                href="{{ $primary ? route('products.show', $primary) : route('products.index') }}"
                class="discovery-card"
            >
                <div class="discovery-card__media">
                    <x-store.image
                            :src="$image"
                            :alt="$primary?->name ?? 'محصول منتخب جانان'"
                            fallback-class="discovery-card__placeholder"
                            fallback="JANAN"
                        />
                </div>

                <div class="discovery-card__overlay" aria-hidden="true"></div>

                <div class="discovery-card__top">
                    <span>01</span>
                    <span>LIVE CATALOG</span>
                </div>

                <div class="discovery-card__body">
                    <div>
                        <span class="discovery-card__kicker">
                            {{ $primary?->brand?->name ?? 'JANAN' }}
                        </span>

                        <h3>
                            {{ $primary?->name ?? 'محصول منتخب' }}
                        </h3>

                        <p>
                            {{ $price }}
                            ·
                            {{ $availability }}
                            @if($discount > 0)
                                · {{ $discount }}٪ تخفیف
                            @endif
                        </p>
                    </div>

                    <span class="discovery-card__action">
                        <span>ورود به جزئیات محصول</span>
                        <b aria-hidden="true">↗</b>
                    </span>
                </div>
            </a>

            <a
                href="{{ $secondary ? route('products.show', $secondary) : route('products.index') }}"
                class="discovery-card discovery-card--category"
            >
                <div class="discovery-card__media">
                    @php
                        $secondaryImage = $secondary?->primaryGalleryMedia?->url;
                    @endphp

                    <x-store.image
                            :src="$secondaryImage"
                            :alt="$secondary?->name ?? 'محصول جانان'"
                            fallback-class="discovery-card__placeholder"
                            fallback="JANAN"
                        />
                </div>

                <div class="discovery-card__overlay" aria-hidden="true"></div>

                <div class="discovery-card__top">
                    <span>02</span>
                    <span>NEXT PICK</span>
                </div>

                <div class="discovery-card__body">
                    <div>
                        <span class="discovery-card__kicker">
                            {{ $secondary?->category?->name ?? 'COLLECTION' }}
                        </span>

                        <h3>
                            {{ $secondary?->name ?? 'ادامه انتخاب‌های جانان' }}
                        </h3>

                        <p>
                            یک مسیر بعدی برای کشف محصولات تازه‌تر از کاتالوگ جانان.
                        </p>
                    </div>

                    <span class="discovery-card__action">
                        <span>کشف انتخاب بعدی</span>
                        <b aria-hidden="true">↗</b>
                    </span>
                </div>
            </a>

        </div>
    </div>
</section>
