<section class="janan-editorial-hero" id="jaananSlider">

    <div class="janan-hero-copy">

        <span class="janan-hero-eyebrow">
            NEW COLLECTION — 2026
        </span>

        <h1>
            Feel<br>
            <em>Beautiful.</em>
        </h1>

        <p>
            A delicate collection designed around confidence,
            comfort and the beauty of everyday femininity.
        </p>

        <a href="{{ route('products.index') }}" class="janan-hero-btn">
            Explore Collection
            <span>↗</span>
        </a>

    </div>


    <div class="janan-hero-visual">

        <div class="janan-visual-glow"></div>


        {{-- =====================================================
             CARD 01
        ====================================================== --}}
        <article
            class="janan-product-card janan-card-one"
            data-index="0"
        >
            <img
                src="{{ $heroSlides[2]['image'] ?? '' }}"
                alt="{{ $heroSlides[2]['title'] ?? 'JANAN Collection' }}"
                draggable="false"
            >
        </article>


        {{-- =====================================================
             CARD 02
        ====================================================== --}}
        <article
            class="janan-product-card janan-card-two"
            data-index="1"
        >
            <img
                src="{{ $heroSlides[1]['image'] ?? '' }}"
                alt="{{ $heroSlides[1]['title'] ?? 'JANAN Collection' }}"
                draggable="false"
            >
        </article>


        {{-- =====================================================
             CARD 03
        ====================================================== --}}
        <article
            class="janan-product-card janan-card-three"
            data-index="2"
        >
            <img
                src="{{ $heroSlides[0]['image'] ?? '' }}"
                alt="{{ $heroSlides[0]['title'] ?? 'JANAN Collection' }}"
                draggable="false"
            >
        </article>


        {{-- =====================================================
             CARD 04
        ====================================================== --}}
        <article
            class="janan-product-card janan-card-four"
            data-index="3"
        >
            <img
                src="{{ $heroSlides[3]['image'] ?? '' }}"
                alt="{{ $heroSlides[3]['title'] ?? 'JANAN Collection' }}"
                draggable="false"
            >
        </article>


        {{-- =====================================================
             ACTIVE CARD
        ====================================================== --}}
        <article
            class="janan-product-card janan-main-card"
            data-index="4"
        >

            <img
                class="janan-main-image"
                src="{{ $heroSlides[0]['image'] ?? '' }}"
                alt="{{ $heroSlides[0]['title'] ?? 'JANAN Collection' }}"
                draggable="false"
            >

            <div class="janan-main-overlay"></div>

            <div class="janan-main-info">

                <span class="janan-main-label">
                    JANAN COLLECTION
                </span>

                <h2 class="janan-main-title">
                    {{ $heroSlides[0]['title'] ?? 'کالکشن جانان' }}
                </h2>

                <span class="janan-main-brand">
                    {{ $heroSlides[0]['brand'] ?? 'JANAN' }}
                </span>

            </div>

        </article>


        {{-- =====================================================
             NUMBER
        ====================================================== --}}
        <div class="janan-slider-number">

            <span class="janan-current">
                01
            </span>

            <i></i>

            <span class="janan-total">
                04
            </span>

        </div>


        {{-- =====================================================
             CONTROLS
        ====================================================== --}}
        <div class="janan-slider-controls">

            <button
                type="button"
                class="janan-slider-arrow janan-prev"
                aria-label="Previous slide"
            >
                <span>←</span>
            </button>

            <button
                type="button"
                class="janan-slider-arrow janan-next"
                aria-label="Next slide"
            >
                <span>→</span>
            </button>

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


    #jaananSlider .janan-main-overlay {
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


    #jaananSlider .janan-main-info {
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

        if (!root) {
            return;
        }


        const cards = Array.from(
            root.querySelectorAll(".janan-product-card")
        );


        const mainImage =
            root.querySelector(".janan-main-image");


        const current =
            root.querySelector(".janan-current");


        const prev =
            root.querySelector(".janan-prev");


        const next =
            root.querySelector(".janan-next");


        const mainTitle =
            root.querySelector(".janan-main-title");


        const mainBrand =
            root.querySelector(".janan-main-brand");


        const slides = @json($heroSlides);


        let activeIndex = 0;

        let busy = false;


        /* =========================================================
           POSITIONS
        ========================================================== */

        function getPositions() {

            return [

                {
                    left: "20px",
                    top: "40px",
                    right: "auto",
                    bottom: "auto",
                    width: "190px",
                    height: "265px",
                    transform: "rotate(-6deg)",
                    z: 2
                },

                {
                    left: "180px",
                    top: "170px",
                    right: "auto",
                    bottom: "auto",
                    width: "205px",
                    height: "285px",
                    transform: "rotate(3deg)",
                    z: 4
                },

                {
                    left: "auto",
                    top: "48px",
                    right: "15px",
                    bottom: "auto",
                    width: "185px",
                    height: "255px",
                    transform: "rotate(6deg)",
                    z: 2
                },

                {
                    left: "auto",
                    top: "auto",
                    right: "70px",
                    bottom: "28px",
                    width: "175px",
                    height: "245px",
                    transform: "rotate(-5deg)",
                    z: 3
                },

                {
                    left: "50%",
                    top: "50%",
                    right: "auto",
                    bottom: "auto",
                    width: "270px",
                    height: "370px",
                    transform:
                        "translate(-50%, -50%) rotate(-1deg) scale(1)",
                    z: 10
                }

            ];

        }


        /* =========================================================
           APPLY POSITION
        ========================================================== */

        function applyPosition(card, position) {

            card.style.left =
                position.left;

            card.style.right =
                position.right;

            card.style.top =
                position.top;

            card.style.bottom =
                position.bottom;

            card.style.width =
                position.width;

            card.style.height =
                position.height;

            card.style.transform =
                position.transform;

            card.style.zIndex =
                position.z;

        }


        /* =========================================================
           INITIAL
        ========================================================== */

        function setupCards() {

            const positions =
                getPositions();

            cards.forEach((card, index) => {

                applyPosition(
                    card,
                    positions[index]
                );

            });

        }


        /* =========================================================
           CONTENT
        ========================================================== */

        function updateContent(index) {

            const data =
                slides[index];

            mainImage.style.opacity = "0";


            setTimeout(() => {

                mainImage.src =
                    data.image;

                mainTitle.innerHTML =
                    data.title;

                mainBrand.textContent =
                    data.brand || "JANAN";


                mainImage.onload = () => {

                    mainImage.style.opacity = "1";

                };


                if (mainImage.complete) {

                    mainImage.style.opacity = "1";

                }

            }, 240);


            current.textContent =
                String(index + 1).padStart(2, "0");

        }


        /* =========================================================
           SLIDE
        ========================================================== */

        function go(direction) {

            if (busy) {
                return;
            }

            busy = true;


            const positions =
                getPositions();


            cards.forEach(card => {

                card.classList.add("is-moving");

            });


            if (direction === 1) {

                const incoming =
                    cards[3];


                incoming.classList.add(
                    "becoming-main"
                );

                incoming.style.zIndex = "30";


                cards.forEach(card => {

                    card.style.transition =
                        "none";

                });


                applyPosition(
                    cards[0],
                    positions[0]
                );

                applyPosition(
                    cards[1],
                    positions[1]
                );

                applyPosition(
                    cards[2],
                    positions[2]
                );

                applyPosition(
                    cards[3],
                    positions[3]
                );

                applyPosition(
                    cards[4],
                    positions[4]
                );


                void root.offsetWidth;


                cards.forEach(card => {

                    card.style.transition = "";

                });


                requestAnimationFrame(() => {

                    applyPosition(
                        cards[0],
                        positions[1]
                    );

                    applyPosition(
                        cards[1],
                        positions[2]
                    );

                    applyPosition(
                        cards[2],
                        positions[3]
                    );

                    applyPosition(
                        cards[3],
                        positions[4]
                    );

                    applyPosition(
                        cards[4],
                        positions[0]
                    );

                });


                activeIndex =
                    (activeIndex + 1) % slides.length;

            }


            else {

                const incoming =
                    cards[0];


                incoming.classList.add(
                    "becoming-main"
                );

                incoming.style.zIndex = "30";


                cards.forEach(card => {

                    card.style.transition =
                        "none";

                });


                applyPosition(
                    cards[0],
                    positions[0]
                );

                applyPosition(
                    cards[1],
                    positions[1]
                );

                applyPosition(
                    cards[2],
                    positions[2]
                );

                applyPosition(
                    cards[3],
                    positions[3]
                );

                applyPosition(
                    cards[4],
                    positions[4]
                );


                void root.offsetWidth;


                cards.forEach(card => {

                    card.style.transition = "";

                });


                requestAnimationFrame(() => {

                    applyPosition(
                        cards[1],
                        positions[0]
                    );

                    applyPosition(
                        cards[2],
                        positions[1]
                    );

                    applyPosition(
                        cards[3],
                        positions[2]
                    );

                    applyPosition(
                        cards[4],
                        positions[3]
                    );

                    applyPosition(
                        cards[0],
                        positions[4]
                    );

                });


                activeIndex =
                    (activeIndex - 1 + slides.length) %
                    slides.length;

            }


            setTimeout(() => {

                updateContent(activeIndex);

            }, 350);


            setTimeout(() => {

                cards.forEach(card => {

                    card.classList.remove(
                        "is-moving",
                        "becoming-main"
                    );

                });

                busy = false;

            }, 950);

        }


        /* =========================================================
           BUTTONS
        ========================================================== */

        next.addEventListener(
            "click",
            function () {
                go(1);
            }
        );


        prev.addEventListener(
            "click",
            function () {
                go(-1);
            }
        );


        /* =========================================================
           KEYBOARD
        ========================================================== */

        root.setAttribute(
            "tabindex",
            "0"
        );


        root.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "ArrowRight") {

                    event.preventDefault();

                    go(1);
                }


                if (event.key === "ArrowLeft") {

                    event.preventDefault();

                    go(-1);
                }

            }
        );


        /* =========================================================
           CARD CLICK
        ========================================================== */

        cards.forEach((card, index) => {

            card.addEventListener(
                "click",
                function () {

                    if (busy) {
                        return;
                    }


                    if (index === 4) {
                        return;
                    }


                    if (index === 0) {

                        go(-1);

                    } else {

                        go(1);

                    }

                }
            );

        });


        /* =========================================================
           SWIPE
        ========================================================== */

        let touchStartX = 0;


        root.addEventListener(
            "touchstart",
            function (event) {

                touchStartX =
                    event.touches[0].clientX;

            },
            {
                passive: true
            }
        );


        root.addEventListener(
            "touchend",
            function (event) {

                const endX =
                    event.changedTouches[0].clientX;

                const distance =
                    endX - touchStartX;


                if (Math.abs(distance) < 45) {
                    return;
                }


                if (distance < 0) {

                    go(1);

                } else {

                    go(-1);

                }

            },
            {
                passive: true
            }
        );


        /* =========================================================
           START
        ========================================================== */

        setupCards();

    })();

</script>
