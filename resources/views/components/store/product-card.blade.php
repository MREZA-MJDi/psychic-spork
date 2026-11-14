@props([
    'product',
    'variant' => 'default',
])

@php
    $productVariant = $product->primaryActiveVariant;
    $image = $product->primaryGalleryMedia?->url
        ?: $productVariant?->primaryGalleryMedia?->url;
    $activeVariants = $product->relationLoaded('activeVariants')
        ? $product->activeVariants
        : collect();

    $variantCount = $product->getAttribute('active_variants_count');
    $colorCount = $product->getAttribute('active_variant_colors_count');
    $sizeCount = $product->getAttribute('active_variant_sizes_count');
    $stockMetric = $product->getAttribute('active_stock_total');
    $minimumMetric = $product->getAttribute('minimum_effective_price');
    $maximumMetric = $product->getAttribute('maximum_effective_price');

    $variantCount = $variantCount !== null
        ? (int) $variantCount
        : $activeVariants->count();
    $colorCount = $colorCount !== null
        ? (int) $colorCount
        : $activeVariants->pluck('color')->filter(fn ($value) => filled(trim((string) $value)))->map(fn ($value) => mb_strtolower(trim((string) $value)))->unique()->count();
    $sizeCount = $sizeCount !== null
        ? (int) $sizeCount
        : $activeVariants->pluck('size')->filter(fn ($value) => filled(trim((string) $value)))->map(fn ($value) => mb_strtolower(trim((string) $value)))->unique()->count();

    $stock = $stockMetric !== null
        ? (int) $stockMetric
        : (int) ($productVariant?->stock ?? 0);
    $minimumPrice = $minimumMetric !== null
        ? (float) $minimumMetric
        : ($activeVariants->isNotEmpty()
            ? $activeVariants->min(fn ($item) => (float) $item->effective_price)
            : ($productVariant?->effective_price ?? null));
    $minimumPrice = $minimumPrice !== null && (float) $minimumPrice > 0
        ? $minimumPrice
        : null;
    $maximumPrice = $maximumMetric !== null
        ? (float) $maximumMetric
        : ($activeVariants->isNotEmpty()
            ? $activeVariants->max(fn ($item) => (float) $item->effective_price)
            : $minimumPrice);
    $priceVaries = $minimumPrice !== null && $maximumPrice !== null && $maximumPrice > $minimumPrice;

    $isOnSale = $productVariant?->is_on_sale
        && $minimumPrice !== null
        && (float) $productVariant->effective_price === (float) $minimumPrice;
    $isLowStock = $stock > 0
        && $stock <= (int) ($productVariant?->low_stock_threshold ?? 0);

    $variantFacts = array_values(array_filter([
        $colorCount > 1 ? number_format($colorCount).' رنگ' : null,
        $sizeCount > 1 ? number_format($sizeCount).' سایز' : null,
        $colorCount === 0 && $sizeCount === 0 && $variantCount > 1
            ? number_format($variantCount).' گزینه'
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
            @elseif($isLowStock)
                <div class="product-card__stock is-low" role="status" aria-label="موجودی محدود">
                    موجودی محدود
                </div>
            @endif

            @if($variantCount > 1)
                <a
                    class="quick-add"
                    href="{{ route('products.show', $product) }}"
                >
                    انتخاب مدل
                </a>
            @else
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
            @endif

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
