@props([
    'categories',
    'titleId' => 'home-collections-title',
])

@php
    $visibleCategories = $categories->take(5);
@endphp

<section
    class="store-catalog-section"
    aria-labelledby="{{ $titleId }}"
>
    <div class="container">

        <header class="store-section-heading">
            <div>
                <span class="eyebrow">COLLECTIONS / 01</span>
                <h2 id="{{ $titleId }}">دسته‌بندی را انتخاب کن.</h2>
                <p>چند مسیر مشخص برای رسیدن به انتخابی که دقیقاً برای توست.</p>
            </div>

            <a href="{{ route('categories.index') }}" class="text-link">
                همه دسته‌بندی‌ها
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        @if($visibleCategories->isNotEmpty())
            <div class="catalog-collection-grid">
                @foreach($visibleCategories as $category)
                    <a
                        href="{{ route('categories.show', $category) }}"
                        class="catalog-collection-card {{ $loop->first ? 'is-featured' : '' }}"
                    >
                        <div class="catalog-collection-card__media">
                            @if($category->coverMedia?->url)
                                <img
                                    src="{{ $category->coverMedia->url }}"
                                    alt="{{ $category->name }}"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <div class="catalog-collection-card__placeholder">
                                    <span>{{ $category->name }}</span>
                                </div>
                            @endif

                            <span
                                class="catalog-collection-card__veil"
                                aria-hidden="true"
                            ></span>
                        </div>

                        <div class="catalog-collection-card__body">
                            <small>JANAN / COLLECTION {{ sprintf('%02d', $loop->iteration) }}</small>
                            <h2>{{ $category->name }}</h2>
                            <p>
                                {{ $category->description ?: 'منتخب محصولات این کالکشن را ببینید.' }}
                            </p>

                            <div class="catalog-collection-card__meta">
                                <span>
                                    {{ number_format($category->active_products_count) }}
                                    محصول
                                </span>
                                <strong aria-hidden="true">↗</strong>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <span class="eyebrow">COLLECTIONS / EMPTY</span>
                <h2>هنوز دسته‌بندی فعالی ثبت نشده.</h2>
            </div>
        @endif

    </div>
</section>
