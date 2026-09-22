@php
    $slides = array_values($heroSlides ?? []);
    $slideCount = count($slides);
    $first = $slides[0] ?? [
        'image' => null,
        'title' => 'کالکشن منتخب جانان',
        'brand' => 'JANAN',
        'eyebrow' => 'NEW COLLECTION',
        'subtitle' => 'انتخابی آرام، دقیق و شخصی برای هر روز.',
        'button_text' => 'مشاهده کالکشن',
        'button_url' => route('products.index'),
    ];
    $prev = $slideCount > 1 ? $slides[$slideCount - 1] : $first;
    $next = $slideCount > 1 ? $slides[1] : $first;
@endphp

<section
    class="home-hero"
    data-editorial-hero
    data-autoplay="5600"
    aria-label="کالکشن منتخب جانان"
>
    <div class="container home-hero__grid">

        <div class="home-hero__copy">

            <span class="home-hero__kicker" data-hero-eyebrow>
                {{ $first['eyebrow'] ?? 'NEW COLLECTION' }}
            </span>

            <h1 class="home-hero__title">
                انتخابی<br>
                <em data-hero-heading>{{ $first['title'] ?? 'کالکشن منتخب' }}</em>
            </h1>

            <p class="home-hero__description" data-hero-description>
                {{ $first['subtitle'] ?? 'انتخابی آرام، دقیق و شخصی برای هر روز.' }}
            </p>

            <div class="home-hero__actions">
                <a
                    class="button button--primary"
                    data-hero-button
                    href="{{ $first['button_url'] ?? route('products.index') }}"
                >
                    <span data-hero-button-text>
                        {{ $first['button_text'] ?? 'مشاهده کالکشن' }}
                    </span>
                    <span aria-hidden="true">↗</span>
                </a>

                <a class="button button--ghost" href="{{ route('products.index') }}">
                    فروشگاه
                </a>
            </div>

            <div class="home-hero__meta">
                <span data-hero-current>01</span>
                <span> / </span>
                <span data-hero-total>{{ str_pad((string) max($slideCount, 1), 2, '0', STR_PAD_LEFT) }}</span>
                <span>·</span>
                <span data-hero-brand>{{ $first['brand'] ?? 'JANAN' }}</span>
            </div>

        </div>


        <div class="home-hero__visual">

            <div class="home-hero__side home-hero__side--prev">
                <button type="button" data-hero-prev-card aria-label="اسلاید قبلی">
                    @if(!empty($prev['image'] ?? null))
                        <img src="{{ $prev['image'] }}" alt="" data-hero-side-prev>
                    @else
                        <div class="visual-placeholder"><span>JANAN</span></div>
                    @endif
                    <span class="home-hero__side-label">PREV</span>
                </button>
            </div>

            <div class="home-hero__main" data-hero-main-card>

                @if(!empty($first['image'] ?? null))
                    <img
                        src="{{ $first['image'] }}"
                        alt="{{ $first['title'] ?? 'کالکشن جانان' }}"
                        data-hero-main-image
                    >
                @else
                    <div class="visual-placeholder">
                        <span>JANAN</span>
                        <strong>THE EDIT</strong>
                    </div>
                @endif

                <div class="home-hero__shade"></div>

                <div class="home-hero__main-info">
                    <small>JANAN COLLECTION</small>
                    <strong data-hero-main-title>{{ $first['title'] ?? 'کالکشن منتخب جانان' }}</strong>
                    <span data-hero-main-brand>{{ $first['brand'] ?? 'JANAN' }}</span>
                </div>

            </div>

            <div class="home-hero__side home-hero__side--next">
                <button type="button" data-hero-next-card aria-label="اسلاید بعدی">
                    @if(!empty($next['image'] ?? null))
                        <img src="{{ $next['image'] }}" alt="" data-hero-side-next>
                    @else
                        <div class="visual-placeholder"><span>JANAN</span></div>
                    @endif
                    <span class="home-hero__side-label">NEXT</span>
                </button>
            </div>


            <div class="home-hero__controls">

                <button
                    class="home-hero__arrow"
                    type="button"
                    data-hero-prev
                    aria-label="اسلاید قبلی"
                >
                    ←
                </button>

                <span class="home-hero__counter">
                    <span data-hero-current>01</span>
                    <span>/</span>
                    <span data-hero-total>{{ str_pad((string) max($slideCount, 1), 2, '0', STR_PAD_LEFT) }}</span>
                </span>

                <button
                    class="home-hero__arrow"
                    type="button"
                    data-hero-next
                    aria-label="اسلاید بعدی"
                >
                    →
                </button>

            </div>

        </div>

    </div>

    <script type="application/json" data-hero-data>
        {!! json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
</section>
