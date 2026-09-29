@php
    $slides = array_values($heroSlides ?? []);
    $slideCount = count($slides);
@endphp

@if(!app()->environment('testing'))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/Draggable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/Flip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/CustomEase.min.js"></script>
@endif

<section
    class="immersive-grid-hero"
    id="immersiveGridHero"
    data-immersive-gallery
    aria-label="گالری تعاملی کالکشن جانان"
>
    <div class="immersive-preloader" id="immersivePreloader" aria-hidden="true"></div>

    <header class="immersive-header">
        <div class="immersive-header__nav">
            <a href="{{ route('home') }}" class="immersive-logo" aria-label="Janan">
                <span class="immersive-logo__circles" aria-hidden="true">
                    <span class="immersive-logo__circle immersive-logo__circle--one"></span>
                    <span class="immersive-logo__circle immersive-logo__circle--two"></span>
                </span>
            </a>
        </div>

        <div class="immersive-header__section immersive-header__section--menu">
            <h3>+Menu</h3>
            <ul>
                <li><a href="{{ route('products.index') }}">Products</a></li>
                <li><a href="{{ route('categories.index') }}">Collections</a></li>
                <li><a href="{{ route('about') }}">About</a></li>
                <li><a href="{{ route('contact') }}">Contact</a></li>
            </ul>
        </div>

        <div class="immersive-header__section">
            <h3>+Studio</h3>
            <p>JANAN</p>
            <p>Online Store</p>
        </div>

        <div class="immersive-header__section">
            <h3>+Connect</h3>
            <p>
                @if(!empty($siteStoreEmail))
                    <a href="mailto:{{ $siteStoreEmail }}">{{ $siteStoreEmail }}</a>
                @else
                    <a href="{{ route('contact') }}">Contact Janan</a>
                @endif
            </p>
        </div>

        <div class="immersive-header__section immersive-header__section--social">
            <h3>+Explore</h3>
            <ul>
                <li><a href="{{ route('cart') }}">Cart</a></li>
                <li><a href="{{ auth()->check() ? route('account') : route('login') }}">Account</a></li>
                <li><a href="{{ route('brands.index') }}">Brands</a></li>
            </ul>
        </div>
    </header>

    <div class="immersive-vignette" aria-hidden="true"></div>

    <div class="immersive-viewport" id="immersiveViewport">
        <div class="immersive-canvas-wrapper" id="immersiveCanvasWrapper">
            <div class="immersive-grid-container" id="immersiveGridContainer"></div>
        </div>
    </div>

    <div class="immersive-split-screen" id="immersiveSplitScreen">
        <div class="immersive-split-screen__left" id="immersiveSplitLeft">
            <div class="immersive-zoom-target" id="immersiveZoomTarget"></div>
        </div>

        <div class="immersive-split-screen__right" id="immersiveSplitRight"></div>
    </div>

    <div class="immersive-title-overlay" id="immersiveTitleOverlay">
        <div class="immersive-title-overlay__number">
            <span id="immersiveSlideNumber">01</span>
        </div>
        <div class="immersive-title-overlay__title">
            <h1 id="immersiveSlideTitle">کالکشن منتخب جانان</h1>
        </div>
        <div class="immersive-title-overlay__description" id="immersiveSlideDescription"></div>
        <a
            class="immersive-title-overlay__link"
            id="immersiveSlideLink"
            href="{{ route('products.index') }}"
        >
            <span>View product</span>
            <span aria-hidden="true">↗</span>
        </a>
    </div>

    <button
        type="button"
        class="immersive-close-button"
        id="immersiveCloseButton"
        aria-label="بستن نمای محصول"
    >
        <svg width="64" height="64" viewBox="0 0 16 16" fill="none" aria-hidden="true">
            <path d="M7.89873 16L6.35949 14.48L11.8278 9.08H0V6.92H11.8278L6.35949 1.52L7.89873 0L16 8L7.89873 16Z" fill="white"/>
        </svg>
    </button>

    <div class="immersive-controls" id="immersiveControls">
        <div class="immersive-percentage" id="immersivePercentage">60%</div>

        <div class="immersive-switch" id="immersiveSwitch">
            <button type="button" class="immersive-switch__button immersive-switch__button--current" data-zoom="0.3">
                <span></span>
                ZOOM OUT
            </button>
            <button type="button" class="immersive-switch__button immersive-switch__button--current-false" data-zoom="0.6">
                <span></span>
                NORMAL
            </button>
            <button type="button" class="immersive-switch__button" data-zoom="1">
                <span></span>
                ZOOM IN
            </button>
            <button type="button" class="immersive-switch__button" data-fit="1">
                <span></span>
                FIT
            </button>
        </div>

        <button type="button" class="immersive-sound-toggle" id="immersiveSoundToggle" aria-label="Toggle sound" aria-pressed="false">
            <canvas id="immersiveSoundCanvas" width="32" height="16"></canvas>
        </button>
    </div>

    <footer class="immersive-footer">
        <div class="immersive-footer__info">
            <p>Est. 2026 · Janan Collection</p>
            <p>{{ config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'JANAN' }}</p>
        </div>
        <div class="immersive-footer__counter">
            <span>Interactive Product Grid</span>
        </div>
    </footer>

    <script type="application/json" id="immersiveHeroData">
        @json($slides)
    </script>
</section>
