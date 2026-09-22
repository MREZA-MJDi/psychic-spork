<section class="section-block section-block--compact">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">BRANDS</span><h2>برندهای جانان</h2><p>برندهای فعال فروشگاه.</p></div>
            <a href="{{ route('brands.index') }}" class="text-link">مشاهده همه <span>←</span></a>
        </div>

        @if($brands->isNotEmpty())
            <div class="brand-grid">
                @foreach($brands as $brand)
                    <a class="brand-card reveal-up" href="{{ route('brands.show', $brand) }}">
                        <div class="brand-card__logo">
                            @if($brand->logoMedia?->url)
                                <img src="{{ $brand->logoMedia->url }}" alt="{{ $brand->name }}" loading="lazy">
                            @else
                                <span class="brand-card__fallback">{{ mb_substr($brand->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="brand-card__body">
                            <h3>{{ $brand->name }}</h3>
                            <p>{{ $brand->description ?: 'برای این برند معرفی عمومی ثبت نشده است.' }}</p>
                            <div class="brand-card__meta"><span>{{ number_format($brand->active_products_count) }} محصول</span><b class="brand-card__arrow">←</b></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h2>برندی برای نمایش نیست.</h2></div>
        @endif
    </div>
</section>
