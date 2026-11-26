@props([
    'categories',
    'titleId' => 'home-collections-title',
])

@php($visibleCategories = $categories->take(5))

<section class="home-collections home-collections--curated" aria-labelledby="{{ $titleId }}">
    <div class="container">
        <div class="home-collections__panel">
            <header class="home-collections__heading">
                <div class="home-collections__intro">
                    <span class="home-collections__eyebrow"><i aria-hidden="true"></i> کالکشن‌های جانان</span>
                    <h2 id="{{ $titleId }}">دسته‌بندی‌ها</h2>
                    <p>از دسته‌بندی موردعلاقه‌تان شروع کنید و مدل‌های هر مجموعه را کشف کنید.</p>
                </div>
                <a href="{{ route('categories.index') }}" class="home-collections__browse">
                    <span>همه دسته‌بندی‌ها</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m6 6-6-6 6-6"/></svg>
                </a>
            </header>

            @if($visibleCategories->isNotEmpty())
                <div class="home-collections__gallery">
                    <div class="janan-category-grid janan-category-grid--home" data-category-count="{{ $visibleCategories->count() }}">
                        @foreach($visibleCategories as $category)
                            <x-store.category-card :category="$category" />
                        @endforeach
                    </div>
                </div>
                <div class="home-collections__foot">
                    <span>{{ \App\Support\PersianNumber::format($visibleCategories->count()) }} دسته‌بندی برای مرور</span>
                    <a href="{{ route('products.index') }}">مشاهده همه محصولات <span aria-hidden="true">←</span></a>
                </div>
            @else
                <div class="home-collections__empty">
                    <p>هنوز دسته‌بندی فعالی ثبت نشده است.</p>
                    <a href="{{ route('products.index') }}" class="home-collections__browse">مشاهده محصولات</a>
                </div>
            @endif
        </div>
    </div>
</section>
