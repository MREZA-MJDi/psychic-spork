@php($visibleCategories = $categories->take(5))
<section class="section-block section-block--no-top home-collections">
    <div class="container">
        <div class="section-head home-section-head">
            <div><span class="eyebrow">COLLECTIONS / 01</span><h2>دسته‌بندی را انتخاب کن.</h2><p>چند مسیر برای رسیدن به انتخابی که دقیقاً حال تو را دارد.</p></div>
            <a href="{{ route('categories.index') }}" class="text-link">همه دسته‌بندی‌ها <span>↗</span></a>
        </div>
        @if($visibleCategories->isNotEmpty())
            <div class="home-category-rail">
                @foreach($visibleCategories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="home-category-card">
                        <div class="home-category-card__media">
                            @if($category->coverMedia?->url)
                                <img src="{{ $category->coverMedia->url }}" alt="{{ $category->name }}" loading="lazy">
                            @else
                                <div class="card-image-placeholder"><span>{{ $category->name }}</span></div>
                            @endif
                            <span class="home-category-card__index">{{ sprintf('%02d', $loop->iteration) }}</span>
                        </div>
                        <div class="home-category-card__body">
                            <div><small>{{ number_format($category->active_products_count) }} ITEMS</small><h3>{{ $category->name }}</h3></div>
                            <b>↗</b>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state"><h2>هنوز دسته‌بندی فعالی ثبت نشده.</h2></div>
        @endif
    </div>
</section>
