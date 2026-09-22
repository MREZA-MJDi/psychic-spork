@php($visibleBrands = $brands->take(6))
<section class="home-section home-section--brands">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">HOUSES / 02</span><h2>برندهای جانان.</h2><p>نام‌هایی با شخصیت مستقل، انتخاب‌شده برای جهان جانان.</p></div>
            <a href="{{ route('brands.index') }}" class="text-link">همه برندها <span>↗</span></a>
        </div>
        @if($visibleBrands->isNotEmpty())
            <div class="home-brand-rail">
                @foreach($visibleBrands as $brand)
                    <a class="home-brand-tile" href="{{ route('brands.show', $brand) }}">
                        <span class="home-brand-tile__number">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <div class="home-brand-tile__logo">
                            @if($brand->logoMedia?->url)
                                <img src="{{ $brand->logoMedia->url }}" alt="{{ $brand->name }}" loading="lazy">
                            @else
                                <span>{{ mb_substr($brand->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="home-brand-tile__footer"><strong>{{ $brand->name }}</strong><span>{{ number_format($brand->active_products_count) }} محصول</span></div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h2>برندی برای نمایش نیست.</h2></div>
        @endif
    </div>
</section>
