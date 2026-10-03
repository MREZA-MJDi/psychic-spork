@props([
    'product',
    'variant' => 'default',
])

@php
    $productVariant = $product->primaryActiveVariant;
    $image = $product->primaryGalleryMedia?->url;
    $stock = (int) ($productVariant?->stock ?? 0);
    $isOnSale = (bool) ($productVariant?->is_on_sale ?? false);
    $activeVariants = $product->relationLoaded('activeVariants')
        ? $product->activeVariants
        : collect();
    $colorCount = $activeVariants->pluck('color')
        ->filter(fn ($value) => filled(trim((string) $value)))
        ->map(fn ($value) => mb_strtolower(trim((string) $value)))
        ->unique()
        ->count();
    $sizeCount = $activeVariants->pluck('size')
        ->filter(fn ($value) => filled(trim((string) $value)))
        ->map(fn ($value) => mb_strtolower(trim((string) $value)))
        ->unique()
        ->count();
    $variantPrices = $activeVariants
        ->map(fn ($item) => (float) $item->effective_price)
        ->filter(fn ($price) => $price > 0)
        ->values();
    $minimumPrice = $variantPrices->isNotEmpty()
        ? $variantPrices->min()
        : ($productVariant?->effective_price ?? null);
    $minimumPrice = $minimumPrice !== null && (float) $minimumPrice > 0
        ? $minimumPrice
        : null;
    $maximumPrice = $variantPrices->isNotEmpty()
        ? $variantPrices->max()
        : $minimumPrice;
    $priceVaries = $minimumPrice !== null && $maximumPrice !== null && $maximumPrice > $minimumPrice;
    $variantFacts = array_values(array_filter([
        $colorCount > 1 ? number_format($colorCount).' رنگ' : null,
        $sizeCount > 1 ? number_format($sizeCount).' سایز' : null,
        $colorCount === 0 && $sizeCount === 0 && $activeVariants->count() > 1
            ? number_format($activeVariants->count()).' گزینه'
            : null,
    ]));

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
            aria-label="مشاهده {{ $product->name }}"
        >
            <x-store.image
                :src="$image"
                :alt="$product->name"
                fallback-class="product-image-placeholder"
                fallback="JANE JANAN"
            />
        </a>

    </div>

    <div class="product-card__body">

        <div class="product-card__topline">
            @if($product->brand?->name)
                <span class="product-card__brand">{{ $product->brand->name }}</span>
            @endif
            @if($product->category?->name)
                <span class="product-card__category">{{ $product->category->name }}</span>
            @endif
        </div>

        @if(count($variantFacts))
            <div class="product-card__facts" aria-label="{{ implode('، ', $variantFacts) }}">
                @foreach($variantFacts as $fact)
                    <span>{{ $fact }}</span>
                @endforeach
            </div>
        @endif

        <a
            href="{{ route('products.show', $product) }}"
            class="product-card__title"
        >
            {{ $product->name }}
        </a>

        @if($minimumPrice !== null)

            <div class="product-price">
                <strong>
                    @if($priceVaries)
                        <span class="product-price__prefix">از</span>
                    @endif
                    {{ number_format($minimumPrice) }}
                </strong>

                <span>تومان</span>

                @if($isOnSale && !$priceVaries)
                    <del>
                        {{ number_format($productVariant->price) }}
                    </del>
                @endif
            </div>

            @if($stock < 1)
                <div class="product-card__stock is-out" role="status" aria-label="ناموجود">
                    ناموجود
                </div>
            @elseif($productVariant->is_low_stock)
                <div class="product-card__stock is-low" role="status" aria-label="موجودی محدود">
                    موجودی محدود
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('cart.store', $productVariant) }}"
                class="quick-add-form"
                data-cart-add
                data-product-name="{{ $product->name }}"
                data-product-image="{{ $image ?? '' }}"
            >
                @csrf

                <input
                    type="hidden"
                    name="quantity"
                    value="1"
                >

                <button
                    type="submit"
                    class="quick-add"
                    @disabled($stock < 1)
                >
                    {{ $stock > 0 ? 'افزودن به سبد خرید' : 'ناموجود' }}
                </button>
            </form>

        @else

            <span class="product-card__no-variant">
                اطلاعات قیمت در دسترس نیست.
            </span>

            <a
                class="product-card__no-price-link"
                href="{{ route('products.show', $product) }}"
            >
                مشاهده جزئیات محصول
                <span aria-hidden="true">←</span>
            </a>

        @endif

    </div>
</article>
