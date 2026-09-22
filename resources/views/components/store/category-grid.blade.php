<section class="section-block section-block--no-top">
    <div class="container">
        <div class="section-head">
            <div><span class="eyebrow">EXPLORE</span><h2>دسته‌بندی محصولات</h2><p>دسته‌بندی‌های فعال فروشگاه.</p></div>
            <a href="{{ route('categories.index') }}" class="text-link">مشاهده همه <span>←</span></a>
        </div>

        @if($categories->isNotEmpty())
            <div class="category-grid">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="category-card reveal-up">
                        <div class="category-card__image">
                            @if($category->coverMedia?->url)
                                <img src="{{ $category->coverMedia->url }}" alt="{{ $category->name }}" loading="lazy">
                            @else
                                <div class="card-image-placeholder"><span>{{ $category->name }}</span></div>
                            @endif
                        </div>
                        <div class="category-card__content">
                            <div><h3>{{ $category->name }}</h3><span>{{ $category->description ?: 'کالکشن جانان' }}</span></div>
                            <strong>{{ number_format($category->active_products_count) }}</strong>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h2>هنوز دسته‌بندی فعالی ثبت نشده.</h2></div>
        @endif
    </div>
</section>
