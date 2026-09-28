@props([
    'products',
])

@php
    $products = collect($products)->take(8)->values();
@endphp

<section
    class="home-product-section"
    aria-labelledby="home-products-title"
>
    <div class="container">

        <header class="home-product-section__head">
            <div>
                <span class="eyebrow">JANAN / EDIT 03</span>

                <h2 id="home-products-title">
                    محصولات منتخب
                </h2>

                <p>
                    مجموعه‌ای از محصولات فعال و منتخب جانان؛ ساده، قابل مقایسه و آماده‌ی خرید.
                </p>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                همه محصولات
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        @if($products->isNotEmpty())

            <div class="home-product-grid">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>

            <div class="home-product-section__footer">
                <a
                    href="{{ route('products.index') }}"
                    class="button button--ghost"
                >
                    مشاهده همه محصولات
                    <span aria-hidden="true">↗</span>
                </a>
            </div>

        @else

            <div class="empty-state">
                <span class="eyebrow">JANAN COLLECTION</span>

                <h2>
                    هنوز محصول فعالی برای نمایش وجود ندارد.
                </h2>

                <a
                    href="{{ route('products.index') }}"
                    class="button button--primary"
                >
                    مشاهده محصولات
                </a>
            </div>

        @endif

    </div>
</section>
