@php
    $slides = array_values($heroSlides ?? []);
@endphp


<section
    class="immersive-grid-hero"
    id="immersiveGridHero"
    data-immersive-gallery
    aria-label="گالری تعاملی کالکشن جانان"
>
    <div class="immersive-vignette" aria-hidden="true"></div>

    <div class="immersive-viewport" id="immersiveViewport">
        <div class="immersive-canvas-wrapper" id="immersiveCanvasWrapper">
            <div class="immersive-grid-container" id="immersiveGridContainer"></div>
        </div>
    </div>

    <div class="immersive-split-screen" id="immersiveSplitScreen">
        <div class="immersive-split-screen__left" id="immersiveSplitLeft">
            <a class="immersive-zoom-target" id="immersiveZoomTarget" href="{{ route('products.index') }}" aria-label="مشاهده محصول انتخاب‌شده" tabindex="-1" aria-hidden="true"></a>
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
            <span>مشاهده محصول</span>
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
                کوچک‌نمایی
            </button>
            <button type="button" class="immersive-switch__button immersive-switch__button--current-false" data-zoom="0.6">
                <span></span>
                اندازه عادی
            </button>
            <button type="button" class="immersive-switch__button" data-zoom="1">
                <span></span>
                بزرگ‌نمایی
            </button>
            <button type="button" class="immersive-switch__button" data-fit="1">
                <span></span>
                نمایش کامل
            </button>
        </div>
    </div>

    <footer class="immersive-footer">
        <div class="immersive-footer__info">
            <p>جانان · انتخاب‌شده برای تو</p>
            <p>{{ config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'JANAN' }}</p>
        </div>
        <div class="immersive-footer__counter">
            <span>گالری تعاملی محصولات</span>
        </div>
    </footer>

    <script type="application/json" id="immersiveHeroData">
        @json($slides)
    </script>
</section>
