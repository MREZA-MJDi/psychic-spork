@php
    $editorialMedia = $product?->galleryMedia?->first();
    $editorialImage = $editorialMedia?->url;
    $editorialAlt = $product?->name ?: 'کالکشن جانان';
@endphp

<section class="home-section home-section--editorial">
    <div class="container">
        <div class="editorial reveal-up">
            <div class="editorial__image">
                @if($editorialImage)
                    <img src="{{ $editorialImage }}" alt="{{ $editorialAlt }}" loading="lazy">
                @else
                    <div class="visual-placeholder"><span>JANAN</span><strong>THE ART OF DETAILS</strong></div>
                @endif
            </div>
            <div class="editorial__content">
                <span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / THE ART OF DETAILS</span>
                <h2>لطافت خاص، برای زنانی که تفاوت را احساس می‌کنند.</h2>
                <p>در جانان، انتخاب پارچه، فرم و جزئیات بخشی از تجربه‌ای است که قرار است هر بار با آن احساس بهتری داشته باشید.</p>
                <a href="{{ route('products.index') }}" class="button button--dark">کشف کالکشن</a>
            </div>
        </div>
    </div>
</section>
