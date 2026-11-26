<article class="wholesale-product-card">
    <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__media">
        <x-store.image
            :src="$product->primaryGalleryMedia?->url ?: $product->primaryActiveVariant?->primaryGalleryMedia?->url"
            :alt="$product->name"
            fallback-tag="span"
            fallback-class=""
            fallback="JANAN"
        />
    </a>
    <div class="wholesale-product-card__body">
        <div class="wholesale-product-card__meta">
            <span>{{ $product->brand?->name ?? 'Janan' }}</span>
            <span>{{ $product->category?->name ?? 'محصول' }}</span>
        </div>
        <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__title">{{ $product->name }}</a>
        <div class="wholesale-product-card__facts">
            <span>{{ number_format((int) $product->wholesale_variants_count) }} تنوع دارای قیمت عمده</span>
        </div>
        <div class="wholesale-variant-price">
            @if((float) $product->minimum_wholesale_price === (float) $product->maximum_wholesale_price)
                {{ number_format((float) $product->minimum_wholesale_price) }}
            @else
                {{ number_format((float) $product->minimum_wholesale_price) }}
                تا
                {{ number_format((float) $product->maximum_wholesale_price) }}
            @endif
            <small>تومان</small>
        </div>
        <a class="button button--ghost wholesale-product-card__action" href="{{ route('products.show', $product) }}">
            مشاهده رنگ و سایزها
            <span aria-hidden="true">↗</span>
        </a>
    </div>
</article>
