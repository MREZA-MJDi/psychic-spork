(() => {
    const boot = () => {
        if (window.__JANAN_IMMERSIVE_HERO_INITIALIZED__) return;
        window.__JANAN_IMMERSIVE_HERO_INITIALIZED__ = true;

        const root = document.querySelector('[data-immersive-gallery]');
        if (!root) return;

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

        let slides = [];
        try {
            const payload = JSON.parse(data.textContent || '[]');
            slides = Array.isArray(payload) ? payload.slice(0, 30) : [];
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

        const loadScript = (src) => new Promise((resolve, reject) => {
            const existing = document.querySelector(
                'script[data-janan-gsap-src="' + src + '"]'
            );

            if (existing) {
                existing.addEventListener('load', () => resolve(), { once: true });
                existing.addEventListener('error', () => reject(new Error('script-load-failed')), { once: true });
                if (
                    (src.endsWith('gsap.min.js') && window.gsap) ||
                    (src.includes('Draggable') && window.Draggable) ||
                    (src.includes('Flip') && window.Flip)
                ) {
                    resolve();
                }
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.defer = true;
            script.dataset.jananGsapSrc = src;
            script.onload = resolve;
            script.onerror = () => reject(new Error('script-load-failed'));
            document.head.appendChild(script);
        });

        const ensureGsap = async () => {
            if (window.gsap && window.Draggable && window.Flip) {
                window.gsap.registerPlugin(window.Draggable, window.Flip);
                return true;
            }

            if (!window.__JANAN_GSAP_LOAD__) {
                window.__JANAN_GSAP_LOAD__ = (async () => {
                    await loadScript(
                        'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js'
                    );
                    await Promise.all([
                        loadScript(
                            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/Draggable.min.js'
                        ),
                        loadScript(
                            'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/Flip.min.js'
                        ),
                    ]);

                    if (!window.gsap || !window.Draggable || !window.Flip) {
                        throw new Error('gsap-incomplete');
                    }

                    window.gsap.registerPlugin(window.Draggable, window.Flip);
                })();
            }

            try {
                await window.__JANAN_GSAP_LOAD__;
                return true;
            } catch {
                return false;
            }
        };

        const renderStaticFallback = () => {
            root.classList.add('is-static-fallback');
            viewport.style.opacity = '1';
            viewport.style.overflow = 'auto';
            wrap.style.position = 'relative';
            wrap.style.top = 'auto';
            wrap.style.left = 'auto';
            wrap.style.width = '100%';
            wrap.style.height = '100%';
            wrap.style.transform = 'none';
            grid.style.display = 'grid';
            grid.style.gridTemplateColumns =
                window.innerWidth <= 540
                    ? 'repeat(2, minmax(0, 1fr))'
                    : 'repeat(4, minmax(0, 1fr))';
            grid.style.gap = window.innerWidth <= 540 ? '6px' : '10px';
            grid.replaceChildren();

            slides.forEach((slide, index) => {
                const item = document.createElement('a');
                item.className = 'immersive-grid-item';
                item.href = slide.url || '#';
                item.style.position = 'relative';
                item.style.inset = 'auto';
                item.style.width = '100%';
                item.style.height = 'auto';
                item.style.aspectRatio = '1';
                item.style.opacity = '1';
                item.style.setProperty('--hero-intro-index', String(Math.min(index, 12)));

                if (slide.image) {
                    const image = document.createElement('img');
                    image.src = slide.image;
                    image.alt = slide.title || 'محصول جانان';
                    image.loading = 'lazy';
                    image.decoding = 'async';
                    image.draggable = false;
                    item.appendChild(image);
                }

                grid.appendChild(item);
            });
        };

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
                            image.dataset.storeImageFallback = 'JANAN';
                            image.dataset.storeImageFallbackClass =
                                'immersive-grid-placeholder';
                            item.appendChild(image);
                        } else {
                            const placeholder = document.createElement('span');
                            placeholder.className = 'immersive-grid-placeholder';
                            placeholder.textContent = 'JANAN';
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

                            this.open(model);
                        });

                        grid.appendChild(item);
                        this.items.push(model);
                    });
                }

                applyItemPositions(animated = false) {
                    this.items.forEach((item) => {
                        item.baseX =
                            item.col * (this.cfg.size + this.cfg.gap);
                        item.baseY =
                            item.row * (this.cfg.size + this.cfg.gap);

                        // Keep the rendered card size in sync with the CSS
                        // breakpoint value used to calculate the canvas.
                        item.element.style.width = this.cfg.size + 'px';
                        item.element.style.height = this.cfg.size + 'px';

                        if (animated) {
                            window.gsap.to(item.element, {
                                left: item.baseX,
                                top: item.baseY,
                                duration: reducedMotion ? 0.01 : 0.42,
                                ease: 'power2.out',
                            });
                        } else {
                            window.gsap.set(item.element, {
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
                        window.gsap.to(wrap, {
                            x: nextX,
                            y: nextY,
                            duration: reducedMotion ? 0.02 : 0.5,
                            ease: 'power3.out',
                            onUpdate: () => {
                                this.last = {
                                    x: window.gsap.getProperty(wrap, 'x'),
                                    y: window.gsap.getProperty(wrap, 'y'),
                                };
                            },
                        });
                    } else {
                        window.gsap.set(wrap, {
                            x: nextX,
                            y: nextY,
                        });
                        this.last = { x: nextX, y: nextY };
                    }
                }

                init() {
                    this.build();
                    this.cfg.zoom = viewport.clientWidth <= 540 ? 1 : 0.6;
                    this.cfg.gap = this.getGap();
                    this.dimensions();
                    if (viewport.clientWidth <= 540) {
                        const fit = Math.min(
                            1,
                            (viewport.clientWidth - 32) / this.dim.width,
                            (viewport.clientHeight - 150) / this.dim.height
                        );
                        this.cfg.zoom = Math.max(0.25, fit);
                        this.cfg.gap = this.getGap();
                        this.dimensions();
                        percentage.textContent = Math.round(this.cfg.zoom * 100) + '%';
                    }
                    wrap.style.width = this.dim.width + 'px';
                    wrap.style.height = this.dim.height + 'px';

                    const center = this.centerPosition();
                    window.gsap.set(wrap, {
                        x: center.x,
                        y: center.y,
                        scale: this.cfg.zoom,
                    });
                    this.last = center;

                    const centerX = viewport.clientWidth / 2;
                    const centerY = viewport.clientHeight / 2;

                    this.items.forEach((item) => {
                        window.gsap.set(item.element, {
                            left: centerX / this.cfg.zoom - this.cfg.size / 2,
                            top: centerY / this.cfg.zoom - this.cfg.size / 2,
                            scale: 0.86,
                            opacity: 0,
                        });
                    });

                    window.gsap.set(viewport, { opacity: 1 });

                    window.gsap.to(this.items.map((item) => item.element), {
                        left: (i) => this.items[i].baseX,
                        top: (i) => this.items[i].baseY,
                        scale: 1,
                        opacity: 1,
                        duration: reducedMotion ? 0.05 : 0.52,
                        ease: 'power2.out',
                        stagger: reducedMotion
                            ? 0
                            : { amount: 0.45, from: 'start' },
                        onComplete: () => {
                            controls.classList.add('is-visible');
                            window.gsap.to(root.querySelector('.immersive-footer'), {
                                opacity: 1,
                                duration: reducedMotion ? 0.01 : 0.45,
                            });
                            this.initDrag();
                        },
                    });
                }

                initDrag() {
                    this.drag?.kill();
                    this.dimensions();

                    this.drag = window.Draggable.create(wrap, {
                        type: 'x,y',
                        bounds: this.bounds(),
                        edgeResistance: 0.82,
                        inertia: false,
                        onDragStart: () => root.classList.add('is-dragging'),
                        onDrag: () => {
                            this.last = {
                                x: this.drag.x,
                                y: this.drag.y,
                            };
                        },
                        onDragEnd: () => root.classList.remove('is-dragging'),
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
                    number.textContent = String(
                        slide.number || model.index + 1
                    ).padStart(2, '0');
                    title.textContent = slide.title || 'JANAN';

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
                    const anchor = document.createElement('a');
                    anchor.className = 'immersive-scaling-overlay';
                    anchor.href = model.slide.url || '#';
                    anchor.setAttribute(
                        'aria-label',
                        (model.slide.title || 'محصول جانان') + ' — مشاهده محصول'
                    );
                    anchor.setAttribute('aria-live', 'off');

                    if (model.slide.image) {
                        const image = document.createElement('img');
                        image.src = model.slide.image;
                        image.alt = model.slide.title || 'محصول جانان';
                        image.loading = 'eager';
                        image.decoding = 'async';
                        image.draggable = false;
                        image.dataset.storeImageFallback = 'JANAN';
                        image.dataset.storeImageFallbackClass =
                            'immersive-overlay-placeholder';
                        anchor.appendChild(image);
                    } else {
                        const placeholder = document.createElement('span');
                        placeholder.className = 'immersive-overlay-placeholder';
                        placeholder.textContent = 'JANAN';
                        anchor.appendChild(placeholder);
                    }

                    root.appendChild(anchor);
                    const rect = model.element.getBoundingClientRect();

                    window.gsap.set(anchor, {
                        left: rect.left,
                        top: rect.top,
                        width: rect.width,
                        height: rect.height,
                        opacity: 1,
                    });

                    return anchor;
                }

                open(model) {
                    if (this.zoom.active) return;

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

                    const lines = this.updateDetails(model);
                    window.gsap.set(overlay, { opacity: 0 });
                    window.gsap.set([number, title, ...lines, close], {
                        opacity: 0,
                    });
                    window.gsap.set(
                        itemOrArray(model.element),
                        { opacity: 0 }
                    );

                    const duration = reducedMotion ? 0.04 : 0.74;

                    window.gsap.to(split, {
                        opacity: 1,
                        duration: reducedMotion ? 0.02 : 0.28,
                    });

                    window.gsap.to(overlay, {
                        opacity: 1,
                        duration: reducedMotion ? 0.02 : 0.22,
                    });

                    window.Flip.fit(overlayAnchor, target, {
                        absolute: true,
                        duration,
                        ease: 'power3.inOut',
                        onComplete: () => {
                            overlayAnchor.style.pointerEvents = 'auto';
                            overlay.classList.add('is-active');

                            window.gsap.fromTo(
                                number,
                                { y: 16, opacity: 0 },
                                { y: 0, opacity: 1, duration: reducedMotion ? 0.04 : 0.4 }
                            );
                            window.gsap.fromTo(
                                title,
                                { y: 32, opacity: 0 },
                                { y: 0, opacity: 1, duration: reducedMotion ? 0.04 : 0.44, delay: reducedMotion ? 0 : 0.04 }
                            );
                            window.gsap.fromTo(
                                lines,
                                { y: 44, opacity: 0 },
                                {
                                    y: 0,
                                    opacity: 1,
                                    duration: reducedMotion ? 0.04 : 0.44,
                                    delay: reducedMotion ? 0 : 0.08,
                                }
                            );
                            window.gsap.fromTo(
                                close,
                                { x: 26, opacity: 0 },
                                { x: 0, opacity: 1, duration: reducedMotion ? 0.04 : 0.34 }
                            );
                        },
                    });
                }

                close() {
                    if (!this.zoom.active) return;

                    const current = this.zoom;
                    const overlayAnchor = current.overlay;
                    const lines = [...description.querySelectorAll(
                        '.immersive-description-line'
                    )];

                    overlayAnchor.style.pointerEvents = 'none';

                    window.gsap.to(
                        [number, title, ...lines, close],
                        {
                            y: -12,
                            opacity: 0,
                            duration: reducedMotion ? 0.03 : 0.22,
                            stagger: reducedMotion ? 0 : -0.02,
                        }
                    );

                    window.gsap.to(overlay, {
                        opacity: 0,
                        duration: reducedMotion ? 0.03 : 0.18,
                    });

                    window.gsap.to(split, {
                        opacity: 0,
                        duration: reducedMotion ? 0.03 : 0.25,
                    });

                    window.Flip.fit(
                        overlayAnchor,
                        current.item.element,
                        {
                            absolute: true,
                            duration: reducedMotion ? 0.04 : 0.62,
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
                                this.drag?.enable();
                                this.zoom = {
                                    active: false,
                                    item: null,
                                    overlay: null,
                                };
                            },
                        }
                    );
                }

                setZoom(value, button) {
                    if (this.zoom.active) return;

                    const zoom = Math.max(0.25, Math.min(1, value));
                    const oldZoom = this.cfg.zoom;
                    const oldCenter = {
                        x: (viewport.clientWidth / 2 - this.last.x) / oldZoom,
                        y: (viewport.clientHeight / 2 - this.last.y) / oldZoom,
                    };

                    this.cfg.zoom = zoom;
                    this.cfg.gap = this.getGap(zoom);
                    this.dimensions();
                    this.applyItemPositions(true);

                    const x = viewport.clientWidth / 2 - oldCenter.x * zoom;
                    const y = viewport.clientHeight / 2 - oldCenter.y * zoom;

                    window.gsap.to(wrap, {
                        scale: zoom,
                        x,
                        y,
                        duration: reducedMotion ? 0.04 : 0.62,
                        ease: 'power3.inOut',
                        onComplete: () => {
                            this.last = {
                                x: window.gsap.getProperty(wrap, 'x'),
                                y: window.gsap.getProperty(wrap, 'y'),
                            };
                            this.initDrag();
                        },
                    });

                    percentage.textContent = Math.round(zoom * 100) + '%';

                    root.querySelectorAll('[data-zoom]').forEach((node) => {
                        node.classList.toggle(
                            'is-current',
                            Number(node.dataset.zoom) === zoom
                        );
                    });

                    button?.focus({ preventScroll: true });
                }

                fit() {
                    if (this.zoom.active) return;

                    this.cfg.zoom = 1;
                    this.cfg.gap = this.getGap(1);
                    this.dimensions();

                    const scaleX = (viewport.clientWidth - 80) / this.dim.width;
                    const scaleY = (viewport.clientHeight - 130) / this.dim.height;
                    const zoom = Math.max(0.25, Math.min(1, scaleX, scaleY));

                    this.cfg.zoom = zoom;
                    this.cfg.gap = this.getGap(zoom);
                    this.dimensions();
                    this.applyItemPositions();

                    const center = this.centerPosition();

                    window.gsap.to(wrap, {
                        scale: zoom,
                        x: center.x,
                        y: center.y,
                        duration: reducedMotion ? 0.04 : 0.62,
                        ease: 'power3.inOut',
                        onComplete: () => this.initDrag(),
                    });

                    this.last = center;
                    percentage.textContent = Math.round(zoom * 100) + '%';

                    root.querySelectorAll('[data-zoom]').forEach((node) => {
                        node.classList.remove('is-current');
                    });
                }

                relayout() {
                    if (root.classList.contains('is-static-fallback')) return;

                    if (this.zoom.active) {
                        const overlayAnchor = this.zoom.overlay;
                        if (overlayAnchor) {
                            window.Flip.fit(overlayAnchor, target, {
                                absolute: true,
                                duration: 0,
                            });
                        }
                        return;
                    }

                    const changed = this.syncConfig();
                    this.dimensions();
                    wrap.style.width = this.dim.width + 'px';
                    wrap.style.height = this.dim.height + 'px';

                    const center = this.centerPosition();

                    if (changed) {
                        this.applyItemPositions(false);
                    }

                    this.applyPosition(
                        changed ? center.x : this.last.x,
                        changed ? center.y : this.last.y,
                        false
                    );

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

            const itemOrArray = (item) => item;
            const gallery = new Gallery();

            left.addEventListener('click', (event) => {
                if (event.target === left) gallery.close();
            });

            right.addEventListener('click', () => gallery.close());

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
                    return;
                }

                if (event.key === '1') gallery.setZoom(0.3);
                if (event.key === '2') gallery.setZoom(0.6);
                if (event.key === '3') gallery.setZoom(1);
                if (event.key.toLowerCase() === 'f') gallery.fit();
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

        ensureGsap().then((ready) => {
            if (!ready) {
                renderStaticFallback();
                return;
            }

            initGallery();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
