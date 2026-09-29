document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const menu = document.querySelector('[data-admin-menu]');

    menu?.addEventListener('click', () => {
        const isOpen = sidebar?.classList.toggle('is-open') ?? false;
        menu.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    sidebar?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            sidebar.classList.remove('is-open');
            menu?.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('click', (event) => {
        if (window.innerWidth > 820) return;
        if (!sidebar?.classList.contains('is-open')) return;
        if (sidebar.contains(event.target) || menu?.contains(event.target)) return;
        sidebar.classList.remove('is-open');
        menu?.setAttribute('aria-expanded', 'false');
    });
});


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-media-picker]').forEach((field) => {
        const input = field.querySelector('[data-media-input]');
        const editor = field.querySelector('[data-media-editor]');
        const canvas = field.querySelector('[data-media-canvas]');
        const zoom = field.querySelector('[data-media-zoom]');
        const apply = field.querySelector('[data-media-apply]');
        const cancel = field.querySelector('[data-media-cancel]');
        const preview = field.querySelector('[data-media-preview]');
        const fileName = field.querySelector('[data-media-file-name]');
        const ctx = canvas?.getContext('2d');

        if (!input || !editor || !canvas || !zoom || !apply || !ctx) return;

        let image = null;
        let offsetX = 0;
        let offsetY = 0;
        let scale = 1;
        let dragging = false;
        let dragStartX = 0;
        let dragStartY = 0;

        const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

        const draw = () => {
            if (!image) return;

            const baseScale = Math.max(
                canvas.width / image.width,
                canvas.height / image.height
            );

            const renderedWidth = image.width * baseScale * scale;
            const renderedHeight = image.height * baseScale * scale;

            const maxX = Math.max(0, (renderedWidth - canvas.width) / 2);
            const maxY = Math.max(0, (renderedHeight - canvas.height) / 2);

            offsetX = clamp(offsetX, -maxX, maxX);
            offsetY = clamp(offsetY, -maxY, maxY);

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#201a1d';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            const x = (canvas.width - renderedWidth) / 2 + offsetX;
            const y = (canvas.height - renderedHeight) / 2 + offsetY;

            ctx.drawImage(image, x, y, renderedWidth, renderedHeight);
        };

        const openEditor = (src, name) => {
            image = new Image();

            image.onload = () => {
                offsetX = 0;
                offsetY = 0;
                scale = 1;
                zoom.value = '1';
                draw();
                editor.hidden = false;
                fileName.textContent = name;
            };

            image.src = src;
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = () => openEditor(String(reader.result), file.name);
            reader.readAsDataURL(file);
        });

        zoom.addEventListener('input', () => {
            scale = Number(zoom.value) || 1;
            draw();
        });

        const pointFromEvent = (event) =>
            event.touches?.[0] ?? event.changedTouches?.[0] ?? event;

        const startDrag = (event) => {
            if (!image) return;

            const point = pointFromEvent(event);
            dragging = true;
            dragStartX = point.clientX - offsetX;
            dragStartY = point.clientY - offsetY;

            event.preventDefault?.();
        };

        const moveDrag = (event) => {
            if (!dragging) return;

            const point = pointFromEvent(event);
            offsetX = point.clientX - dragStartX;
            offsetY = point.clientY - dragStartY;
            draw();

            event.preventDefault?.();
        };

        const endDrag = () => {
            dragging = false;
        };

        canvas.addEventListener('mousedown', startDrag);
        window.addEventListener('mousemove', moveDrag);
        window.addEventListener('mouseup', endDrag);
        canvas.addEventListener('touchstart', startDrag, { passive: false });
        window.addEventListener('touchmove', moveDrag, { passive: false });
        window.addEventListener('touchend', endDrag);

        const replaceInputWithCrop = () => new Promise((resolve) => {
            if (!image || !input.files?.length) {
                resolve(false);
                return;
            }

            canvas.toBlob((blob) => {
                if (!blob) {
                    resolve(false);
                    return;
                }

                const originalName = input.files[0].name || 'image';
                const baseName = originalName.replace(/\\.[^/.]+$/, '') || 'image';
                const croppedFile = new File(
                    [blob],
                    baseName + '.webp',
                    { type: 'image/webp', lastModified: Date.now() }
                );

                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                input.files = transfer.files;

                // Never post the canvas as a base64 hidden field.
                resolve(true);
            }, 'image/webp', 0.88);
        });

        const saveCropToPreview = async () => {
            if (!image) return;

            const saved = await replaceInputWithCrop();
            if (!saved) return;

            let previewImage = preview.querySelector('[data-media-preview-image]');

            if (!previewImage) {
                previewImage = document.createElement('img');
                previewImage.setAttribute('data-media-preview-image', '');
                preview.appendChild(previewImage);
            }

            previewImage.src = URL.createObjectURL(input.files[0]);
            preview.querySelector('.admin-media-field__empty')?.remove();
            fileName.textContent = input.files[0].name;
            editor.hidden = true;
        };

        apply.addEventListener('click', saveCropToPreview);

        cancel?.addEventListener('click', () => {
            editor.hidden = true;
            input.value = '';
        });

        field.closest('form')?.addEventListener('submit', async (event) => {
            if (image && input.files?.length && editor.hidden === false) {
                event.preventDefault();
                const saved = await replaceInputWithCrop();
                if (saved) {
                    editor.hidden = true;
                    field.closest('form').requestSubmit();
                }
            }
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-attributes-editor]').forEach((editor) => {
        const rows = editor.querySelector('[data-attribute-rows]');
        const addButton = editor.querySelector('[data-add-attribute]');
        const output = editor.querySelector('[data-attributes-json]');

        if (!rows || !addButton || !output) return;

        let initial = [];

        try {
            initial = JSON.parse(editor.dataset.initialAttributes || '[]');
        } catch {
            initial = [];
        }

        const addRow = (key = '', value = '') => {
            const row = document.createElement('div');
            row.className = 'admin-attribute-row';

            row.innerHTML = `
                <input type="text" data-attribute-key placeholder="نام ویژگی" value="${escapeHtml(key)}">
                <input type="text" data-attribute-value placeholder="مقدار؛ چند مورد را با ، جدا کن" value="${escapeHtml(value)}">
                <button type="button" class="admin-attribute-row__remove" aria-label="حذف ویژگی">×</button>
            `;

            row.querySelector('.admin-attribute-row__remove')?.addEventListener('click', () => {
                row.remove();
                sync();
            });

            row.querySelectorAll('input').forEach((input) => {
                input.addEventListener('input', sync);
            });

            rows.appendChild(row);
        };

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const sync = () => {
            const result = {};

            rows.querySelectorAll('.admin-attribute-row').forEach((row) => {
                const key = row.querySelector('[data-attribute-key]')?.value.trim();
                const rawValue = row.querySelector('[data-attribute-value]')?.value.trim();

                if (!key || !rawValue) return;

                const values = rawValue
                    .split(/[,،]/)
                    .map((item) => item.trim())
                    .filter(Boolean);

                result[key] = values.length > 1 ? values : (values[0] ?? '');
            });

            output.value = Object.keys(result).length
                ? JSON.stringify(result)
                : '';
        };

        initial.forEach((item) => addRow(item.key, item.value));

        if (!initial.length) {
            addRow();
        }

        addButton.addEventListener('click', () => addRow());
        sync();
    });
});


document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-product-index]').forEach((page) => {
        const dialog = page.querySelector('[data-product-preview-modal]');
        const image = dialog?.querySelector('[data-preview-image]');
        const title = dialog?.querySelector('[data-preview-name]');
        const closeButton = dialog?.querySelector('[data-preview-close]');

        if (!dialog || !image || !title) return;

        const close = () => {
            if (dialog.open) dialog.close();
            image.removeAttribute('src');
            image.alt = '';
            title.textContent = '';
        };

        page.querySelectorAll('[data-preview-open]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const url = trigger.dataset.previewUrl;
                const name = trigger.dataset.previewName || '';

                if (!url) return;

                image.src = url;
                image.alt = name;
                title.textContent = name;

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                }
            });
        });

        closeButton?.addEventListener('click', close);

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                close();
            }
        });

        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            close();
        });

        dialog.addEventListener('close', () => {
            image.removeAttribute('src');
            image.alt = '';
            title.textContent = '';
        });
    });
});

/* =========================================================
   ADMIN / LOCALIZED FORM INPUTS
========================================================= */
(() => {
    const MONEY_NAMES = new Set([
        'price',
        'sale_price',
        'amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
    ]);

    const faNumber = new Intl.NumberFormat('fa-IR');

    const normalizeDigits = (value) => String(value ?? '')
        .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
        .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)));

    const rawMoney = (value) => normalizeDigits(value)
        .replace(/[٬,،\s]/g, '')
        .replace(/[^0-9.-]/g, '');

    const formatMoney = (value) => {
        const raw = rawMoney(value);
        if (!raw || raw === '-') return '';
        const number = Number(raw);
        if (!Number.isFinite(number)) return '';
        return faNumber.format(Math.max(0, Math.round(number)));
    };

    const isMoneyInput = (input) => {
        const name = input.getAttribute('name') || '';
        return input.hasAttribute('data-money-input') || MONEY_NAMES.has(name);
    };

    const enhanceMoneyInput = (input) => {
        if (input.dataset.moneyReady === '1') return;
        input.dataset.moneyReady = '1';

        const originalName = input.name;
        const wasRequired = input.required;

        input.type = 'hidden';
        input.required = false;

        const visible = document.createElement('input');
        visible.type = 'text';
        visible.className = 'admin-money-input';
        visible.inputMode = 'numeric';
        visible.autocomplete = 'off';
        visible.name = originalName + '_display';
        visible.value = formatMoney(input.value);
        visible.placeholder = 'مثلاً ۱٬۵۰۰٬۰۰۰';
        visible.dir = 'ltr';
        visible.required = wasRequired;

        input.parentNode.insertBefore(visible, input);

        const sync = () => {
            input.value = rawMoney(visible.value);
            visible.value = formatMoney(input.value);
        };

        visible.addEventListener('input', () => {
            input.value = rawMoney(visible.value);
        });

        visible.addEventListener('blur', sync);
        input.closest('form')?.addEventListener('submit', sync);
    };

    const gregorianToJalali = (gy, gm, gd) => {
        const gdm = [0,31,59,90,120,151,181,212,243,273,304,334];
        let jy;

        if (gy > 1600) {
            jy = 979;
            gy -= 1600;
        } else {
            jy = 0;
            gy -= 621;
        }

        const gy2 = gm > 2 ? gy + 1 : gy;
        let days =
            (365 * gy) +
            Math.floor((gy2 + 3) / 4) -
            Math.floor((gy2 + 99) / 100) +
            Math.floor((gy2 + 399) / 400) -
            80 +
            gd +
            gdm[gm - 1];

        jy += 33 * Math.floor(days / 12053);
        days %= 12053;
        jy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            jy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        const jm = days < 186
            ? 1 + Math.floor(days / 31)
            : 7 + Math.floor((days - 186) / 30);

        const jd = 1 + (days < 186 ? days % 31 : (days - 186) % 30);
        return [jy, jm, jd];
    };

    const jalaliToGregorian = (jy, jm, jd) => {
        jy += 1597;

        let days =
            -355668 +
            (365 * jy) +
            Math.floor(jy / 33) * 8 +
            Math.floor(((jy % 33) + 3) / 4) +
            jd +
            (jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);

        let gy = 400 * Math.floor(days / 146097);
        days %= 146097;

        if (days > 36524) {
            gy += 100 * Math.floor(--days / 36524);
            days %= 36524;

            if (days >= 365) {
                days++;
            }
        }

        gy += 4 * Math.floor(days / 1461);
        days %= 1461;

        if (days > 365) {
            gy += Math.floor((days - 1) / 365);
            days = (days - 1) % 365;
        }

        const gd = days + 1;
        const leap = (gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0;
        const monthDays = [0,31,leap ? 29 : 28,31,30,31,30,31,31,30,31,30,31];

        let month = 1;
        let day = gd;

        while (day > monthDays[month]) {
            day -= monthDays[month];
            month++;
        }

        return [gy, month, day];
    };

    const formatJalali = (iso) => {
        if (!iso) return '';
        const match = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!match) return '';

        const [jy, jm, jd] = gregorianToJalali(
            Number(match[1]),
            Number(match[2]),
            Number(match[3])
        );

        return jy + '/' + String(jm).padStart(2, '0') + '/' + String(jd).padStart(2, '0');
    };

    const jalaliToIso = (value) => {
        const normalized = normalizeDigits(value).replace(/-/g, '/');
        const match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);

        if (!match) return '';

        const jy = Number(match[1]);
        const jm = Number(match[2]);
        const jd = Number(match[3]);

        if (jm < 1 || jm > 12 || jd < 1 || jd > 31) {
            return '';
        }

        const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);

        return gy + '-' + String(gm).padStart(2, '0') + '-' + String(gd).padStart(2, '0');
    };

    const enhanceDateInput = (input) => {
        if (input.dataset.jalaliReady === '1') return;
        input.dataset.jalaliReady = '1';

        const wasRequired = input.required;
        const originalValue = input.value;

        input.type = 'hidden';
        input.required = false;

        const visible = document.createElement('input');
        visible.type = 'text';
        visible.className = 'admin-jalali-input';
        visible.inputMode = 'numeric';
        visible.autocomplete = 'off';
        visible.name = input.name + '_jalali';
        visible.placeholder = '۱۴۰۵/۰۷/۰۶';
        visible.value = formatJalali(originalValue);
        visible.dir = 'ltr';
        visible.required = wasRequired;

        const hint = document.createElement('small');
        hint.className = 'admin-help';
        hint.textContent = 'تاریخ را شمسی وارد کن؛ سیستم قبل از ذخیره آن را به فرمت استاندارد تبدیل می‌کند.';

        input.parentNode.insertBefore(visible, input);
        visible.insertAdjacentElement('afterend', hint);

        const sync = () => {
            const iso = jalaliToIso(visible.value);

            if (iso) {
                input.value = iso;
                visible.value = formatJalali(iso);
                visible.setCustomValidity('');
            } else if (visible.value.trim() !== '') {
                visible.setCustomValidity('تاریخ شمسی معتبر وارد کنید.');
            }
        };

        visible.addEventListener('blur', sync);
        visible.addEventListener('change', sync);
        input.closest('form')?.addEventListener('submit', (event) => {
            sync();

            if (wasRequired && !input.value) {
                event.preventDefault();
                visible.setCustomValidity('تاریخ شمسی معتبر وارد کنید.');
                visible.reportValidity();
            }
        });
    };

    const formatLocalDates = () => {
        document.querySelectorAll('[data-admin-date]').forEach((element) => {
            const iso = element.dataset.adminDate;
            if (!iso) return;

            const date = new Date(iso);
            if (Number.isNaN(date.getTime())) return;

            const dateOnly = element.dataset.adminDateFormat === 'day';

            element.textContent = new Intl.DateTimeFormat(
                'fa-IR-u-ca-persian',
                dateOnly
                    ? {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                    }
                    : {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                    }
            ).format(date);
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.admin-main input').forEach((input) => {
            if (input instanceof HTMLInputElement && isMoneyInput(input)) {
                enhanceMoneyInput(input);
            }
        });

        document.querySelectorAll('.admin-main input[type="date"]').forEach(enhanceDateInput);
        formatLocalDates();
    });
})();


/* =========================================================
   PRODUCT MEDIA MANAGER
========================================================= */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-product-media-manager]').forEach((manager) => {
        const input = manager.querySelector('[data-media-upload-input]');
        const dropzone = manager.querySelector('[data-media-dropzone]');
        const preview = manager.querySelector('[data-media-upload-preview]');
        const count = manager.querySelector('[data-media-upload-count]');
        const submit = manager.querySelector('[data-media-upload-submit]');
        const sortable = manager.querySelector('[data-media-sortable]');

        const renderFiles = (files) => {
            if (!input || !preview || !submit) return;

            preview.innerHTML = '';
            const validFiles = [...files].filter((file) => file.type.startsWith('image/'));

            validFiles.forEach((file) => {
                const item = document.createElement('div');
                item.className = 'admin-media-upload-preview__item';

                const image = document.createElement('img');
                image.alt = file.name;
                image.src = URL.createObjectURL(file);

                const name = document.createElement('span');
                name.textContent = file.name;

                item.append(image, name);
                preview.appendChild(item);
            });

            if (count) {
                count.textContent = validFiles.length
                    ? `${validFiles.length} تصویر انتخاب شده`
                    : '';
            }

            submit.disabled = validFiles.length === 0;
        };

        input?.addEventListener('change', () => renderFiles(input.files));

        ['dragenter', 'dragover'].forEach((eventName) => {
            dropzone?.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            dropzone?.addEventListener(eventName, (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone?.addEventListener('drop', (event) => {
            const files = event.dataTransfer?.files;
            if (!files?.length || !input) return;

            const transfer = new DataTransfer();
            [...files].slice(0, 12).forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            renderFiles(input.files);
        });

        let dragged = null;

        sortable?.querySelectorAll('[data-media-id]').forEach((item) => {
            item.addEventListener('dragstart', () => {
                dragged = item;
                item.classList.add('is-dragging');
            });

            item.addEventListener('dragend', async () => {
                item.classList.remove('is-dragging');
                if (!dragged || !sortable) return;

                const ids = [...sortable.querySelectorAll('[data-media-id]')]
                    .map((node) => Number(node.dataset.mediaId))
                    .filter(Boolean);

                try {
                    const response = await fetch(
                        '{{ route('admin.products.media.reorder', $product) }}',
                        {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ media: ids }),
                        }
                    );

                    if (!response.ok) {
                        throw new Error('reorder failed');
                    }
                } catch {
                    window.location.reload();
                }

                dragged = null;
            });

            item.addEventListener('dragover', (event) => {
                event.preventDefault();
                if (!dragged || dragged === item) return;

                const rect = item.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;

                if (after) {
                    item.after(dragged);
                } else {
                    item.before(dragged);
                }
            });
        });
    });
});
