@props([
    'variant' => 'default',
])

@php
    $productVariant = $product->variants->first();
    $image = $product->galleryMedia->first()?->url;

    $isOnSale = $productVariant?->is_on_sale;
    $stock = (int) ($productVariant?->stock ?? 0);

    $discount = $isOnSale
        ? max(
            1,
            round(
                (1 - (
                    $productVariant->effective_price /
                    max(1, (float) $productVariant->price)
                )) * 100
            )
        )
        : 0;
@endphp

<article class="product-card product-card--{{ $variant }}">

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

        @if($productVariant)
            <form
                method="POST"
                action="{{ route('cart.store', $productVariant) }}"
                class="quick-add-form"
                data-cart-add
            >
                @csrf
                <input type="hidden" name="quantity" value="1">

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

        @if($productVariant)
            <div class="product-price">
                <strong>
                    {{ number_format($productVariant->effective_price) }}
                </strong>

                <span>تومان</span>

                @if($isOnSale)
                    <del>
                        {{ number_format($productVariant->price) }}
                    </del>
                @endif
            </div>

            <div
                class="product-card__stock {{ match (true) {
                    $stock < 1 => 'is-out',
                    $productVariant->is_low_stock => 'is-low',
                    default => ''
                } }}"
            >
                {{ match (true) {
                    $stock < 1 => 'ناموجود',
                    $productVariant->is_low_stock => 'موجودی محدود',
                    default => 'موجود'
                } }}
            </div>
        @endif

    </div>
</article>
