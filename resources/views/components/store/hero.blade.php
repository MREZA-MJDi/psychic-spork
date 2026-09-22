<section class="janan-editorial-hero" id="jaananSlider">
    <div class="janan-hero-copy">
        <span class="janan-hero-eyebrow">NEW COLLECTION — 2026</span>
        <h1>Feel<br><em>Beautiful.</em></h1>
        <p>یک انتخاب نرم و دقیق، ساخته‌شده از محصولات واقعی فروشگاه جانان و برندهای فعال آن.</p>
        <a href="{{ route('products.index') }}" class="janan-hero-btn">
            Explore Collection <span>↗</span>
        </a>
    </div>

    <div class="janan-hero-visual">
        <div class="janan-visual-glow"></div>

        @php
            $slides = array_values($heroSlides ?? []);
            $slideCount = max(1, count($slides));
            $slotIndexes = [
                2 % $slideCount,
                1 % $slideCount,
                0,
                3 % $slideCount,
                0,
            ];
        @endphp

        @foreach($slotIndexes as $slot => $slideIndex)
            @php($slide = $slides[$slideIndex] ?? null)
            <article
                class="janan-product-card {{ $slot === 4 ? 'janan-main-card' : 'janan-card-' . ['one','two','three','four'][$slot] }}"
                data-slot="{{ $slot }}"
                data-slide-index="{{ $slideIndex }}"
            >
                @if(!empty($slide['image'] ?? null))
                    <img
                        class="{{ $slot === 4 ? 'janan-main-image' : '' }}"
                        src="{{ $slide['image'] }}"
                        alt="{{ $slide['title'] }}"
                        draggable="false"
                    >
                @else
                    <div class="visual-placeholder"><span>JANAN</span></div>
                @endif

                <div class="janan-main-overlay"></div>
                <div class="janan-main-info">
                    <span class="janan-main-label">JANAN COLLECTION</span>
                    <h2 class="janan-main-title">{{ $slide['title'] ?? 'کالکشن جانان' }}</h2>
                    <span class="janan-main-brand">{{ $slide['brand'] ?? 'JANAN' }}</span>
                </div>
            </article>
        @endforeach

        <div class="janan-slider-number">
            <span class="janan-current">01</span>
            <i></i>
            <span class="janan-total">{{ str_pad((string) $slideCount, 2, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="janan-slider-controls">
            <button type="button" class="janan-slider-arrow janan-prev" aria-label="Previous slide"><span>←</span></button>
            <button type="button" class="janan-slider-arrow janan-next" aria-label="Next slide"><span>→</span></button>
        </div>
    </div>
</section>

<style>

    /* =========================================================
       ROOT
    ========================================================== */

    #jaananSlider {
        position: relative;
        width: 100%;
        min-height: 760px;
        overflow: hidden;

        display: flex;
        align-items: center;

        padding: 70px 7vw;

        background:
            radial-gradient(
                circle at 72% 42%,
                rgba(255, 255, 255, .95) 0%,
                rgba(255, 255, 255, 0) 42%
            ),
            linear-gradient(
                135deg,
                #f8fbff 0%,
                #f3f8fc 46%,
                #fff7fa 100%
            );

        color: #334155;

        isolation: isolate;
    }


    /* =========================================================
       COPY
    ========================================================== */

    #jaananSlider .janan-hero-copy {
        position: relative;
        z-index: 20;

        width: 38%;
        max-width: 480px;

        padding-right: 10px;
    }


    #jaananSlider .janan-hero-eyebrow {
        display: block;

        margin-bottom: 24px;

        font-size: 10px;
        line-height: 1;

        font-weight: 700;

        letter-spacing: .28em;

        color: #94a3b8;
    }


    #jaananSlider .janan-hero-copy h1 {
        margin: 0;

        font-family:
            Inter,
            Arial,
            sans-serif;

        font-size: clamp(65px, 7vw, 112px);

        line-height: .84;

        font-weight: 400;

        letter-spacing: -.065em;

        color: #334155;
    }


    #jaananSlider .janan-hero-copy h1 em {
        font-family:
            Georgia,
            "Times New Roman",
            serif;

        font-weight: 400;

        font-style: italic;

        letter-spacing: -.06em;

        color: #b97896;
    }


    #jaananSlider .janan-hero-copy p {
        max-width: 360px;

        margin: 34px 0 30px;

        color: #64748b;

        font-size: 14px;

        line-height: 1.9;
    }


    #jaananSlider .janan-hero-btn {
        display: inline-flex;

        align-items: center;

        gap: 18px;

        padding: 15px 21px;

        border: 1px solid #d7e1ea;

        border-radius: 100px;

        background: rgba(255, 255, 255, .48);

        color: #334155;

        text-decoration: none;

        font-size: 11px;

        font-weight: 600;

        letter-spacing: .12em;

        text-transform: uppercase;

        backdrop-filter: blur(12px);

        transition:
            background .3s ease,
            color .3s ease,
            border-color .3s ease,
            transform .3s ease;
    }


    #jaananSlider .janan-hero-btn span {
        font-size: 17px;
        line-height: 1;
    }


    #jaananSlider .janan-hero-btn:hover {
        background: #334155;

        border-color: #334155;

        color: #fff;

        transform: translateY(-2px);
    }


    /* =========================================================
       VISUAL
    ========================================================== */

    #jaananSlider .janan-hero-visual {
        position: relative;

        width: 62%;
        height: 620px;

        margin-left: 20px;
    }


    #jaananSlider .janan-visual-glow {
        position: absolute;

        width: 460px;
        height: 460px;

        left: 50%;
        top: 50%;

        transform: translate(-50%, -50%);

        border-radius: 50%;

        background:
            radial-gradient(
                circle,
                rgba(255,255,255,.90) 0%,
                rgba(255,255,255,.35) 52%,
                rgba(255,255,255,0) 72%
            );

        filter: blur(4px);

        pointer-events: none;
    }


    /* =========================================================
       CARDS
    ========================================================== */

    #jaananSlider .janan-product-card {
        position: absolute;

        overflow: hidden;

        border-radius: 20px;

        background: #e2e8f0;

        box-shadow:
            0 25px 55px rgba(51, 65, 85, .10);

        cursor: pointer;

        will-change:
            transform,
            width,
            height,
            left,
            right,
            top,
            bottom,
            opacity;

        transition:
            transform 900ms cubic-bezier(.22,.8,.25,1),
            width 900ms cubic-bezier(.22,.8,.25,1),
            height 900ms cubic-bezier(.22,.8,.25,1),
            left 900ms cubic-bezier(.22,.8,.25,1),
            right 900ms cubic-bezier(.22,.8,.25,1),
            top 900ms cubic-bezier(.22,.8,.25,1),
            bottom 900ms cubic-bezier(.22,.8,.25,1),
            opacity 650ms ease,
            box-shadow 700ms ease,
            border-radius 700ms ease;
    }


    #jaananSlider .janan-product-card img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;

        user-select: none;

        -webkit-user-drag: none;

        transition:
            transform 1000ms cubic-bezier(.22,.8,.25,1),
            opacity 500ms ease;
    }


    /* =========================================================
       POSITIONS
    ========================================================== */

    #jaananSlider .janan-card-one {
        width: 190px;
        height: 265px;

        left: 20px;
        top: 40px;

        transform: rotate(-6deg);

        z-index: 2;
    }


    #jaananSlider .janan-card-two {
        width: 205px;
        height: 285px;

        left: 180px;
        top: 170px;

        transform: rotate(3deg);

        z-index: 4;
    }


    #jaananSlider .janan-card-three {
        width: 185px;
        height: 255px;

        right: 15px;
        top: 48px;

        transform: rotate(6deg);

        z-index: 2;
    }


    #jaananSlider .janan-card-four {
        width: 175px;
        height: 245px;

        right: 70px;
        bottom: 28px;

        transform: rotate(-5deg);

        z-index: 3;
    }


    /* =========================================================
       MAIN CARD
    ========================================================== */

    #jaananSlider .janan-main-card {
        width: 270px;
        height: 370px;

        left: 50%;
        top: 50%;

        transform:
            translate(-50%, -50%)
            rotate(-1deg)
            scale(1);

        z-index: 10;

        border-radius: 24px;

        box-shadow:
            0 35px 75px rgba(51, 65, 85, .18);
    }


    #jaananSlider .janan-main-image {
        transition:
            transform 1000ms cubic-bezier(.22,.8,.25,1),
            opacity 500ms ease;
    }


    #jaananSlider .janan-main-card .janan-main-overlay {
        position: absolute;

        inset: 0;

        background:
            linear-gradient(
                to top,
                rgba(15,23,42,.72),
                rgba(15,23,42,0) 58%
            );

        pointer-events: none;
    }


    #jaananSlider .janan-main-card .janan-main-info {
        position: absolute;

        left: 25px;
        right: 25px;
        bottom: 25px;

        color: white;
    }


    #jaananSlider .janan-main-label {
        display: block;

        margin-bottom: 8px;

        font-size: 9px;

        letter-spacing: .22em;

        text-transform: uppercase;

        opacity: .72;
    }


    #jaananSlider .janan-main-title {
        margin: 0 0 7px;

        font-family:
            Georgia,
            "Times New Roman",
            serif;

        font-size: 30px;

        line-height: .95;

        font-weight: 400;
    }


    #jaananSlider .janan-main-brand {
        font-size: 10px;

        letter-spacing: .15em;

        text-transform: uppercase;

        opacity: .78;
    }


    /* =========================================================
       NUMBER
    ========================================================== */

    #jaananSlider .janan-slider-number {
        position: absolute;

        left: calc(50% + 160px);
        top: calc(50% + 190px);

        z-index: 20;

        display: flex;

        align-items: center;

        gap: 10px;

        font-size: 10px;

        letter-spacing: .1em;

        color: #94a3b8;
    }


    #jaananSlider .janan-slider-number i {
        width: 28px;
        height: 1px;

        background: #cbd5e1;
    }


    /* =========================================================
       CONTROLS
    ========================================================== */

    #jaananSlider .janan-slider-controls {
        position: absolute;

        left: 50%;
        bottom: 12px;

        transform: translateX(-50%);

        z-index: 30;

        display: flex;

        gap: 8px;
    }


    #jaananSlider .janan-slider-arrow {
        width: 48px;
        height: 48px;

        border: 1px solid rgba(100, 116, 139, .18);

        border-radius: 50%;

        background: rgba(255,255,255,.62);

        color: #475569;

        cursor: pointer;

        display: grid;

        place-items: center;

        font-size: 17px;

        backdrop-filter: blur(14px);

        transition:
            background .25s ease,
            color .25s ease,
            transform .25s ease,
            border-color .25s ease;
    }


    #jaananSlider .janan-slider-arrow:hover {
        background: #334155;

        border-color: #334155;

        color: white;

        transform: translateY(-2px);
    }


    /* =========================================================
       MOVING STATE
    ========================================================== */

    #jaananSlider .janan-product-card.is-moving {
        pointer-events: none;
    }


    #jaananSlider .janan-product-card.becoming-main {
        z-index: 25;

        border-radius: 24px;

        box-shadow:
            0 35px 75px rgba(51, 65, 85, .18);
    }


    /* =========================================================
       MOBILE
    ========================================================== */

    @media (max-width: 1000px) {

        #jaananSlider {
            min-height: 850px;

            padding:
                55px 25px 45px;

            display: block;
        }


        #jaananSlider .janan-hero-copy {
            width: 100%;

            max-width: 600px;

            margin-bottom: 20px;
        }


        #jaananSlider .janan-hero-copy h1 {
            font-size: 72px;
        }


        #jaananSlider .janan-hero-copy p {
            margin: 22px 0;
        }


        #jaananSlider .janan-hero-visual {
            width: 100%;

            height: 510px;

            margin: 0;
        }


        #jaananSlider .janan-card-one {
            width: 145px;
            height: 205px;

            left: 0;
            top: 35px;
        }


        #jaananSlider .janan-card-two {
            width: 155px;
            height: 220px;

            left: 70px;
            top: 150px;
        }


        #jaananSlider .janan-card-three {
            width: 140px;
            height: 195px;

            right: 0;
            top: 30px;
        }


        #jaananSlider .janan-card-four {
            width: 140px;
            height: 190px;

            right: 25px;
            bottom: 15px;
        }


        #jaananSlider .janan-main-card {
            width: 215px;
            height: 300px;
        }


        #jaananSlider .janan-slider-number {
            left: auto;
            right: 30px;

            top: auto;
            bottom: 78px;
        }


        #jaananSlider .janan-slider-controls {
            bottom: 0;
        }

    }


    @media (max-width: 560px) {

        #jaananSlider {
            min-height: 760px;

            padding:
                42px 18px 35px;
        }


        #jaananSlider .janan-hero-copy h1 {
            font-size: 58px;
        }


        #jaananSlider .janan-hero-copy p {
            max-width: 290px;

            font-size: 12px;
        }


        #jaananSlider .janan-hero-visual {
            height: 430px;
        }


        #jaananSlider .janan-card-one {
            width: 110px;
            height: 160px;
        }


        #jaananSlider .janan-card-two {
            width: 125px;
            height: 175px;

            left: 45px;
            top: 130px;
        }


        #jaananSlider .janan-card-three {
            width: 105px;
            height: 150px;
        }


        #jaananSlider .janan-card-four {
            width: 105px;
            height: 145px;

            right: 15px;
            bottom: 10px;
        }


        #jaananSlider .janan-main-card {
            width: 185px;
            height: 255px;
        }


        #jaananSlider .janan-main-title {
            font-size: 24px;
        }


        #jaananSlider .janan-main-info {
            left: 18px;
            right: 18px;
            bottom: 18px;
        }


        #jaananSlider .janan-slider-number {
            right: 18px;
            bottom: 68px;
        }

    }

</style>



<script>
(function () {
    const root = document.querySelector("#jaananSlider");
    if (!root) return;

    const cards = Array.from(root.querySelectorAll(".janan-product-card"));
    const current = root.querySelector(".janan-current");
    const next = root.querySelector(".janan-next");
    const prev = root.querySelector(".janan-prev");

    const slides = @json(array_values($slides ?? []));
    if (!slides.length) return;

    let slotSlides = [
        2 % slides.length,
        1 % slides.length,
        0,
        3 % slides.length,
        0
    ];

    let busy = false;
    let timer = null;

    function positions() {
        return [
            {left:"20px",top:"40px",right:"auto",bottom:"auto",width:"190px",height:"265px",transform:"rotate(-6deg)",z:2},
            {left:"180px",top:"170px",right:"auto",bottom:"auto",width:"205px",height:"285px",transform:"rotate(3deg)",z:4},
            {left:"auto",top:"48px",right:"15px",bottom:"auto",width:"185px",height:"255px",transform:"rotate(6deg)",z:2},
            {left:"auto",top:"auto",right:"70px",bottom:"28px",width:"175px",height:"245px",transform:"rotate(-5deg)",z:3},
            {left:"50%",top:"50%",right:"auto",bottom:"auto",width:"270px",height:"370px",transform:"translate(-50%, -50%) rotate(-1deg) scale(1)",z:10}
        ];
    }

    function applyPosition(card, p) {
        card.style.left = p.left;
        card.style.right = p.right;
        card.style.top = p.top;
        card.style.bottom = p.bottom;
        card.style.width = p.width;
        card.style.height = p.height;
        card.style.transform = p.transform;
        card.style.zIndex = p.z;
    }

    function renderCard(card, slideIndex) {
        const slide = slides[slideIndex] || {};
        let image = card.querySelector("img");
        if (!image && slide.image) {
            image = document.createElement("img");
            card.prepend(image);
        }
        if (image) {
            image.src = slide.image || "";
            image.alt = slide.title || "Janan";
        }
        card.dataset.slideIndex = String(slideIndex);

        const title = card.querySelector(".janan-main-title");
        const brand = card.querySelector(".janan-main-brand");
        if (title) title.textContent = slide.title || "کالکشن جانان";
        if (brand) brand.textContent = slide.brand || "JANAN";
    }

    function renderAll() {
        cards.forEach((card, index) => renderCard(card, slotSlides[index]));
    }

    function setCurrentFromMain() {
        const mainIndex = slotSlides[4];
        current.textContent = String(mainIndex + 1).padStart(2, "0");
    }

    function go(direction = 1) {
        if (busy || cards.length < 5) return;
        busy = true;

        const p = positions();
        cards.forEach(card => card.classList.add("is-moving"));

        cards.forEach(card => card.style.transition = "none");
        cards.forEach((card, index) => applyPosition(card, p[index]));
        void root.offsetWidth;
        cards.forEach(card => card.style.transition = "");

        const nextSlotSlides = direction === 1
            ? [slotSlides[4], slotSlides[0], slotSlides[1], slotSlides[2], slotSlides[3]]
            : [slotSlides[1], slotSlides[2], slotSlides[3], slotSlides[4], slotSlides[0]];

        if (direction === 1) {
            requestAnimationFrame(() => {
                applyPosition(cards[0], p[1]);
                applyPosition(cards[1], p[2]);
                applyPosition(cards[2], p[3]);
                applyPosition(cards[3], p[4]);
                applyPosition(cards[4], p[0]);
            });
        } else {
            requestAnimationFrame(() => {
                applyPosition(cards[1], p[0]);
                applyPosition(cards[2], p[1]);
                applyPosition(cards[3], p[2]);
                applyPosition(cards[4], p[3]);
                applyPosition(cards[0], p[4]);
            });
        }

        slotSlides = nextSlotSlides;
        cards.forEach(card => card.classList.remove("janan-main-card"));
        cards[direction === 1 ? 3 : 0].classList.add("janan-main-card");
        renderAll();
        setCurrentFromMain();

        window.setTimeout(() => {
            cards.forEach(card => card.classList.remove("is-moving"));
            busy = false;
        }, 950);
    }

    function restartTimer() {
        if (timer) window.clearInterval(timer);
        timer = window.setInterval(() => go(1), 4000);
    }

    renderAll();
    cards.forEach((card, index) => applyPosition(card, positions()[index]));
    setCurrentFromMain();

    next?.addEventListener("click", () => { go(1); restartTimer(); });
    prev?.addEventListener("click", () => { go(-1); restartTimer(); });

    root.addEventListener("keydown", (event) => {
        if (event.key === "ArrowRight") { event.preventDefault(); go(1); restartTimer(); }
        if (event.key === "ArrowLeft") { event.preventDefault(); go(-1); restartTimer(); }
    });
    root.setAttribute("tabindex", "0");

    let touchStartX = 0;
    root.addEventListener("touchstart", event => { touchStartX = event.touches[0].clientX; }, {passive:true});
    root.addEventListener("touchend", event => {
        const distance = event.changedTouches[0].clientX - touchStartX;
        if (Math.abs(distance) < 45) return;
        go(distance < 0 ? 1 : -1);
        restartTimer();
    }, {passive:true});

    restartTimer();
})();
</script>
