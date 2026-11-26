<article class="wholesale-pack-card">
    @php
        $firstPackVariant = $pack->items->first()?->variant;
        $packImage = $pack->image_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($pack->image_path)
            : ($firstPackVariant?->primaryGalleryMedia?->url ?? $firstPackVariant?->product?->primaryGalleryMedia?->url);
    @endphp
    <div class="wholesale-pack-card__cover">
        @if($packImage)
            <img src="{{ $packImage }}" alt="{{ $pack->image_path ? $pack->name : 'تصویر نمونه از اقلام ' . $pack->name }}" loading="lazy">
            @unless($pack->image_path)<span class="wholesale-pack-card__cover-note">تصویر نمونه از اقلام پک</span>@endunless
        @else
            <div class="wholesale-pack-card__cover-empty">برای این بسته هنوز عکسی ثبت نشده</div>
        @endif
    </div>
    <div class="wholesale-pack-card__top">
        <div>
            <span class="wholesale-pack-card__eyebrow">WHOLESALE PACK</span>
            <h3>{{ $pack->name }}</h3>
        </div>
        <strong>{{ number_format($pack->pack_quantity) }} عدد</strong>
    </div>

    @if($pack->description)
        <p class="wholesale-pack-card__description">{{ $pack->description }}</p>
    @endif

    <div class="wholesale-pack-card__items">
        @foreach($pack->items as $item)
            @php $v = $item->variant; @endphp
            <a href="{{ $v?->product ? route('products.show', $v->product) : '#' }}" class="wholesale-pack-item">
                <span class="wholesale-pack-item__media">
                    <x-store.image
                        :src="$v?->primaryGalleryMedia?->url ?? $v?->product?->primaryGalleryMedia?->url"
                        :alt="$v?->display_name ?? $v?->product?->name ?? 'واریانت پک عمده'"
                        fallback-tag="span"
                        fallback-class=""
                        fallback="JANAN"
                    />
                </span>
                <span class="wholesale-pack-item__copy">
                    <strong>{{ $v?->product?->name ?? 'محصول' }}</strong>
                    <span>{{ $v?->product?->brand?->name ?? 'بدون برند' }} · {{ $v?->display_name ?? 'واریانت' }}</span>
                    <small>SKU {{ $v?->sku ?? '—' }} · {{ $v?->stock !== null ? 'موجودی '.number_format($v->stock) : 'موجودی نامشخص' }}</small>
                </span>
                <b class="wholesale-pack-item__quantity">× {{ number_format($item->quantity) }}</b>
            </a>
        @endforeach
    </div>

    <div class="wholesale-pack-card__foot">
        <span>قیمت پک عمده</span>
        <strong>{{ number_format($pack->display_price) }} تومان</strong>
    </div>
    @php
        $packAvailable = $pack->items->isNotEmpty() && $pack->items->every(
            fn ($item) => $item->variant
                && $item->variant->is_active
                && $item->variant->product?->is_active
                && $item->variant->stock >= $item->quantity
                && $item->variant->wholesale_price !== null
        );
    @endphp
    <form method="POST" action="{{ route('cart.wholesale-packs.store', $pack) }}" class="wholesale-pack-card__purchase">
        @csrf
        <label>
            تعداد پک
            <input type="number" name="quantity" min="1" max="99" value="1" @disabled(!$packAvailable) aria-label="تعداد پک {{ $pack->name }}">
        </label>
        <button class="button button--primary" type="submit" @disabled(!$packAvailable)>
            {{ $packAvailable ? 'افزودن پک به سبد خرید' : 'موجودی کافی نیست' }}
        </button>
    </form>
</article>
