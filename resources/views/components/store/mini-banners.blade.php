@php
    $latestImage = $latestProduct?->galleryMedia?->first()?->url;
    $category = $categories->first();
    $categoryImage = $category?->coverMedia?->url;
@endphp

<section class="section-block section-block--tight">
    <div class="container">
        <div class="mini-banner-grid">
            <a class="mini-banner reveal-up" href="{{ route('products.index') }}">
                @if($latestImage)
                    <img src="{{ $latestImage }}" alt="{{ $latestProduct->name }}" loading="lazy">
                @else
                    <div class="mini-banner__placeholder"><span>JANAN</span></div>
                @endif
                <span class="mini-banner__veil"></span>
                <div><span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / NEW IN</span><h3>تازه‌های جانان</h3><span>مشاهده محصولات ←</span></div>
            </a>

            <a class="mini-banner mini-banner--soft reveal-up" style="--delay:.08s" href="{{ $category ? route('categories.show',$category) : route('categories.index') }}">
                @if($categoryImage)
                    <img src="{{ $categoryImage }}" alt="{{ $category->name }}" loading="lazy">
                @else
                    <div class="mini-banner__placeholder"><span>{{ $category?->name ?: 'JANAN' }}</span></div>
                @endif
                <span class="mini-banner__veil"></span>
                <div><span class="eyebrow">EDITED FOR YOU</span><h3>{{ $category?->name ?: 'انتخاب‌های خاص' }}</h3><span>دیدن انتخاب‌ها ←</span></div>
            </a>
        </div>
    </div>
</section>
