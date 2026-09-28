@php
    $productsUrl = route('products.index');
    $visuals = collect();

    if ($latestProduct?->galleryMedia?->first()?->url) {
        $visuals->push($latestProduct->galleryMedia->first()->url);
    }

    foreach ($categories->take(3) as $category) {
        if ($category->coverMedia?->url) {
            $visuals->push($category->coverMedia->url);
        }
    }
@endphp

<section
    class="home-closing-section"
    aria-label="ورود به فروشگاه جانان"
>
    <div class="container">

        <a
            href="{{ $productsUrl }}"
            class="home-closing__panel"
            aria-label="مشاهده همه محصولات"
        >
            <span class="home-closing__bg" aria-hidden="true"></span>

            @foreach($visuals->take(4) as $image)
                <span class="home-closing__image">
                    <img
                        src="{{ $image }}"
                        alt=""
                        loading="lazy"
                        decoding="async"
                    >
                </span>
            @endforeach

            <span class="home-closing__shade" aria-hidden="true"></span>

            <span class="home-closing__center" aria-hidden="true">
                <small>JANAN</small>
                <strong>EXPLORE</strong>
                <i>↗</i>
            </span>
        </a>

    </div>
</section>
