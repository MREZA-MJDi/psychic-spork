@php($variant = $product->variants->first())

<article class="product-card">
    <div class="product-card__media">
        @if($variant?->is_on_sale)
            <span class="sale-pill">{{ max(1, round((1 - ($variant->effective_price / max(1,(float)$variant->price))) * 100)) }}٪</span>
        @endif

        <a href="{{ route('products.show',$product) }}" class="product-card__image-link">
            @if($product->galleryMedia->first()?->url)
                <img src="{{ $product->galleryMedia->first()->url }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <div class="product-image-placeholder" aria-label="{{ $product->name }}"><span>{{ $product->name }}</span></div>
            @endif
        </a>

        @if($variant)
            <form method="POST" action="{{ route('cart.store',$variant) }}" class="quick-add-form">
                @csrf
                <button class="quick-add" type="submit" {{ $variant->stock < 1 ? 'disabled' : '' }}>{{ $variant->stock > 0 ? 'افزودن به سبد' : 'ناموجود' }}</button>
            </form>
        @endif
    </div>

    <div class="product-card__body">
        <div class="product-card__topline"><span>{{ $product->category?->name ?: 'Janan' }}</span></div>
        <a href="{{ route('products.show',$product) }}" class="product-card__title">{{ $product->name }}</a>

        <div class="product-price">
            <strong>{{ number_format($variant?->effective_price ?? 0) }}</strong><span>تومان</span>
            @if($variant?->is_on_sale)<del>{{ number_format($variant->price) }}</del>@endif
        </div>

        @if($variant)
            <div class="product-card__stock {{ $variant->stock < 1 ? 'is-out' : ($variant->is_low_stock ? 'is-low' : '') }}">
                {{ $variant->stock < 1 ? 'ناموجود' : ($variant->is_low_stock ? 'موجودی محدود' : 'موجود') }}
            </div>
        @endif
    </div>
</article>
