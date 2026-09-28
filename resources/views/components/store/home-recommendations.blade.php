@props([
    'products' => collect(),
])

@php
    $products = collect($products)->take(6)->values();
@endphp

<section
    class="home-recommendations-section"
    aria-labelledby="home-recommendations-title"
>
    <div class="container home-recommendations">
        <header class="home-recommendations__head">
            <div>
                <span class="eyebrow">JANAN / RECOMMENDED</span>

                <h2 id="home-recommendations-title">
                    پیشنهادهای جانان
                </h2>

                <p>
                    چند انتخاب آماده از محصولات واقعی فروشگاه؛
                    برای وقتی که می‌خواهی سریع‌تر به گزینه مناسب برسی.
                </p>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                مشاهده همه محصولات
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        @if($products->isNotEmpty())
            <div class="home-recommendations__grid">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>

            <div class="home-recommendations__footer">
                <a
                    href="{{ route('products.index') }}"
                    class="button button--ghost"
                >
                    دیدن همه محصولات
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        @else
            <div class="empty-state">
                <span class="eyebrow">JANAN / RECOMMENDED</span>

                <h2>
                    هنوز محصولی برای پیشنهاد وجود ندارد.
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
