@props([
'brands',
'titleId' => 'home-brands-title',
])

@php
    $visibleBrands = $brands->take(6);
@endphp

<section
    class="home-brands"
    aria-labelledby="{{ $titleId }}"
>
    <div class="container">

        <header class="section-head home-brands__head">

            <div class="home-brands__intro">


                <h2 id="{{ $titleId }}">
                    برندهای جانان.
                </h2>

                <p>
                    نام‌هایی با شخصیت مستقل، انتخاب‌شده برای جهان جانان.
                </p>
            </div>


        </header>

        @if($visibleBrands->isNotEmpty())

            <div class="home-brand-rail">

                @foreach($visibleBrands as $brand)

                    <a
                        href="{{ route('products.index', ['brand' => $brand->slug]) }}"
                        class="home-brand-tile {{ $loop->first ? 'is-featured' : '' }}"
                    >

                        <span
                            class="home-brand-tile__number"
                            aria-hidden="true"
                        >
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>

                        <div class="home-brand-tile__logo">

                            <x-store.image
                                :src="$brand->logoMedia?->url"
                                :alt="$brand->name"
                                fallback-tag="span"
                                fallback-class="home-brand-tile__initial"
                                :fallback="mb_substr($brand->name, 0, 1)"
                            />

                        </div>

                        <div class="home-brand-tile__footer">

                            <div class="home-brand-tile__name">
                                <strong>
                                    {{ $brand->name }}
                                </strong>

                                <span>
                                    {{ \App\Support\PersianNumber::format($brand->active_products_count) }}
                                    محصول
                                </span>
                            </div>


                        </div>

                    </a>

                @endforeach

            </div>

        @else

            <div class="empty-state">


                <h2>
                    برندی برای نمایش نیست.
                </h2>
            </div>

        @endif

    </div>
</section>
