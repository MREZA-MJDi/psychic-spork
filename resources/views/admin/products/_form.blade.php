@php
    $product = $product ?? null;
    $variant = $variant ?? null;

    $existingAttributes = is_array($product?->attributes)
        ? $product->attributes
        : [];

    if (old('attributes_json') !== null) {
        $decodedOldAttributes = json_decode(
            old('attributes_json'),
            true
        );

        if (is_array($decodedOldAttributes)) {
            $existingAttributes = $decodedOldAttributes;
        }
    }

    if (empty($existingAttributes)) {
        $existingAttributes = [
            '' => '',
        ];
    }

    $galleryImage = $product?->galleryMedia?->first();

    $existingColorCode = $variant?->color_code;

    $defaultCategoryId = old(
        'category_id',
        $product?->category_id ?? $categories->first()?->id
    );

    $imagePreviewUrl = $galleryImage?->url;
@endphp


<div class="admin-form-grid">

    {{-- =========================================================
        MAIN INFORMATION
    ========================================================== --}}

    <section class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    اطلاعات اصلی
                </h2>

                <p class="admin-card-description">
                    فقط اطلاعات ساده محصول را وارد کنید؛ بخش‌های فنی خودکار مدیریت می‌شوند.
                </p>

            </div>

        </div>


        <div class="admin-form-grid">

            {{-- Name --}}

            <div class="admin-field">

                <label for="name">
                    نام محصول
                    <span class="required">*</span>
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $product?->name) }}"
                    maxlength="180"
                    placeholder="مثلاً لباس خواب ساتن"
                    required
                >

            </div>


            {{-- Slug --}}

            <div class="admin-field">

                <label for="slug">
                    آدرس محصول
                </label>

                <div style="display:flex;gap:8px;">

                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        value="{{ old('slug', $product?->slug) }}"
                        dir="ltr"
                        maxlength="200"
                        placeholder="خودکار ساخته می‌شود"
                    >

                    <button
                        type="button"
                        id="generate-slug"
                        class="admin-btn admin-btn--ghost admin-btn--sm"
                    >
                        خودکار
                    </button>

                </div>

                <small class="admin-help">
                    لازم نیست خودتان چیزی وارد کنید.
                </small>

            </div>


            {{-- Category --}}

            <div class="admin-field">

                <label for="category_id">
                    دسته‌بندی
                    <span class="required">*</span>
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    required
                >

                    <option value="">
                        انتخاب دسته‌بندی
                    </option>

                    @foreach($categories as $category)

                        <option
                            value="{{ $category->id }}"
                            @selected(
                            (string) $defaultCategoryId
                            ===
                            (string) $category->id
                            )
                            >
                            {{ $category->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Brand --}}

            <div class="admin-field">

                <label for="brand_id">
                    برند
                </label>

                <select
                    id="brand_id"
                    name="brand_id"
                >

                    <option value="">
                        بدون برند
                    </option>

                    @foreach($brands as $brand)

                        <option
                            value="{{ $brand->id }}"
                            @selected(
                            (string) old(
                        'brand_id',
                        $product?->brand_id
                        )
                        ===
                        (string) $brand->id
                        )
                        >
                        {{ $brand->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Short description --}}

            <div class="admin-field admin-field-full">

                <label for="short_description">
                    توضیح کوتاه
                </label>

                <textarea
                    id="short_description"
                    name="short_description"
                    rows="3"
                    maxlength="1000"
                    placeholder="یک توضیح کوتاه درباره محصول..."
                >{{ old('short_description', $product?->short_description) }}</textarea>

            </div>


            {{-- Description --}}

            <div class="admin-field admin-field-full">

                <label for="description">
                    توضیحات محصول
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    maxlength="10000"
                    placeholder="توضیحات کامل محصول..."
                >{{ old('description', $product?->description) }}</textarea>

            </div>

        </div>

    </section>


    {{-- =========================================================
        STATUS
    ========================================================== --}}

    <section class="admin-card admin-form-section">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    وضعیت
                </h2>

                <p class="admin-card-description">
                    وضعیت نمایش محصول در فروشگاه
                </p>

            </div>

        </div>


        <div class="admin-checkbox-list">

            <label class="admin-checkbox">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(
                    old(
                'is_active',
                $product?->is_active ?? true
                )
                )
                >

                <span>
                    محصول فعال باشد
                </span>

            </label>


            <label class="admin-checkbox">

                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    @checked(
                    old(
                'is_featured',
                $product?->is_featured ?? false
                )
                )
                >

                <span>
                    محصول ویژه باشد
                </span>

            </label>

        </div>

    </section>


    {{-- =========================================================
        SORT
    ========================================================== --}}

    <section class="admin-card admin-form-section">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    ترتیب نمایش
                </h2>

                <p class="admin-card-description">
                    اختیاری
                </p>

            </div>

        </div>


        <div class="admin-field">

            <label for="sort_order">
                ترتیب
            </label>

            <input
                id="sort_order"
                type="number"
                name="sort_order"
                min="0"
                max="9999"
                value="{{ old('sort_order', $product?->sort_order ?? 0) }}"
            >

        </div>

    </section>


    {{-- =========================================================
        VARIANT
    ========================================================== --}}

    <section class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    قیمت و موجودی
                </h2>

                <p class="admin-card-description">
                    اطلاعاتی که برای فروش محصول لازم است.
                </p>

            </div>

        </div>


        <div class="admin-form-grid">

            {{-- SKU --}}

            <div class="admin-field">

                <label for="sku">
                    کد کالا
                </label>

                <div style="display:flex;gap:8px;">

                    <input
                        id="sku"
                        type="text"
                        name="sku"
                        value="{{ old('sku', $variant?->sku) }}"
                        dir="ltr"
                        maxlength="120"
                        placeholder="خودکار ساخته می‌شود"
                    >

                    <button
                        type="button"
                        id="generate-sku"
                        class="admin-btn admin-btn--ghost admin-btn--sm"
                    >
                        خودکار
                    </button>

                </div>

                <small class="admin-help">
                    لازم نیست کاربر کد کالا بلد باشد.
                </small>

            </div>


            {{-- Size --}}

            <div class="admin-field">

                <label for="size">
                    سایز
                </label>

                <input
                    id="size"
                    type="text"
                    name="size"
                    value="{{ old('size', $variant?->size) }}"
                    maxlength="80"
                    placeholder="مثلاً M یا 38"
                >

            </div>


            {{-- Color --}}

            <div class="admin-field">

                <label for="color">
                    رنگ
                </label>

                <input
                    id="color"
                    type="text"
                    name="color"
                    value="{{ old('color', $variant?->color) }}"
                    maxlength="80"
                    placeholder="مثلاً مشکی"
                >

            </div>


            {{-- Color picker --}}

            <div class="admin-field">

                <label for="color_picker">
                    انتخاب رنگ
                </label>

                <div style="display:flex;align-items:center;gap:10px;">

                    <input
                        id="color_picker"
                        type="color"
                        value="{{ $existingColorCode ?: '#000000' }}"
                        style="
                            width:54px;
                            min-height:44px;
                            padding:4px;
                            cursor:pointer;
                        "
                    >

                    <input
                        id="color_code"
                        type="text"
                        name="color_code"
                        value="{{ old('color_code', $existingColorCode) }}"
                        dir="ltr"
                        maxlength="20"
                        placeholder="اختیاری"
                    >

                </div>

                <small class="admin-help">
                    لازم نیست کد رنگ را بدانید؛ فقط رنگ را انتخاب کنید.
                </small>

            </div>


            {{-- Price --}}

            <div class="admin-field">

                <label for="price">
                    قیمت اصلی
                    <span class="required">*</span>
                </label>

                <input
                    id="price"
                    type="number"
                    name="price"
                    min="0"
                    step="1"
                    value="{{ old('price', $variant?->price ?? 0) }}"
                    required
                >

                <small class="admin-help">
                    مبلغ به تومان وارد شود.
                </small>

            </div>


            {{-- Sale price --}}

            <div class="admin-field">

                <label for="sale_price">
                    قیمت تخفیفی
                </label>

                <input
                    id="sale_price"
                    type="number"
                    name="sale_price"
                    min="0"
                    step="1"
                    value="{{ old('sale_price', $variant?->sale_price) }}"
                    placeholder="اختیاری"
                >

                <small class="admin-help">
                    اگر تخفیف ندارید خالی بگذارید. اگر بیشتر از قیمت اصلی باشد، نادیده گرفته می‌شود.
                </small>

            </div>


            {{-- Stock --}}

            <div class="admin-field">

                <label for="stock">
                    موجودی
                    <span class="required">*</span>
                </label>

                <input
                    id="stock"
                    type="number"
                    name="stock"
                    min="0"
                    value="{{ old('stock', $variant?->stock ?? 0) }}"
                    required
                >

            </div>


            {{-- Low stock --}}

            <div class="admin-field">

                <label for="low_stock_threshold">
                    هشدار موجودی
                </label>

                <input
                    id="low_stock_threshold"
                    type="number"
                    name="low_stock_threshold"
                    min="0"
                    value="{{ old(
                        'low_stock_threshold',
                        $variant?->low_stock_threshold ?? 5
                    ) }}"
                    required
                >

                <small class="admin-help">
                    وقتی موجودی به این عدد برسد، هشدار داده می‌شود.
                </small>

            </div>

        </div>

    </section>


    {{-- =========================================================
        ATTRIBUTES
    ========================================================== --}}

    <section class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    ویژگی‌های محصول
                </h2>

                <p class="admin-card-description">
                    ویژگی‌ها را مثل «جنس / ساتن» وارد کنید. نیازی به JSON نیست.
                </p>

            </div>


            <button
                type="button"
                id="add-attribute"
                class="admin-btn admin-btn--ghost admin-btn--sm"
            >
                + افزودن ویژگی
            </button>

        </div>


        <div
            id="attributes-list"
            class="admin-card-body"
        >

            @foreach($existingAttributes as $key => $value)

                <div
                    class="attribute-row"
                    style="
                        display:grid;
                        grid-template-columns:minmax(0,1fr) minmax(0,1fr) auto;
                        gap:10px;
                        margin-bottom:10px;
                    "
                >

                    <input
                        type="text"
                        class="attribute-key"
                        value="{{ $key }}"
                        placeholder="ویژگی، مثلاً جنس"
                    >

                    <input
                        type="text"
                        class="attribute-value"
                        value="{{ $value }}"
                        placeholder="مقدار، مثلاً ساتن"
                    >

                    <button
                        type="button"
                        class="admin-btn admin-btn--danger admin-btn--sm remove-attribute"
                    >
                        حذف
                    </button>

                </div>

            @endforeach

        </div>

        <input
            type="hidden"
            name="attributes_json"
            id="attributes_json"
            value="{{ old('attributes_json') }}"
        >

    </section>


    {{-- =========================================================
        IMAGE
    ========================================================== --}}

    <section class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">

            <div>

                <h2 class="admin-card-title">
                    تصویر محصول
                </h2>

                <p class="admin-card-description">
                    قبل از ذخیره می‌توانید تصویر انتخاب‌شده را ببینید.
                </p>

            </div>

        </div>


        <div style="padding:20px 22px 22px;">

            <div
                id="image-preview-wrap"
                @if(!$imagePreviewUrl) style="display:none;" @endif
            >

                <div class="admin-current-image">

                    <img
                        id="image-preview"
                        src="{{ $imagePreviewUrl ?: '' }}"
                        alt="{{ $product?->name ?: 'پیش‌نمایش تصویر' }}"
                    >

                    <div>

                        <strong>
                            پیش‌نمایش تصویر
                        </strong>

                        <p class="admin-muted">
                            این تصویر قبل از ذخیره به شما نمایش داده می‌شود.
                        </p>

                    </div>

                </div>

            </div>


            <div class="admin-field">

                <label for="image_file">
                    انتخاب تصویر
                </label>

                <input
                    id="image_file"
                    type="file"
                    name="image_file"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small class="admin-help">
                    JPG، PNG یا WebP — حداکثر 2MB
                </small>

            </div>

        </div>

    </section>

</div>


@once

    @push('scripts')

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /*
                |--------------------------------------------------------------------------
                | Helpers
                |--------------------------------------------------------------------------
                */

                const slugInput = document.getElementById('slug');
                const nameInput = document.getElementById('name');
                const skuInput = document.getElementById('sku');

                const slugButton = document.getElementById('generate-slug');
                const skuButton = document.getElementById('generate-sku');

                const colorPicker = document.getElementById('color_picker');
                const colorCode = document.getElementById('color_code');

                const priceInput = document.getElementById('price');
                const salePriceInput = document.getElementById('sale_price');

                const imageInput = document.getElementById('image_file');
                const imagePreview = document.getElementById('image-preview');
                const imagePreviewWrap = document.getElementById('image-preview-wrap');

                const attributesList = document.getElementById('attributes-list');
                const attributesJson = document.getElementById('attributes_json');
                const addAttributeButton = document.getElementById('add-attribute');


                /*
                |--------------------------------------------------------------------------
                | Slug
                |--------------------------------------------------------------------------
                */

                function generateSlug() {

                    const value = (nameInput?.value || '').trim();

                    if (!value || !slugInput) {
                        return;
                    }

                    let slug = value
                        .toLowerCase()
                        .replace(/[إأآا]/g, 'a')
                        .replace(/[ب]/g, 'b')
                        .replace(/[پ]/g, 'p')
                        .replace(/[ت]/g, 't')
                        .replace(/[ث]/g, 's')
                        .replace(/[ج]/g, 'j')
                        .replace(/[چ]/g, 'ch')
                        .replace(/[ح]/g, 'h')
                        .replace(/[خ]/g, 'kh')
                        .replace(/[د]/g, 'd')
                        .replace(/[ذ]/g, 'z')
                        .replace(/[ر]/g, 'r')
                        .replace(/[ز]/g, 'z')
                        .replace(/[ژ]/g, 'zh')
                        .replace(/[س]/g, 's')
                        .replace(/[ش]/g, 'sh')
                        .replace(/[ص]/g, 's')
                        .replace(/[ض]/g, 'z')
                        .replace(/[ط]/g, 't')
                        .replace(/[ظ]/g, 'z')
                        .replace(/[ع]/g, 'a')
                        .replace(/[غ]/g, 'gh')
                        .replace(/[ف]/g, 'f')
                        .replace(/[ق]/g, 'gh')
                        .replace(/[ک]/g, 'k')
                        .replace(/[گ]/g, 'g')
                        .replace(/[ل]/g, 'l')
                        .replace(/[م]/g, 'm')
                        .replace(/[ن]/g, 'n')
                        .replace(/[و]/g, 'v')
                        .replace(/[ه]/g, 'h')
                        .replace(/[ی]/g, 'y')
                        .replace(/[ء]/g, '')
                        .replace(/[ۀة]/g, 'e')
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');

                    if (!slug) {
                        slug = 'product';
                    }

                    slugInput.value = slug;
                }


                if (slugButton) {
                    slugButton.addEventListener('click', generateSlug);
                }


                /*
                |--------------------------------------------------------------------------
                | SKU
                |--------------------------------------------------------------------------
                */

                function generateSku() {

                    if (!skuInput) {
                        return;
                    }

                    const random = Math.random()
                        .toString(36)
                        .substring(2, 8)
                        .toUpperCase();

                    skuInput.value = 'JAN-' + random;
                }


                if (skuButton) {
                    skuButton.addEventListener('click', generateSku);
                }


                /*
                |--------------------------------------------------------------------------
                | First load defaults
                |--------------------------------------------------------------------------
                */

                if (skuInput && !skuInput.value.trim()) {
                    generateSku();
                }

                if (
                    slugInput &&
                    nameInput &&
                    !slugInput.value.trim() &&
                    nameInput.value.trim()
                ) {
                    generateSlug();
                }


                /*
                |--------------------------------------------------------------------------
                | Color picker
                |--------------------------------------------------------------------------
                */

                if (colorPicker && colorCode) {

                    colorPicker.addEventListener('input', function () {
                        colorCode.value = colorPicker.value;
                    });

                    if (colorCode.value) {
                        colorPicker.value = colorCode.value;
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Sale price helper
                |--------------------------------------------------------------------------
                */

                function normalizeSalePrice() {

                    if (!priceInput || !salePriceInput) {
                        return;
                    }

                    const price = Number(priceInput.value);
                    const salePrice = Number(salePriceInput.value);

                    if (
                        Number.isFinite(price) &&
                        Number.isFinite(salePrice) &&
                        salePrice >= price
                    ) {
                        salePriceInput.value = '';
                    }
                }

                if (priceInput) {
                    priceInput.addEventListener(
                        'input',
                        normalizeSalePrice
                    );
                }

                if (salePriceInput) {
                    salePriceInput.addEventListener(
                        'input',
                        normalizeSalePrice
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Image preview
                |--------------------------------------------------------------------------
                */

                if (imageInput) {

                    imageInput.addEventListener('change', function () {

                        const file = imageInput.files?.[0];

                        if (!file) {
                            return;
                        }

                        if (!file.type.startsWith('image/')) {
                            imageInput.value = '';
                            return;
                        }

                        const url = URL.createObjectURL(file);

                        imagePreview.src = url;
                        imagePreviewWrap.style.display = 'block';

                        imagePreview.onload = function () {
                            URL.revokeObjectURL(url);
                        };
                    });
                }


                /*
                |--------------------------------------------------------------------------
                | Attributes
                |--------------------------------------------------------------------------
                */

                function createAttributeRow(key = '', value = '') {

                    const row = document.createElement('div');

                    row.className = 'attribute-row';

                    row.style.display = 'grid';
                    row.style.gridTemplateColumns =
                        'minmax(0,1fr) minmax(0,1fr) auto';
                    row.style.gap = '10px';
                    row.style.marginBottom = '10px';

                    row.innerHTML = `
                        <input
                            type="text"
                            class="attribute-key"
                            value="${escapeHtml(key)}"
                            placeholder="ویژگی، مثلاً جنس"
                        >

                        <input
                            type="text"
                            class="attribute-value"
                            value="${escapeHtml(value)}"
                            placeholder="مقدار، مثلاً ساتن"
                        >

                        <button
                            type="button"
                            class="admin-btn admin-btn--danger admin-btn--sm remove-attribute"
                        >
                            حذف
                        </button>
                    `;

                    attributesList.appendChild(row);
                }


                function escapeHtml(value) {

                    const div = document.createElement('div');

                    div.textContent = value ?? '';

                    return div.innerHTML;
                }


                function syncAttributes() {

                    if (!attributesList || !attributesJson) {
                        return;
                    }

                    const result = {};

                    attributesList
                        .querySelectorAll('.attribute-row')
                        .forEach(function (row) {

                            const key =
                                row.querySelector('.attribute-key')
                                    ?.value
                                    .trim();

                            const value =
                                row.querySelector('.attribute-value')
                                    ?.value
                                    .trim();

                            if (key) {
                                result[key] = value;
                            }
                        });

                    attributesJson.value =
                        JSON.stringify(result);
                }


                if (addAttributeButton) {

                    addAttributeButton.addEventListener(
                        'click',
                        function () {

                            createAttributeRow();

                            syncAttributes();
                        }
                    );
                }


                attributesList?.addEventListener(
                    'click',
                    function (event) {

                        const button =
                            event.target.closest(
                                '.remove-attribute'
                            );

                        if (!button) {
                            return;
                        }

                        button
                            .closest('.attribute-row')
                            ?.remove();

                        syncAttributes();
                    }
                );


                attributesList?.addEventListener(
                    'input',
                    syncAttributes
                );


                /*
                |--------------------------------------------------------------------------
                | Before submit
                |--------------------------------------------------------------------------
                */

                const form =
                    attributesJson?.closest('form');

                form?.addEventListener(
                    'submit',
                    function () {

                        syncAttributes();

                        if (
                            salePriceInput &&
                            priceInput
                        ) {
                            normalizeSalePrice();
                        }

                    }
                );


                syncAttributes();
            });
        </script>

    @endpush

@endonce
