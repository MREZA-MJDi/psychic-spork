@props([
    'products' => collect(),
])

@php
    $editorialProducts = collect($products)
        ->filter(fn ($item) => $item)
        ->take(3)
        ->values();
@endphp

<section
    class="janan-edit-section"
    aria-labelledby="janan-edit-title"
>
    <div class="container">

        <header class="janan-edit__head">
            <div>
                <span class="eyebrow">JANAN / EDIT</span>
                <h2 id="janan-edit-title">یک نگاه، چند انتخاب.</h2>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="janan-edit__link"
                aria-label="مشاهده همه محصولات"
            >
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        @if($editorialProducts->isNotEmpty())

            <div class="janan-edit__layout">

                @foreach($editorialProducts as $product)

                    @php
                        $image = $product->galleryMedia?->first()?->url;
                    @endphp

                    <a
                        href="{{ route('products.index') }}"
                        class="janan-edit__tile {{ $loop->first ? 'is-main' : '' }}"
                        aria-label="مشاهده همه محصولات"
                    >
                        @if($image)
                            <img
                                src="{{ $image }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <span
                                class="janan-edit__placeholder"
                                aria-hidden="true"
                            >
                                JANAN
                            </span>
                        @endif

                        <span
                            class="janan-edit__veil"
                            aria-hidden="true"
                        ></span>

                        <span
                            class="janan-edit__number"
                            aria-hidden="true"
                        >
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>

                        <span
                            class="janan-edit__arrow"
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
                class="janan-edit__fallback"
                aria-label="مشاهده همه محصولات"
            >
                <span>JANAN</span>
                <i aria-hidden="true">↗</i>
            </a>

        @endif

    </div>
</section>
