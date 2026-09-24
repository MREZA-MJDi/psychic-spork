@php
    $slides = array_values($heroSlides ?? []);
    $slideCount = count($slides);
@endphp

<section
    class="editorial-hero"
    id="editorialHero"
    data-editorial-hero
    data-autoplay="6500"
    aria-label="کالکشن منتخب جانان"
>
    <div class="editorial-hero__ambient" aria-hidden="true">
        <span class="editorial-hero__orb editorial-hero__orb--sky"></span>
        <span class="editorial-hero__orb editorial-hero__orb--pink"></span>
        <span class="editorial-hero__grid"></span>
    </div>

    <div class="editorial-hero__container">

        {{-- Editorial copy --}}
        <div class="editorial-hero__copy">

            <div class="editorial-hero__copy-top">
                <span
                    class="editorial-hero__eyebrow"
                    data-hero-eyebrow
                >
                    NEW COLLECTION
                </span>

                <span class="editorial-hero__season">
                    JANAN / 2026
                </span>
            </div>

            <div class="editorial-hero__heading-wrap">
                <span class="editorial-hero__micro-label">
                    THE NEW JANAN EDIT
                </span>

                <h1 data-hero-heading>
                    کالکشن منتخب جانان
                </h1>
            </div>

            <p
                class="editorial-hero__description"
                data-hero-description
            >
                کالکشنی برای استایل روزمره؛ ظریف، راحت و با جزئیاتی که حس بهتری می‌سازند.
            </p>

            <div class="editorial-hero__actions">

                <a
                    href="{{ route('products.index') }}"
                    class="editorial-hero__button"
                    data-hero-button
                >
                    <span data-hero-button-text>
                        مشاهده کالکشن
                    </span>

                    <span
                        class="editorial-hero__button-icon"
                        aria-hidden="true"
                    >
                        ↗
                    </span>
                </a>

                <div class="editorial-hero__counter">
                    <strong data-hero-current>01</strong>

                    <span></span>

                    <small data-hero-total>
                        {{ str_pad((string) max(1, $slideCount), 2, '0', STR_PAD_LEFT) }}
                    </small>
                </div>

            </div>

            <div class="editorial-hero__caption">
                <span>SELECTED FOR YOU</span>
                <i></i>
                <span>JANAN STORE</span>
            </div>

        </div>


        {{-- Product stage --}}
        <div class="editorial-hero__stage">

            <div
                class="editorial-hero__stage-glow"
                aria-hidden="true"
            ></div>

            <div
                class="editorial-hero__stage-line editorial-hero__stage-line--one"
                aria-hidden="true"
            ></div>

            <div
                class="editorial-hero__stage-line editorial-hero__stage-line--two"
                aria-hidden="true"
            ></div>


            @php
                $initialSlots = [0, 1, 2, 3, 4];
            @endphp

            @foreach($initialSlots as $slot)
                @php
                    $slideIndex = match ($slot) {
                        0 => $slideCount > 0
                            ? (($slideCount - 1) % $slideCount)
                            : 0,

                        1 => $slideCount > 0
                            ? (2 % $slideCount)
                            : 0,

                        2 => 0,

                        3 => $slideCount > 0
                            ? (1 % $slideCount)
                            : 0,

                        default => 0,
                    };

                    $slide = $slides[$slideIndex] ?? null;
                @endphp

                <article
                    class="
                        editorial-hero__card
                        editorial-hero__card--slot-{{ $slot }}
                    {{ $slot === 4 ? 'is-hero' : '' }}
                        "
                    data-hero-card
                    data-hero-slot="{{ $slot }}"
                    data-slide-index="{{ $slideIndex }}"
                    aria-hidden="{{ $slot === 4 ? 'false' : 'true' }}"
                >

                    <div class="editorial-hero__card-media">

                        @if(!empty($slide['image'] ?? null))

                            <picture>
                                <source
                                    data-card-mobile-source
                                >

                                <img
                                    data-card-image
                                    src="{{ $slide['image'] }}"
                                    alt="{{ $slide['title'] ?? 'محصول جانان' }}"
                                    draggable="false"
                                >
                            </picture>

                        @else

                            <div class="editorial-hero__placeholder">
                                <span>JANAN</span>
                            </div>

                        @endif

                    </div>


                    <div
                        class="editorial-hero__card-shade"
                        aria-hidden="true"
                    ></div>


                    <div class="editorial-hero__card-content">

                        <span class="editorial-hero__card-label">
                            JANAN COLLECTION
                        </span>

                        <h2 data-card-title>
                            {{ $slide['title'] ?? 'کالکشن منتخب' }}
                        </h2>

                        <span class="editorial-hero__card-brand">
                            {{ $slide['brand'] ?? 'JANAN' }}
                        </span>

                    </div>

                </article>
            @endforeach


            {{-- Stage details --}}
            <div
                class="editorial-hero__stage-meta"
                aria-hidden="true"
            >
                <span>01</span>
                <i></i>
                <span>05</span>
            </div>


            <div class="editorial-hero__controls">

                <button
                    type="button"
                    class="editorial-hero__control"
                    data-hero-prev
                    aria-label="محصول قبلی"
                >
                    <span>←</span>
                </button>

                <button
                    type="button"
                    class="editorial-hero__control"
                    data-hero-next
                    aria-label="محصول بعدی"
                >
                    <span>→</span>
                </button>

            </div>

        </div>

    </div>


    <script type="application/json" data-hero-data>
        @json($slides)
    </script>
</section>
