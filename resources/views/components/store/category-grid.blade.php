@props([
    'categories',
    'titleId' => 'home-collections-title',
])

@php($visibleCategories = $categories->take(3))
@php($showDirectoryCard = $visibleCategories->count() === 2)

<section class="home-collections home-collections--curated" aria-labelledby="{{ $titleId }}">
    <div class="container">
        <header class="home-collections__heading">
            <h2 id="{{ $titleId }}">دسته‌بندی‌ها</h2>
            @unless($showDirectoryCard)
                <a href="{{ route('categories.index') }}" class="home-collections__browse">همه دسته‌بندی‌ها</a>
            @endunless
        </header>

        @if($visibleCategories->isNotEmpty())
            <div class="janan-category-grid janan-category-grid--home" data-category-count="{{ $visibleCategories->count() }}">
                @foreach($visibleCategories as $category)
                    <x-store.category-card :category="$category" />
                @endforeach
                @if($showDirectoryCard)
                    <a class="janan-category-card" href="{{ route('categories.index') }}">
                        <div class="janan-category-card__frame home-collections__directory-frame" style="--category-cover-ratio: {{ \App\Support\CategoryCover::CSS_RATIO }}" aria-hidden="true"></div>
                        <div class="janan-category-card__body"><h3>همه دسته‌بندی‌ها</h3></div>
                    </a>
                @endif
            </div>
        @else
            <p class="home-collections__empty">هنوز دسته‌بندی فعالی ثبت نشده است.</p>
        @endif
    </div>
</section>
