@props([
    'categories',
    'titleId' => 'home-collections-title',
])

@php($visibleCategories = $categories->take(3))

<section class="home-collections home-collections--curated" aria-labelledby="{{ $titleId }}">
    <div class="container">
        <header class="home-collections__heading">
            <h2 id="{{ $titleId }}">دسته‌بندی‌ها</h2>
            <a href="{{ route('categories.index') }}" class="home-collections__browse">همه دسته‌بندی‌ها</a>
        </header>

        @if($visibleCategories->isNotEmpty())
            <div class="janan-category-grid janan-category-grid--home">
                @foreach($visibleCategories as $category)
                    <x-store.category-card :category="$category" />
                @endforeach
            </div>
        @else
            <p class="home-collections__empty">هنوز دسته‌بندی فعالی ثبت نشده است.</p>
        @endif
    </div>
</section>
