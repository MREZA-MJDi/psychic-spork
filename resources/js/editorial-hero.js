import { persianDigits } from './persian-digits.js';
import { gsap } from 'gsap';
import { Draggable } from 'gsap/Draggable';
import { Flip } from 'gsap/Flip';

(() => {
    const boot = () => {
        const root = document.querySelector('[data-immersive-gallery]');
        if (!root || root.dataset.galleryInitialized) return;

        const q = (selector) => root.querySelector(selector);
        const viewport = q('#immersiveViewport');
        const wrap = q('#immersiveCanvasWrapper');
        const grid = q('#immersiveGridContainer');
        const split = q('#immersiveSplitScreen');
        const target = q('#immersiveZoomTarget');
        const left = q('#immersiveSplitLeft');
        const right = q('#immersiveSplitRight');
        const close = q('#immersiveCloseButton');
        const overlay = q('#immersiveTitleOverlay');
        const number = q('#immersiveSlideNumber');
        const title = q('#immersiveSlideTitle');
        const description = q('#immersiveSlideDescription');
        const link = q('#immersiveSlideLink');
        const productMeta = q('#immersiveProductMeta');
        const productPrice = q('#immersiveProductPrice');
        const controls = q('#immersiveControls');
        const percentage = q('#immersivePercentage');
        const data = q('#immersiveHeroData');

        if (
            !viewport ||
            !wrap ||
            !grid ||
            !split ||
            !target ||
            !left ||
            !right ||
            !close ||
            !overlay ||
            !number ||
            !title ||
            !description ||
            !link ||
            !controls ||
            !percentage ||
            !data
        ) {
            return;
        }

        root.dataset.galleryInitialized = 'true';

        let slides = [];
        try {
            const payload = JSON.parse(data.textContent || '[]');
            slides = Array.isArray(payload) ? payload.slice(0, 36) : [];
        } catch {
            slides = [];
        }

        const reducedMotion = window.matchMedia?.(
            '(prefers-reduced-motion: reduce)'
        ).matches ?? false;

        const header = document.querySelector('.store-header--immersive');
        let headerUpdateFrame = 0;
        const updateHeaderSurface = () => {
            if (headerUpdateFrame) return;

            headerUpdateFrame = window.requestAnimationFrame(() => {
                headerUpdateFrame = 0;
                if (!header) return;

                const heroBounds = root.getBoundingClientRect();
                const headerOverHero = heroBounds.top <= 0
                    && heroBounds.bottom >= header.offsetHeight;

                header.classList.toggle('is-over-content', !headerOverHero);
            });
        };

        updateHeaderSurface();
        window.addEventListener('scroll', updateHeaderSurface, { passive: true });
        window.addEventListener('resize', updateHeaderSurface, { passive: true });

        window.addEventListener('pageshow', updateHeaderSurface);

        gsap.registerPlugin(Draggable, Flip);
        // The storefront cart uses the same animation engine on the home page.
        window.gsap = gsap;

        const initGallery = () => {
            if (!slides.length) {
                root.classList.add('is-empty');
                return;
            }

            // Keep vertical scrolling and momentum native. GSAP still owns
            // horizontal canvas dragging; no synthetic scroll is needed.
            root.style.touchAction = 'pan-y';

            class Gallery {
                constructor() {
                    this.cfg = {
                        size: 320,
                        gap: 32,
                        zoom: 0.6,
                        cols: 5,
                        rows: 1,
                    };

                    this.items = [];
                    this.dim = { width: 0, height: 0 };
                    this.drag = null;
                    this.zoom = {
                        active: false,
                        item: null,
                        overlay: null,
                    };
                    this.last = { x: 0, y: 0 };
                    this.layoutFrame = 0;
                    this.introPlaying = true;
                    this.layoutPending = false;
                    this.fitMode = false;
                    this.zoomTween = null;
                    this.returnFocus = null;
                }

                getItemSize() {
                    return parseFloat(
                        getComputedStyle(root).getPropertyValue('--immersive-item-size')
                    ) || 320;
                }

                getColumns() {
                    const width = viewport.clientWidth;
                    if (width <= 540) return Math.min(2, slides.length);
                    if (width <= 820) return Math.min(3, slides.length);
                    if (width <= 1100) return Math.min(4, slides.length);
                    return Math.min(5, slides.length);
                }

                getGap(zoom = this.cfg.zoom) {
                    if (zoom >= 1) return 16;
                    if (zoom >= 0.6) return 26;
                    return 42;
                }

                dimensions() {
                    const width =
                        this.cfg.cols * this.cfg.size +
                        Math.max(0, this.cfg.cols - 1) * this.cfg.gap;

                    const height =
                        this.cfg.rows * this.cfg.size +
                        Math.max(0, this.cfg.rows - 1) * this.cfg.gap;

                    this.dim = { width, height };
                    return this.dim;
                }

                syncConfig() {
                    const oldCols = this.cfg.cols;
                    const oldSize = this.cfg.size;

                    this.cfg.size = this.getItemSize();
                    this.cfg.cols = Math.max(1, this.getColumns());
                    this.cfg.rows = Math.ceil(slides.length / this.cfg.cols);
                    this.cfg.gap = this.getGap();

                    return oldCols !== this.cfg.cols || oldSize !== this.cfg.size;
                }

                build() {
                    this.syncConfig();
                    this.dimensions();
                    wrap.style.width = this.dim.width + 'px';
                    wrap.style.height = this.dim.height + 'px';
                    grid.replaceChildren();
                    this.items = [];

                    slides.forEach((slide, index) => {
                        const row = Math.floor(index / this.cfg.cols);
                        const col = index % this.cfg.cols;
                        const x =
                            col * (this.cfg.size + this.cfg.gap);
                        const y =
                            row * (this.cfg.size + this.cfg.gap);

                        const item = document.createElement('a');
                        item.className = 'immersive-grid-item';
                        item.href = slide.url || '#';
                        item.setAttribute(
                            'aria-label',
                            (slide.title || 'محصول جانان') + ' — مشاهده محصول'
                        );
                        item.style.left = x + 'px';
                        item.style.top = y + 'px';
                        item.style.width = this.cfg.size + 'px';
                        item.style.height = this.cfg.size + 'px';
                        item.style.opacity = '0';
                        item.style.zIndex = String(slides.length - index);

                        if (slide.image) {
                            const image = document.createElement('img');
                            image.src = slide.image;
                            image.alt = slide.title || 'محصول جانان';
                            image.loading = index < 8 ? 'eager' : 'lazy';
                            image.decoding = 'async';
                            image.fetchPriority = index === 0 ? 'high' : 'auto';
                            image.draggable = false;
                            image.dataset.storeImageFallback = 'جانان';
                            image.dataset.storeImageFallbackClass =
                                'immersive-grid-placeholder';
                            item.appendChild(image);
                        } else {
                            const placeholder = document.createElement('span');
                            placeholder.className = 'immersive-grid-placeholder';
                            placeholder.textContent = 'جانان';
                            item.appendChild(placeholder);
                        }

                        const model = {
                            element: item,
                            slide,
                            index,
                            row,
                            col,
                            baseX: x,
                            baseY: y,
                        };

                        item.addEventListener('click', (event) => {
                            if (
                                event.button !== 0 ||
                                event.metaKey ||
                                event.ctrlKey ||
                                event.shiftKey ||
                                event.altKey
                            ) {
                                return;
                            }

                            event.preventDefault();
                            event.stopPropagation();

                            this.open(model);
                        });

                        grid.appendChild(item);
                        this.items.push(model);
                    });
                }

                applyItemPositions(animated = false) {
                    this.items.forEach((item) => {
                        item.row = Math.floor(item.index / this.cfg.cols);
                        item.col = item.index % this.cfg.cols;
                        item.baseX =
                            item.col * (this.cfg.size + this.cfg.gap);
                        item.baseY =
                            item.row * (this.cfg.size + this.cfg.gap);

                        // Keep the rendered card size in sync with the CSS
                        // breakpoint value used to calculate the canvas.
                        item.element.style.width = this.cfg.size + 'px';
                        item.element.style.height = this.cfg.size + 'px';

                        if (animated) {
                            gsap.to(item.element, {
                                left: item.baseX,
                                top: item.baseY,
                                duration: reducedMotion ? 0.01 : 0.24,
                                overwrite: 'auto',
                                ease: 'power2.out',
                            });
                        } else {
                            gsap.set(item.element, {
                                left: item.baseX,
                                top: item.baseY,
                            });
                        }
                    });
                }

                bounds() {
                    const w = viewport.clientWidth;
                    const h = viewport.clientHeight;
                    const scaledWidth = this.dim.width * this.cfg.zoom;
                    const scaledHeight = this.dim.height * this.cfg.zoom;
                    const margin = Math.max(20, this.cfg.gap * this.cfg.zoom);

                    const x = scaledWidth <= w
                        ? (w - scaledWidth) / 2
                        : {
                            min: w - scaledWidth - margin,
                            max: margin,
                        };

                    const y = scaledHeight <= h
                        ? (h - scaledHeight) / 2
                        : {
                            min: h - scaledHeight - margin,
                            max: margin,
                        };

                    return {
                        minX: typeof x === 'number' ? x : x.min,
                        maxX: typeof x === 'number' ? x : x.max,
                        minY: typeof y === 'number' ? y : y.min,
                        maxY: typeof y === 'number' ? y : y.max,
                    };
                }

                centerPosition() {
                    const w = viewport.clientWidth;
                    const h = viewport.clientHeight;
                    return {
                        x: (w - this.dim.width * this.cfg.zoom) / 2,
                        y: (h - this.dim.height * this.cfg.zoom) / 2,
                    };
                }

                applyPosition(x, y, animated = false) {
                    const b = this.bounds();
                    const nextX = Math.max(b.minX, Math.min(b.maxX, x));
                    const nextY = Math.max(b.minY, Math.min(b.maxY, y));

                    if (animated) {
                        gsap.to(wrap, {
                            x: nextX,
                            y: nextY,
                            duration: reducedMotion ? 0.02 : 0.5,
                            ease: 'power3.out',
                            onUpdate: () => {
                                this.last = {
                                    x: gsap.getProperty(wrap, 'x'),
                                    y: gsap.getProperty(wrap, 'y'),
                                };
                            },
                        });
                    } else {
                        gsap.set(wrap, {
                            x: nextX,
                            y: nextY,
                        });
                        this.last = { x: nextX, y: nextY };
                    }
                }

                init() {
                    this.build();
                    this.cfg.zoom = 0.6;
                    this.cfg.gap = this.getGap();
                    this.dimensions();
                    if (viewport.clientWidth <= 540) {
                        // Keep the initial cards large enough to identify and tap.
                        this.cfg.zoom = Math.max(0.3, Math.min(0.85, (viewport.clientWidth - 32) / this.dim.width));
                        this.cfg.gap = this.getGap();
                        this.dimensions();
                    }
                    this.syncControls();
                    this.applyItemPositions(false);
                    wrap.style.width = this.dim.width + 'px';
                    wrap.style.height = this.dim.height + 'px';

                    const center = this.centerPosition();
                    gsap.set(wrap, {
                        x: center.x,
                        y: center.y,
                        scale: this.cfg.zoom,
                    });
                    this.last = center;

                    const centerX = viewport.clientWidth / 2;
                    const centerY = viewport.clientHeight / 2;

                    this.items.forEach((item) => {
                        gsap.set(item.element, {
                            left: centerX / this.cfg.zoom - this.cfg.size / 2,
                            top: centerY / this.cfg.zoom - this.cfg.size / 2,
                            scale: 0.86,
                            opacity: 0,
                        });
                    });

                    gsap.set(viewport, { opacity: 1 });

                    gsap.to(this.items.map((item) => item.element), {
                        left: (i) => this.items[i].baseX,
                        top: (i) => this.items[i].baseY,
                        scale: 1,
                        opacity: 1,
                        duration: reducedMotion ? 0.01 : 0.32,
                        ease: 'power2.out',
                        stagger: reducedMotion
                            ? 0
                            : { amount: 0.18, from: 'start' },
                        onComplete: () => {
                            this.introPlaying = false;
                            this.relayout();
                            controls.classList.add('is-visible');
                            root.dataset.galleryReady = 'true';
                            gsap.to(root.querySelector('.immersive-footer'), {
                                opacity: 1,
                                duration: reducedMotion ? 0.01 : 0.45,
                            });
                        },
                    });
                }

                initDrag() {
                    this.drag?.kill();
                    this.dimensions();

                    this.drag = Draggable.create(wrap, {
                        type: 'x,y',
                        bounds: this.bounds(),
                        edgeResistance: 0.95,
                        minimumMovement: 8,
                        inertia: false,
                        onDragStart: () => root.classList.add('is-dragging'),
                        onDrag: () => {
                            this.last = {
                                x: this.drag.x,
                                y: this.drag.y,
                            };
                        },
                        onDragEnd: () => {
                            root.classList.remove('is-dragging');
                            this.applyPosition(this.drag.x, this.drag.y);
                            this.drag.update();
                        },
                    })[0];
                }

                renderDescription(text) {
                    description.replaceChildren();

                    const value = String(text || '');
                    const line = document.createElement('span');
                    line.className = 'immersive-description-line';
                    line.textContent = value;
                    description.appendChild(line);

                    return [line];
                }

                updateDetails(model) {
                    const slide = model.slide || {};
                    number.textContent = persianDigits(String(
                        slide.number || model.index + 1
                    ).padStart(2, '0'));
                    title.textContent = persianDigits(slide.title || 'جانان');
                    if (productMeta) {
                        productMeta.replaceChildren();
                        [slide.brand, slide.category, slide.availability, ...(Array.isArray(slide.facts) ? slide.facts : [])]
                            .filter(Boolean).forEach(value => {
                                const chip = document.createElement('span');
                                chip.textContent = persianDigits(String(value));
                                productMeta.appendChild(chip);
                            });
                    }
                    if (productPrice) productPrice.textContent = slide.price || 'قیمت در صفحه محصول';

                    link.href = slide.url || '#';
                    link.hidden = !slide.url;

                    target.href = slide.url || '#';
                    target.setAttribute(
                        'aria-label',
                        (slide.title || 'محصول جانان') + ' — مشاهده محصول'
                    );

                    return this.renderDescription(
                        slide.description ||
                        (slide.brand
                            ? slide.brand + ' · منتخب جانان برای این قاب.'
                            : 'منتخبی از کالکشن جانان.')
                    );
                }

                createOverlay(model) {
                    const anchor = document.createElement('button');
                    anchor.type = 'button';
                    anchor.className = 'immersive-scaling-overlay';
                    anchor.dataset.productUrl = model.slide.url || '';
                    anchor.setAttribute(
                        'aria-label',
                        (model.slide.title || 'محصول جانان') + ' — مشاهده محصول'
                    );
                    anchor.setAttribute('aria-live', 'off');
                    anchor.tabIndex = -1;

                    if (model.slide.image) {
                        const image = document.createElement('img');
                        image.src = model.slide.image;
                        image.alt = model.slide.title || 'محصول جانان';
                        image.loading = 'eager';
                        image.decoding = 'async';
                        image.draggable = false;
                        image.dataset.storeImageFallback = 'جانان';
                        image.dataset.storeImageFallbackClass =
                            'immersive-overlay-placeholder';
                        anchor.appendChild(image);
                    } else {
                        const placeholder = document.createElement('span');
                        placeholder.className = 'immersive-overlay-placeholder';
                        placeholder.textContent = 'جانان';
                        anchor.appendChild(placeholder);
                    }

                    root.appendChild(anchor);
                    const rect = model.element.getBoundingClientRect();

                    gsap.set(anchor, {
                        left: rect.left,
                        top: rect.top,
                        width: rect.width,
                        height: rect.height,
                        opacity: 1,
                    });

                    return anchor;
                }

                navigateToProduct(url) {
                    if (!url) return;

                    try {
                        const targetUrl = new URL(url, window.location.origin);

                        window.location.assign(
                            targetUrl.pathname + targetUrl.search + targetUrl.hash
                        );
                    } catch {
                        return;
                    }
                }

                open(model) {
                    if (this.introPlaying || this.zoom.active) return;
                    this.finishZoomMotion();
                    this.returnFocus = model.element;
                    const overlayAnchor = this.createOverlay(model);
                    this.zoom = {
                        active: true,
                        item: model,
                        overlay: overlayAnchor,
                    };

                    overlayAnchor.style.pointerEvents = 'none';
                    this.drag?.disable();
                    root.classList.add('is-zoomed');
                    document.body.classList.add('immersive-zoom-lock');
                    split.classList.add('is-active');
                    controls.classList.add('is-split');
                    close.classList.add('is-active');
                    viewport.inert = true;
                    controls.inert = true;
                    overlay.removeAttribute('inert');
                    overlay.setAttribute('aria-hidden', 'false');
                    root.setAttribute('role', 'dialog');
                    root.setAttribute('aria-modal', 'true');
                    root.setAttribute('aria-labelledby', 'immersiveSlideTitle');
                    close.disabled = false;
                    close.setAttribute('aria-hidden', 'false');
                    const lines = this.updateDetails(model);
                    close.focus({ preventScroll: true });
                    gsap.set(overlay, { opacity: 0 });
                    gsap.set([number, title, ...lines, close], {
                        opacity: 0,
                    });
                    gsap.set(model.element, { opacity: 0 });

                    const duration = reducedMotion ? 0.01 : 0.42;

                    gsap.to(split, {
                        opacity: 1,
                        duration: reducedMotion ? 0.02 : 0.28,
                    });

                    gsap.to(overlay, {
                        opacity: 1,
                        duration: reducedMotion ? 0.02 : 0.22,
                    });

                    Flip.fit(overlayAnchor, target, {
                        absolute: true,
                        duration,
                        ease: 'power3.inOut',
                        onComplete: () => {
                            if (!this.zoom.active || this.zoom.closing || this.zoom.overlay !== overlayAnchor) return;

                            overlayAnchor.style.pointerEvents = 'auto';
                            overlayAnchor.tabIndex = 0;
                            root.dataset.productReady = 'true';
                            if (this.layoutPending) Flip.fit(overlayAnchor, target, {absolute:true,duration:0});
                            overlay.classList.add('is-active');

                            overlayAnchor.addEventListener('click', (event) => {
                                event.preventDefault();
                                event.stopPropagation();
                                this.navigateToProduct(model.slide.url);
                            }, { once: true });

                            gsap.fromTo(
                                number,
                                { y: 16, opacity: 0 },
                                { y: 0, opacity: 1, duration: reducedMotion ? 0.04 : 0.4 }
                            );
                            gsap.fromTo(
                                title,
                                { y: 32, opacity: 0 },
                                { y: 0, opacity: 1, duration: reducedMotion ? 0.04 : 0.44, delay: reducedMotion ? 0 : 0.04 }
                            );
                            gsap.fromTo(
                                lines,
                                { y: 44, opacity: 0 },
                                {
                                    y: 0,
                                    opacity: 1,
                                    duration: reducedMotion ? 0.04 : 0.44,
                                    delay: reducedMotion ? 0 : 0.08,
                                }
                            );
                            gsap.fromTo(
                                close,
                                { x: 26, opacity: 0 },
                                { x: 0, opacity: 1, duration: reducedMotion ? 0.04 : 0.34 }
                            );
                        },
                    });
                }

                close() {
                    if (!this.zoom.active || this.zoom.closing) return;

                    const current = this.zoom;
                    current.closing = true;
                    delete root.dataset.productReady;
                    const overlayAnchor = current.overlay;
                    const lines = [...description.querySelectorAll(
                        '.immersive-description-line'
                    )];

                    overlayAnchor.style.pointerEvents = 'none';
                    overlayAnchor.tabIndex = -1;
                    gsap.killTweensOf([overlay, split, number, title, ...lines, close]);
                    Flip.killFlipsOf(overlayAnchor);

                    gsap.to(
                        [number, title, ...lines, close],
                        {
                            y: -12,
                            opacity: 0,
                            duration: reducedMotion ? 0.03 : 0.22,
                            stagger: reducedMotion ? 0 : -0.02,
                        }
                    );

                    gsap.to(overlay, {
                        opacity: 0,
                        duration: reducedMotion ? 0.03 : 0.18,
                    });

                    gsap.to(split, {
                        opacity: 0,
                        duration: reducedMotion ? 0.03 : 0.25,
                    });

                    Flip.fit(
                        overlayAnchor,
                        current.item.element,
                        {
                            absolute: true,
                            duration: reducedMotion ? 0.01 : 0.36,
                            ease: 'power3.inOut',
                            onComplete: () => {
                                current.item.element.style.opacity = '1';
                                current.item.element.classList.remove('is-returning');
                                // A small landing cue confirms the card has returned to its grid slot.
                                void current.item.element.offsetWidth;
                                current.item.element.classList.add('is-returning');
                                current.item.element.addEventListener('animationend', () => {
                                    current.item.element.classList.remove('is-returning');
                                }, { once: true });
                                overlayAnchor.remove();
                                overlay.classList.remove('is-active');
                                close.classList.remove('is-active');
                                controls.classList.remove('is-split');
                                split.classList.remove('is-active');
                                root.classList.remove('is-zoomed');
                                document.body.classList.remove('immersive-zoom-lock');
                                viewport.inert = false;
                                controls.inert = false;
                                overlay.setAttribute('inert', '');
                                overlay.setAttribute('aria-hidden', 'true');
                                root.removeAttribute('role');
                                root.removeAttribute('aria-modal');
                                root.removeAttribute('aria-labelledby');
                                close.disabled = true;
                                close.setAttribute('aria-hidden', 'true');
                                this.drag?.enable();
                                this.zoom = {
                                    active: false,
                                    item: null,
                                    overlay: null,
                                };
                                if (this.layoutPending) {
                                    this.layoutPending = false;
                                    this.relayout();
                                }
                                this.returnFocus?.focus({ preventScroll: true });
                                this.returnFocus = null;
                            },
                        }
                    );
                }

                syncControls() {
                    percentage.textContent = persianDigits(Math.round(this.cfg.zoom * 100)) + '٪';
                    root.querySelectorAll('[data-zoom], [data-fit]').forEach(node => {
                        const selected = node.hasAttribute('data-fit')
                            ? this.fitMode
                            : !this.fitMode && Math.abs(Number(node.dataset.zoom) - this.cfg.zoom) < 0.001;
                        node.classList.toggle('is-current', selected);
                        node.setAttribute('aria-pressed', String(selected));
                    });
                }

                finishZoomMotion() {
                    if (!this.zoomTween) return;
                    this.zoomTween.kill();
                    this.zoomTween = null;
                    this.applyItemPositions(false);
                    const b = this.bounds();
                    const x = Math.max(b.minX, Math.min(b.maxX, Number(gsap.getProperty(wrap, 'x')) || 0));
                    const y = Math.max(b.minY, Math.min(b.maxY, Number(gsap.getProperty(wrap, 'y')) || 0));
                    gsap.set(wrap, {scale:this.cfg.zoom, x, y});
                    this.last = {x,y};
                    this.initDrag();
                }

                fittedScale() {
                    const gap = this.getGap(0.25);
                    const width = this.cfg.cols * this.cfg.size + Math.max(0, this.cfg.cols - 1) * gap;
                    const height = this.cfg.rows * this.cfg.size + Math.max(0, this.cfg.rows - 1) * gap;
                    return Math.max(0.05, Math.min(1,
                        Math.max(1, viewport.clientWidth - 32) / width,
                        Math.max(1, viewport.clientHeight - 180) / height));
                }

                transitionZoom(value, fit = false) {
                    if (this.introPlaying || this.zoom.active) return;
                    this.zoomTween?.kill();
                    this.drag?.disable();
                    const actualScale = Number(gsap.getProperty(wrap, 'scaleX')) || this.cfg.zoom;
                    const actualX = Number(gsap.getProperty(wrap, 'x')) || 0;
                    const actualY = Number(gsap.getProperty(wrap, 'y')) || 0;
                    const oldCenter = {x:(viewport.clientWidth/2-actualX)/actualScale,y:(viewport.clientHeight/2-actualY)/actualScale};
                    this.fitMode = fit;
                    this.cfg.zoom = fit ? this.fittedScale() : Math.max(0.25,Math.min(1,value));
                    this.cfg.gap = this.getGap();
                    this.dimensions();
                    wrap.style.width = this.dim.width + 'px';
                    wrap.style.height = this.dim.height + 'px';
                    this.items.forEach(item => gsap.killTweensOf(item.element, 'left,top'));
                    this.applyItemPositions(false);
                    const center = fit ? this.centerPosition() : {
                        x:viewport.clientWidth/2-oldCenter.x*this.cfg.zoom,
                        y:viewport.clientHeight/2-oldCenter.y*this.cfg.zoom
                    };
                    const b = this.bounds();
                    const x = Math.max(b.minX,Math.min(b.maxX,center.x));
                    const y = Math.max(b.minY,Math.min(b.maxY,center.y));
                    this.syncControls();
                    this.zoomTween = gsap.to(wrap, {
                        scale:this.cfg.zoom,x,y,overwrite:true,
                        duration:reducedMotion?0.01:0.3,ease:'power2.out',
                        onComplete:()=>{this.zoomTween=null;this.last={x,y};this.initDrag();}
                    });
                }

                setZoom(value) { this.transitionZoom(value); }

                fit() { this.transitionZoom(1,true); }

                relayout() {
                    if (this.introPlaying) return;
                    if (this.zoom.active) {
                        this.layoutPending = true;
                        // Never interrupt an opening or closing FLIP transition.
                        if (!this.zoom.closing && root.dataset.productReady === 'true') {
                            Flip.fit(this.zoom.overlay,target,{absolute:true,duration:0});
                        }
                        return;
                    }
                    this.zoomTween?.kill();
                    this.zoomTween=null;
                    const changed=this.syncConfig();
                    if(this.fitMode) this.cfg.zoom=this.fittedScale();
                    this.cfg.gap=this.getGap();
                    this.dimensions();
                    wrap.style.width=this.dim.width+'px';
                    wrap.style.height=this.dim.height+'px';
                    this.applyItemPositions(false);
                    gsap.set(wrap,{scale:this.cfg.zoom});
                    const center=this.centerPosition();
                    this.applyPosition(changed||this.fitMode?center.x:this.last.x,changed||this.fitMode?center.y:this.last.y);
                    this.syncControls();
                    this.initDrag();
                }

                scheduleRelayout() {
                    if (this.layoutFrame) return;

                    this.layoutFrame = window.requestAnimationFrame(() => {
                        this.layoutFrame = 0;
                        this.relayout();
                    });
                }
            }

            const gallery = new Gallery();

            // Mobile touch contract:
            // GSAP Draggable owns the canvas gesture, so provide an explicit
            // tap path for cards. A tap is only accepted when the pointer
            // travels less than the threshold; real drags remain untouched.
            let mobileTapStart = null;
            let mobileTapHandledUntil = 0;
            const mobileTapQuery = window.matchMedia?.('(max-width: 820px)');

            root.addEventListener('pointerdown', (event) => {
                if (!mobileTapQuery?.matches || event.pointerType === 'mouse' || !event.isPrimary) return;

                const item = event.target.closest?.('.immersive-grid-item');
                if (!item || !root.contains(item)) {
                    mobileTapStart = null;
                    return;
                }

                mobileTapStart = {
                    pointerId: event.pointerId,
                    x: event.clientX,
                    y: event.clientY,
                    item,
                };
            }, true);

            root.addEventListener('pointerup', (event) => {
                if (!mobileTapQuery?.matches || event.pointerType === 'mouse') return;
                if (!mobileTapStart || mobileTapStart.pointerId !== event.pointerId) return;

                const start = mobileTapStart;
                mobileTapStart = null;

                const distance = Math.hypot(
                    event.clientX - start.x,
                    event.clientY - start.y
                );

                if (distance > 10 || gallery.zoom.active || gallery.drag?.isDragging) return;

                const model = gallery.items.find(
                    (candidate) => candidate.element === start.item
                );

                if (!model) return;

                event.preventDefault();
                event.stopPropagation();
                mobileTapHandledUntil = performance.now() + 450;
                gallery.open(model);
            }, true);

            root.addEventListener('pointercancel', () => {
                mobileTapStart = null;
            });

            root.addEventListener('click', (event) => {
                if (event.target.closest?.('.immersive-grid-item') && mobileTapHandledUntil > performance.now()) {
                    event.preventDefault();
                    event.stopPropagation();
                    mobileTapHandledUntil = 0;
                }
            }, true);

            left.addEventListener('click', (event) => {
                if (event.target === left) gallery.close();
            });

            right.addEventListener('click', () => gallery.close());

            link.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                gallery.navigateToProduct(link.href);
            });

            target.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                gallery.navigateToProduct(target.href);
            });

            close.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                gallery.close();
            });

            root.querySelectorAll('[data-zoom]').forEach((button) => {
                button.addEventListener('click', () => {
                    gallery.setZoom(Number(button.dataset.zoom), button);
                });
            });

            root.querySelector('[data-fit]')?.addEventListener('click', () => {
                gallery.fit();
            });

            document.addEventListener('keydown', (event) => {
                if (gallery.zoom.active) {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        gallery.close();
                    }
                    if (event.key === 'Tab') {
                        const candidates = [close, link, gallery.zoom.overlay].filter(node => node && !node.hidden && !node.disabled && node.tabIndex >= 0);
                        if (!candidates.length) return;
                        const index = candidates.indexOf(document.activeElement);
                        const next = index < 0 ? (event.shiftKey ? candidates.length-1 : 0) : (index + (event.shiftKey ? -1 : 1) + candidates.length) % candidates.length;
                        event.preventDefault();
                        candidates[next].focus({preventScroll:true});
                    }
                    return;
                }
                if (!root.contains(document.activeElement) || event.target.matches?.('input,textarea,select,[contenteditable]') || event.ctrlKey || event.metaKey || event.altKey) return;
                const actions = {'1':()=>gallery.setZoom(0.3),'2':()=>gallery.setZoom(0.6),'3':()=>gallery.setZoom(1),'f':()=>gallery.fit()};
                const action = actions[event.key.toLowerCase()];
                if(action) {event.preventDefault();action();}
            });

            root.addEventListener('mouseleave', () => {
                if (!gallery.drag || gallery.zoom.active) return;
                root.classList.remove('is-dragging');
            });

            if ('ResizeObserver' in window) {
                const resizeObserver = new ResizeObserver(() => {
                    gallery.scheduleRelayout();
                });
                resizeObserver.observe(viewport);
                resizeObserver.observe(root);
            } else {
                window.addEventListener('orientationchange', () => {
                    gallery.scheduleRelayout();
                }, { passive: true });
            }

            gallery.init();
        };

        initGallery();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
