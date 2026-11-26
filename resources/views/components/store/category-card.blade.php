@props(['category'])

<a class="janan-category-card" href="{{ route('categories.show', $category) }}">
    <div class="janan-category-card__frame" style="--category-cover-ratio: {{ \App\Support\CategoryCover::CSS_RATIO }}">
        <x-store.image :src="$category->coverMedia?->url" alt="" fallback-class="janan-category-card__placeholder" :fallback="mb_substr($category->name, 0, 1)" />
    </div>
    <div class="janan-category-card__body">
        <div>
            <h3>{{ \App\Support\PersianNumber::digits($category->name) }}</h3>
            <span class="janan-category-card__count">{{ \App\Support\PersianNumber::format($category->active_products_count) }} محصول</span>
        </div>
        <div class="janan-category-card__arrow" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M19 12H5m6 6-6-6 6-6"/></svg></div>
    </div>
</a>
