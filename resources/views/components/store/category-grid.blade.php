@props([
    'categories',
    'titleId' => 'home-collections-title',
])

@php
    $visibleCategories = $categories->take(5);
@endphp

<section class="home-collections" aria-labelledby="{{ $titleId }}">
    <div class="container">
        <div class="section-head home-section-head">
            <div class="home-section-head__copy">
                <h2 id="{{ $titleId }}">دسته‌بندی‌ها</h2>
            </div>
            <a href="{{ route('categories.index') }}" class="text-link">همه دسته‌بندی‌ها</a>
        </div>

        @if($visibleCategories->isNotEmpty())
            <div class="home-category-grid">
                @foreach($visibleCategories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="home-collection-link">
                        <div class="home-collection-link__art" aria-hidden="true">
                            <x-store.image
                                :src="$category->coverMedia?->url"
                                alt=""
                                fallback-class="home-collection-link__fallback"
                                fallback=""
                            />
                        </div>
                        <h3>{{ $category->name }}</h3>
                        <span>{{ \App\Support\PersianNumber::format($category->active_products_count) }} محصول</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="empty-state">هنوز دسته‌بندی فعالی ثبت نشده.</p>
        @endif
    </div>
</section>
