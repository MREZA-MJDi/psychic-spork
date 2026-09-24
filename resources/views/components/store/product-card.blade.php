@php
    $variant = $product->variants->first();
    $image = $product->galleryMedia->first()?->url;

    $isOnSale = $variant?->is_on_sale;
    $stock = (int) ($variant?->stock ?? 0);

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
@endphp

<article class="product-card">

    <div class="product-card__media">

        @if($isOnSale)
            <span class="sale-pill">
                {{ $discount }}٪
            </span>
        @endif

        <a
            href="{{ route('products.show', $product) }}"
            class="product-card__image-link"
            aria-label="{{ $product->name }}"
        >
            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <div
                    class="product-image-placeholder"
                    aria-hidden="true"
                >
                    <span>JANAN</span>
                </div>
            @endif
        </a>

        @if($variant)
            <form
                method="POST"
                action="{{ route('cart.store', $variant) }}"
                class="quick-add-form"
            >
                @csrf

                <button
                    type="submit"
                    class="quick-add"
                    @disabled($stock < 1)
                >
                {{ $stock > 0 ? 'افزودن به سبد' : 'ناموجود' }}
                </button>
            </form>
        @endif

    </div>


    <div class="product-card__body">

        <div class="product-card__topline">
            <span>
                {{ $product->category?->name ?? 'Janan' }}
            </span>
        </div>

        <a
            href="{{ route('products.show', $product) }}"
            class="product-card__title"
        >
            {{ $product->name }}
        </a>

        @if($variant)
            <div class="product-price">

                <strong>
                    {{ number_format($variant->effective_price) }}
                </strong>

                <span>تومان</span>

                @if($isOnSale)
                    <del>
                        {{ number_format($variant->price) }}
                    </del>
                @endif

            </div>

            <div
                class="product-card__stock {{ match (true) {
                    $stock < 1 => 'is-out',
                    $variant->is_low_stock => 'is-low',
                    default => ''
                } }}"
            >
                {{ match (true) {
                    $stock < 1 => 'ناموجود',
                    $variant->is_low_stock => 'موجودی محدود',
                    default => 'موجود'
                } }}
            </div>
        @endif

    </div>

</article>
