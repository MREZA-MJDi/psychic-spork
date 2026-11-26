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
            <div class="janan-category-grid janan-category-grid--home">
                @foreach($visibleCategories as $category)
                    <x-store.category-card :category="$category" />
                @endforeach
            </div>
        @else
            <p class="empty-state">هنوز دسته‌بندی فعالی ثبت نشده.</p>
        @endif
    </div>
</section>
