@props(['product'])

@php
    $model = $product->primaryActiveVariant;
    $image = $product->primaryGalleryMedia?->url ?: $model?->primaryGalleryMedia?->url;
    $url = route('products.show', $product);
    $price = (float) ($product->minimum_effective_price ?? $model?->effective_price ?? 0);
    $hasPrice = $price > 0;
    $hasOptions = (int) $product->active_variants_count > 1;
    $priceVaries = $hasPrice && (float) $product->maximum_effective_price > $price;
    $canPurchase = $model && $model->stock > 0;
@endphp

<article class="home-product-card">
    <a href="{{ $url }}" class="home-product-card__image" aria-label="مشاهده {{ $product->name }}">
        <x-store.image
            :src="$image"
            alt=""
            fallback-class="home-product-card__placeholder"
        />
    </a>

    <div class="home-product-card__panel">
        <h3><a href="{{ $url }}">{{ $product->name }}</a></h3>

        <div class="home-product-card__purchase">
            @if($hasPrice)
                <span class="home-product-card__price">
                    @if($priceVaries) از @endif
                    {{ \App\Support\PersianNumber::format($price) }} تومان
                </span>
            @else
                <span class="home-product-card__price">قیمت در دسترس نیست</span>
            @endif

            @if(!$hasPrice || !$model)
                <a class="home-product-card__action" href="{{ $url }}">جزئیات محصول</a>
            @elseif($hasOptions && $product->active_stock_total > 0)
                <a class="home-product-card__action" href="{{ $url }}">انتخاب مدل</a>
            @elseif($canPurchase)
                <form
                    method="POST"
                    action="{{ route('cart.store', $model) }}"
                    class="quick-add-form"
                    data-cart-add
                    data-product-name="{{ $product->name }}"
                    data-product-image="{{ $image ?? '' }}"
                >
                    @csrf
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="home-product-card__action">افزودن به سبد</button>
                </form>
            @else
                <span class="home-product-card__unavailable">ناموجود</span>
            @endif
        </div>
    </div>
</article>
