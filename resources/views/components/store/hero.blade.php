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

    <div class="immersive-title-overlay" id="immersiveTitleOverlay" aria-labelledby="immersiveSlideTitle" aria-hidden="true" inert>
        <div class="immersive-title-overlay__number">
            <span id="immersiveSlideNumber">۰۱</span>
        </div>
        <div class="immersive-title-overlay__title">
            <h1 id="immersiveSlideTitle">کالکشن منتخب جانان</h1>
        </div>
        <div class="immersive-product-meta" id="immersiveProductMeta"></div>
        <div class="immersive-product-price" id="immersiveProductPrice"></div>
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
        aria-hidden="true" disabled
    >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
    </button>

    <div class="immersive-controls" id="immersiveControls" role="group" aria-label="کنترل اندازه گالری">
        <div class="immersive-percentage" id="immersivePercentage">۶۰٪</div>

        <div class="immersive-switch" id="immersiveSwitch">
            <button type="button" class="immersive-switch__button" aria-pressed="false" data-zoom="0.3">
                <span></span>
                کوچک‌نمایی
            </button>
            <button type="button" class="immersive-switch__button is-current" aria-pressed="true" data-zoom="0.6">
                <span></span>
                اندازه عادی
            </button>
            <button type="button" class="immersive-switch__button" aria-pressed="false" data-zoom="1">
                <span></span>
                بزرگ‌نمایی
            </button>
            <button type="button" class="immersive-switch__button" aria-pressed="false" data-fit="1">
                <span></span>
                نمایش کامل
            </button>
        </div>
    </div>

    <div class="immersive-discover">
        <div><span>کالکشن جانان</span><p>محصولی را انتخاب کنید تا جزئیات آن را ببینید.</p></div>
        <a href="{{ route('products.index') }}">همه محصولات</a>
    </div>
    @if(empty($slides))
        <div class="immersive-empty-state"><h1>به جانان خوش آمدید</h1><p>کالکشن فروشگاه را مرور کنید.</p><a href="{{ route('products.index') }}" class="button button--primary">مشاهده فروشگاه</a></div>
    @endif
    <footer class="immersive-footer">
        <div class="immersive-footer__info">
            <p>جانان · انتخاب‌شده برای تو</p>
        </div>
        <div class="immersive-footer__counter">
            <span>گالری تعاملی محصولات</span>
        </div>
    </footer>

    <script type="application/json" id="immersiveHeroData">
        @json($slides)
    </script>

    {{-- The entire visual hero is a client-side gallery, so links must be
         generated from the same APP_URL used by the current request. --}}

</section>
