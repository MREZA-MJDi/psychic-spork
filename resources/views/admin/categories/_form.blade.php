@php
    $category = $category ?? new \App\Models\Category();
    $cover = $category->coverMedia;
@endphp


{{-- =========================================================
    BASIC INFORMATION
========================================================= --}}

<div class="admin-card">

    <div class="admin-card-header">

        <div>

            <h2 class="admin-card-title">
                اطلاعات دسته‌بندی
            </h2>

            <p class="admin-card-description">
                اطلاعات اصلی دسته‌بندی را وارد کنید.
            </p>

        </div>

    </div>


    <div class="admin-card-body">

        <div class="admin-form-grid">

            {{-- NAME --}}

            <div class="admin-field">

                <label for="name">
                    نام دسته‌بندی *
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $category->name) }}"
                    placeholder="مثلاً لباس زیر"
                    autocomplete="off"
                    required
                >

                @error('name')
                <small class="admin-help" style="color:var(--admin-danger);">
                    {{ $message }}
                </small>
                @enderror

            </div>


            {{-- SLUG --}}

            <div class="admin-field">

                <label for="slug">
                    شناسه دسته‌بندی
                </label>

                <div style="display:flex; gap:8px; align-items:stretch;">

                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        value="{{ old('slug', $category->slug) }}"
                        dir="ltr"
                        placeholder="خودکار ساخته می‌شود"
                        autocomplete="off"
                        style="flex:1;"
                    >

                    <button
                        type="button"
                        id="generate-category-slug"
                        class="admin-btn admin-btn--ghost"
                        style="white-space:nowrap;"
                    >
                        خودکار
                    </button>

                </div>

                <small class="admin-help">
                    در صورت خالی بودن، از نام دسته‌بندی ساخته می‌شود.
                </small>

                @error('slug')
                <small class="admin-help" style="color:var(--admin-danger);">
                    {{ $message }}
                </small>
                @enderror

            </div>


            {{-- PARENT --}}

            <div class="admin-field">

                <label for="parent_id">
                    دسته‌بندی والد
                </label>

                <select id="parent_id" name="parent_id">

                    <option value="">
                        بدون والد — دسته اصلی
                    </option>

                    @foreach($parentCategories as $parent)

                        <option
                            value="{{ $parent->id }}"
                            @selected(
                            (string) old(
                        'parent_id',
                        $category->parent_id
                        ) === (string) $parent->id
                        )
                        >
                        {{ $parent->name }}
                        </option>

                    @endforeach

                </select>

                <small class="admin-help">
                    برای ساخت یک دسته اصلی، این گزینه را خالی بگذارید.
                </small>

                @error('parent_id')
                <small class="admin-help" style="color:var(--admin-danger);">
                    {{ $message }}
                </small>
                @enderror

            </div>


            {{-- SORT ORDER --}}

            <div class="admin-field">

                <label for="sort_order">
                    ترتیب نمایش
                </label>

                <input
                    id="sort_order"
                    type="number"
                    name="sort_order"
                    min="0"
                    max="9999"
                    value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                    inputmode="numeric"
                >

                <small class="admin-help">
                    عدد کمتر، معمولاً زودتر نمایش داده می‌شود.
                </small>

                @error('sort_order')
                <small class="admin-help" style="color:var(--admin-danger);">
                    {{ $message }}
                </small>
                @enderror

            </div>


            {{-- DESCRIPTION --}}

            <div class="admin-field admin-field-full">

                <label for="description">
                    توضیحات
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="توضیح کوتاهی درباره این دسته‌بندی بنویسید..."
                >{{ old('description', $category->description) }}</textarea>

                <small class="admin-help">
                    این بخش اختیاری است.
                </small>

                @error('description')
                <small class="admin-help" style="color:var(--admin-danger);">
                    {{ $message }}
                </small>
                @enderror

            </div>


            {{-- ACTIVE --}}

            <div class="admin-field admin-field-full">

                <label class="admin-checkbox">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(
                        old(
                    'is_active',
                    $category->exists
                    ? $category->is_active
                    : true
                    )
                    )
                    >

                    <span>
                        دسته‌بندی فعال باشد
                    </span>

                </label>

                <small class="admin-help">
                    دسته‌بندی فعال در بخش‌های مربوط به فروشگاه قابل نمایش خواهد بود.
                </small>

                @error('is_active')
                <small class="admin-help" style="color:var(--admin-danger);">
                    {{ $message }}
                </small>
                @enderror

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    CATEGORY IMAGE
========================================================= --}}

<div class="admin-card admin-form-section">

    <div class="admin-card-header">

        <div>

            <h2 class="admin-card-title">
                تصویر دسته‌بندی
            </h2>

            <p class="admin-card-description">
                تصویر مناسب برای نمایش دسته‌بندی انتخاب کنید.
            </p>

        </div>

    </div>


    <div class="admin-card-body">

        {{-- CURRENT IMAGE --}}

        @if($cover?->url)

            <div
                class="admin-current-image"
                id="category-current-image"
            >

                <img
                    src="{{ $cover->url }}"
                    alt="{{ $category->name }}"
                >

                <div>

                    <strong>
                        تصویر فعلی
                    </strong>

                    <p class="admin-muted">
                        با انتخاب تصویر جدید، تصویر فعلی جایگزین می‌شود.
                    </p>

                </div>

            </div>

        @endif


        {{-- NEW IMAGE PREVIEW --}}

        <div
            class="admin-current-image"
            id="category-image-preview"
            hidden
            style="margin-bottom:16px;"
        >

            <img
                id="category-image-preview-image"
                src=""
                alt="پیش‌نمایش تصویر دسته‌بندی"
            >

            <div>

                <strong>
                    پیش‌نمایش تصویر جدید
                </strong>

                <p class="admin-muted">
                    این تصویر قبل از ذخیره فقط برای پیش‌نمایش است.
                </p>

            </div>

        </div>


        {{-- FILE INPUT --}}

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
                JPG، PNG یا WebP — حداکثر ۲ مگابایت
            </small>

            @error('image_file')
            <small class="admin-help" style="color:var(--admin-danger);">
                {{ $message }}
            </small>
            @enderror

        </div>

    </div>

</div>


{{-- =========================================================
    FORM ACTIONS
========================================================= --}}

<div class="admin-form-actions">

    <a
        href="{{ route('admin.categories.index') }}"
        class="admin-btn admin-btn--ghost"
    >
        انصراف
    </a>

    <button
        type="submit"
        class="admin-btn admin-btn--secondary"
    >
        {{ $category->exists ? 'ذخیره تغییرات' : 'ایجاد دسته‌بندی' }}
    </button>

</div>


{{-- =========================================================
    CATEGORY FORM JS
========================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const slugButton = document.getElementById('generate-category-slug');

        const imageInput = document.getElementById('image_file');
        const preview = document.getElementById('category-image-preview');
        const previewImage = document.getElementById('category-image-preview-image');


        /*
         * Persian / Arabic → Latin
         */

        const persianMap = {
            'ا': 'a',
            'آ': 'a',
            'ب': 'b',
            'پ': 'p',
            'ت': 't',
            'ث': 's',
            'ج': 'j',
            'چ': 'ch',
            'ح': 'h',
            'خ': 'kh',
            'د': 'd',
            'ذ': 'z',
            'ر': 'r',
            'ز': 'z',
            'ژ': 'zh',
            'س': 's',
            'ش': 'sh',
            'ص': 's',
            'ض': 'z',
            'ط': 't',
            'ظ': 'z',
            'ع': 'a',
            'غ': 'gh',
            'ف': 'f',
            'ق': 'gh',
            'ک': 'k',
            'گ': 'g',
            'ل': 'l',
            'م': 'm',
            'ن': 'n',
            'و': 'v',
            'ه': 'h',
            'ی': 'y',
            'ي': 'y',
            'ئ': 'y',
            'ة': 'h',
            'ؤ': 'v',
            'ء': ''
        };


        const generateSlug = (value) => {

            let text = String(value || '').trim().toLowerCase();

            text = text
                .split('')
                .map(char => persianMap[char] ?? char)
                .join('');

            return text
                .replace(/['’"`]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                .replace(/-+/g, '-');

        };


        const syncSlug = () => {

            if (!nameInput || !slugInput) {
                return;
            }

            slugInput.value = generateSlug(nameInput.value);

        };


        slugButton?.addEventListener('click', () => {

            syncSlug();

            if (slugInput) {
                slugInput.dataset.autoGenerated = 'true';
            }

        });


        nameInput?.addEventListener('input', () => {

            if (
                !slugInput?.value ||
                slugInput?.dataset.autoGenerated === 'true'
            ) {
                syncSlug();

                if (slugInput) {
                    slugInput.dataset.autoGenerated = 'true';
                }
            }

        });


        slugInput?.addEventListener('input', () => {

            slugInput.dataset.autoGenerated = 'false';

        });


        /*
         * Image preview
         */

        imageInput?.addEventListener('change', () => {

            const file = imageInput.files?.[0];

            if (!file) {

                preview.hidden = true;
                previewImage.removeAttribute('src');

                return;

            }


            if (!file.type.startsWith('image/')) {

                imageInput.value = '';
                preview.hidden = true;
                previewImage.removeAttribute('src');

                return;

            }


            const reader = new FileReader();


            reader.onload = (event) => {

                previewImage.src = String(
                    event.target?.result || ''
                );

                preview.hidden = false;

            };


            reader.readAsDataURL(file);

        });


        /*
         * Existing slug should not be overwritten automatically
         */

        if (
            slugInput?.value &&
            nameInput?.value
        ) {
            slugInput.dataset.autoGenerated = 'false';
        }

    });
</script>
