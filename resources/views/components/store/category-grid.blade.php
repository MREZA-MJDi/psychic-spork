@props([
'categories',
'titleId' => 'home-collections-title',
])

@php
    $visibleCategories = $categories->take(5);
@endphp

<section
    class="section-block section-block--no-top home-collections"
    aria-labelledby="{{ $titleId }}"
>
    <div class="container">

        <div class="section-head home-section-head">

            <div class="home-section-head__copy">
                <span class="eyebrow">
                    COLLECTIONS / 01
                </span>

                <h2 id="{{ $titleId }}">
                    دسته‌بندی را انتخاب کن.
                </h2>

                <p>
                    چند مسیر برای رسیدن به انتخابی که دقیقاً حال تو را دارد.
                </p>
            </div>

            <a
                href="{{ route('categories.index') }}"
                class="text-link"
            >
                همه دسته‌بندی‌ها
                <span aria-hidden="true">↗</span>
            </a>

        </div>

        @if($visibleCategories->isNotEmpty())

            <div class="home-category-rail">

                @foreach($visibleCategories as $category)

                    <a
                        href="{{ route('categories.show', $category) }}"
                        class="home-category-card {{ $loop->first ? 'is-featured' : '' }}"
                    >

                        <div class="home-category-card__media">

                            @if($category->coverMedia?->url)

                                <img
                                    src="{{ $category->coverMedia->url }}"
                                    alt="{{ $category->name }}"
                                    loading="lazy"
                                    decoding="async"
                                >

                            @else

                                <div
                                    class="card-image-placeholder"
                                    aria-hidden="true"
                                >
                                    <span>
                                        {{ $category->name }}
                                    </span>
                                </div>

                            @endif

                            <span
                                class="home-category-card__index"
                                aria-hidden="true"
                            >
                                {{ sprintf('%02d', $loop->iteration) }}
                            </span>

                            <span
                                class="home-category-card__veil"
                                aria-hidden="true"
                            ></span>

                        </div>

                        <div class="home-category-card__body">

                            <div class="home-category-card__meta">

                                <small>
                                    {{ number_format($category->active_products_count) }}
                                    ITEMS
                                </small>

                                <h3>
                                    {{ $category->name }}
                                </h3>

                            </div>

                            <span
                                class="home-category-card__arrow"
                                aria-hidden="true"
                            >
                                ↗
                            </span>

                        </div>

                    </a>

                @endforeach

            </div>

        @else

            <div class="empty-state">
                <span class="eyebrow">COLLECTIONS / EMPTY</span>

                <h2>
                    هنوز دسته‌بندی فعالی ثبت نشده.
                </h2>
            </div>

        @endif

    </div>
</section>
