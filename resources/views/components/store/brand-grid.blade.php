@props([
'brands',
'titleId' => 'home-brands-title',
])

@php
    $visibleBrands = $brands->take(6);
@endphp

<section
    class="section-block section-block--compact home-brands"
    aria-labelledby="{{ $titleId }}"
>
    <div class="container">

        <header class="section-head home-brands__head">

            <div class="home-brands__intro">
                <span class="eyebrow">
                    HOUSES / 02
                </span>

                <h2 id="{{ $titleId }}">
                    برندهای جانان.
                </h2>

                <p>
                    نام‌هایی با شخصیت مستقل، انتخاب‌شده برای جهان جانان.
                </p>
            </div>

            <a
                href="{{ route('brands.index') }}"
                class="text-link"
            >
                همه برندها
                <span aria-hidden="true">↗</span>
            </a>

        </header>

        @if($visibleBrands->isNotEmpty())

            <div class="home-brand-rail">

                @foreach($visibleBrands as $brand)

                    <a
                        href="{{ route('brands.show', $brand) }}"
                        class="home-brand-tile {{ $loop->first ? 'is-featured' : '' }}"
                    >

                        <span
                            class="home-brand-tile__number"
                            aria-hidden="true"
                        >
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>

                        <div class="home-brand-tile__logo">

                            @if($brand->logoMedia?->url)

                                <img
                                    src="{{ $brand->logoMedia->url }}"
                                    alt="{{ $brand->name }}"
                                    loading="lazy"
                                    decoding="async"
                                >

                            @else

                                <span class="home-brand-tile__initial">
                                    {{ mb_substr($brand->name, 0, 1) }}
                                </span>

                            @endif

                        </div>

                        <div class="home-brand-tile__footer">

                            <div class="home-brand-tile__name">
                                <strong>
                                    {{ $brand->name }}
                                </strong>

                                <span>
                                    {{ number_format($brand->active_products_count) }}
                                    محصول
                                </span>
                            </div>

                            <span
                                class="home-brand-tile__arrow"
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
                <span class="eyebrow">HOUSES / EMPTY</span>

                <h2>
                    برندی برای نمایش نیست.
                </h2>
            </div>

        @endif

    </div>
</section>
