@php
    $productsUrl = route('products.index');
    $quickItems = collect();

    if ($latestProduct) {
        $quickItems->push([
            'image' => $latestProduct->galleryMedia?->first()?->url,
            'number' => '01',
        ]);
    }

    foreach ($categories->take(3) as $category) {
        $quickItems->push([
            'image' => $category->coverMedia?->url,
            'number' => sprintf('%02d', $quickItems->count() + 1),
        ]);
    }
@endphp

<section
    class="home-discovery-section"
    aria-label="دسترسی تصویری به محصولات"
>
    <div class="container">

        @if($quickItems->isNotEmpty())

            <div class="home-discovery__tiles">

                @foreach($quickItems as $item)

                    <a
                        href="{{ $productsUrl }}"
                        class="home-discovery-tile"
                        aria-label="مشاهده همه محصولات"
                    >
                        @if($item['image'])
                            <img
                                src="{{ $item['image'] }}"
                                alt=""
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <span
                                class="home-discovery-tile__placeholder"
                                aria-hidden="true"
                            >
                                JANAN
                            </span>
                        @endif

                        <span
                            class="home-discovery-tile__shade"
                            aria-hidden="true"
                        ></span>

                        <span
                            class="home-discovery-tile__index"
                            aria-hidden="true"
                        >
                            {{ $item['number'] }}
                        </span>

                        <span
                            class="home-discovery-tile__arrow"
                            aria-hidden="true"
                        >
                            ↗
                        </span>
                    </a>

                @endforeach

            </div>

        @endif

    </div>
</section>
