document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const sidebar = document.getElementById('adminSidebar') || document.querySelector('[data-admin-sidebar]');
    const menu = document.getElementById('adminMenuToggle') || document.querySelector('[data-admin-menu]');
    const overlay = document.getElementById('adminOverlay');

    if (!menu || !sidebar) return;

    const closeMenu = () => {
        body.classList.remove('admin-menu-open');
        sidebar.classList.remove('is-open');
        menu.setAttribute('aria-expanded', 'false');
    };

    const openMenu = () => {
        body.classList.add('admin-menu-open');
        sidebar.classList.add('is-open');
        menu.setAttribute('aria-expanded', 'true');
    };

    menu.addEventListener('click', () => {
        body.classList.contains('admin-menu-open') ? closeMenu() : openMenu();
    });

    overlay?.addEventListener('click', closeMenu);

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 960) closeMenu();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 960) closeMenu();
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
