@props([
    'products' => collect(),
])

@php
    $editorialProducts = collect($products)
        ->filter(fn ($item) => $item)
        ->take(5)
        ->values();
@endphp

<section
    class="editorial-tech-section"
    aria-labelledby="editorial-tech-title"
>
    <div class="container">

        <header class="editorial-tech__head">
            <div>
                <span class="eyebrow">JANAN / EDIT</span>
                <h2 id="editorial-tech-title">انتخاب‌های جانان</h2>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="editorial-tech__all"
                aria-label="مشاهده همه محصولات"
            >
                <span>↗</span>
            </a>
        </header>

        @if($editorialProducts->isNotEmpty())

            <div class="editorial-tech__grid">

                @foreach($editorialProducts as $product)

                    @php
                        $image = $product->galleryMedia?->first()?->url;
                        $label = $product->brand?->name
                            ?? $product->category?->name
                            ?? 'JANAN';
                    @endphp

                    <a
                        href="{{ route('products.index') }}"
                        class="editorial-tech__card {{ $loop->first ? 'is-featured' : '' }}"
                        aria-label="مشاهده محصولات {{ $label }}"
                    >

                        <span class="editorial-tech__media">
                            @if($image)
                                <img
                                    src="{{ $image }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                    decoding="async"
                                >
                            @else
                                <span
                                    class="editorial-tech__placeholder"
                                    aria-hidden="true"
                                >
                                    JANAN
                                </span>
                            @endif
                        </span>

                        <span
                            class="editorial-tech__shade"
                            aria-hidden="true"
                        ></span>

                        <span
                            class="editorial-tech__index"
                            aria-hidden="true"
                        >
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>

                        <span
                            class="editorial-tech__arrow"
                            aria-hidden="true"
                        >
                            ↗
                        </span>

                    </a>

                @endforeach

            </div>

        @else

            <a
                href="{{ route('products.index') }}"
                class="editorial-tech__empty"
            >
                <span>JANAN</span>
                <b>مشاهده محصولات</b>
                <i aria-hidden="true">↗</i>
            </a>

        @endif

    </div>
</section>
