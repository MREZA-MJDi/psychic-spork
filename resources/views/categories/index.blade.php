@extends('layouts.store')

@section('title', 'دسته‌بندی‌ها — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium">
    <div class="container page-hero__layout">
        <div>
            <span class="eyebrow">01 / COLLECTIONS</span>
            <h1>دسته‌بندی‌های جانان</h1>
            <p>
                کالکشن‌ها را بر اساس فرم، کاربرد و حال‌وهوای انتخاب مرور کن.
            </p>
        </div>

        <div class="page-hero__stat">
            <b>{{ number_format($categories->count()) }}</b>
            <span>دسته فعال</span>
        </div>
    </div>
</section>

<section class="store-catalog-section">
    <div class="container">

        @if($categories->isNotEmpty())
            <div class="catalog-collection-grid catalog-collection-grid--page">
                @foreach($categories as $category)
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
                            <small>
                                JANAN / COLLECTION {{ sprintf('%02d', $loop->iteration) }}
                            </small>

                            <h2>{{ $category->name }}</h2>

                            <p>
                                {{ $category->description ?: 'منتخب محصولات این دسته‌بندی را ببینید.' }}
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
                <h2>دسته‌بندی فعالی وجود ندارد.</h2>
            </div>
        @endif

    </div>
</section>
@endsection
