@php
    $brand = $brand ?? new \App\Models\Brand();
    $logo = $brand->logoMedia;
@endphp

{{-- =========================================================
    BASIC INFORMATION
========================================================= --}}

<div class="admin-card">

    <div class="admin-card-header">

        <div>

            <h2 class="admin-card-title">
                اطلاعات برند
            </h2>

            <p class="admin-card-description">
                نام و اطلاعات اصلی برند را وارد کنید.
            </p>

        </div>

    </div>


    <div class="admin-card-body">

        <div class="admin-form-grid">

            {{-- NAME --}}

            <div class="admin-field">

                <label for="name">
                    نام برند *
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $brand->name) }}"
                    placeholder="مثلاً نایک"
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
                    شناسه برند
                </label>

                <div style="display:flex; gap:8px; align-items:stretch;">

                    <input
                        id="slug"
                        type="text"
                        name="slug"
                        value="{{ old('slug', $brand->slug) }}"
                        dir="ltr"
                        placeholder="خودکار ساخته می‌شود"
                        autocomplete="off"
                        style="flex:1;"
                    >

                    <button
                        type="button"
                        id="generate-brand-slug"
                        class="admin-btn admin-btn--ghost"
                        style="white-space:nowrap;"
                    >
                        خودکار
                    </button>

                </div>

                <small class="admin-help">
                    برای برند جدید می‌توانید این بخش را خالی بگذارید.
                </small>

                @error('slug')
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
                    placeholder="توضیح کوتاهی درباره برند بنویسید..."
                >{{ old('description', $brand->description) }}</textarea>

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
                    $brand->exists
                    ? $brand->is_active
                    : true
                    )
                    )
                    >

                    <span>
                        برند فعال باشد
                    </span>

                </label>

                <small class="admin-help">
                    برند فعال در بخش‌های مربوط به فروشگاه نمایش داده می‌شود.
                </small>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
    LOGO
========================================================= --}}

<div class="admin-card admin-form-section">

    <div class="admin-card-header">

        <div>

            <h2 class="admin-card-title">
                لوگوی برند
            </h2>

            <p class="admin-card-description">
                لوگوی برند را انتخاب کنید.
            </p>

        </div>

    </div>


    <div class="admin-card-body">

        {{-- CURRENT LOGO --}}

        @if($logo?->url)

            <div
                class="admin-current-image"
                id="brand-current-logo"
            >

                <img
                    src="{{ $logo->url }}"
                    alt="{{ $brand->name }}"
                >

                <div>

                    <strong>
                        لوگوی فعلی
                    </strong>

                    <p class="admin-muted">
                        با انتخاب تصویر جدید، لوگوی فعلی جایگزین می‌شود.
                    </p>

                </div>

            </div>

        @endif


        {{-- NEW LOGO PREVIEW --}}

        <div
            class="admin-current-image"
            id="brand-logo-preview"
            hidden
            style="margin-bottom:16px;"
        >

            <img
                id="brand-logo-preview-image"
                src=""
                alt="پیش‌نمایش لوگوی جدید"
            >

            <div>

                <strong>
                    پیش‌نمایش لوگوی جدید
                </strong>

                <p class="admin-muted">
                    این تصویر قبل از ذخیره فقط برای پیش‌نمایش است.
                </p>

            </div>

        </div>


        {{-- FILE INPUT --}}

        <div class="admin-field">

            <label for="logo_file">
                انتخاب لوگو
            </label>

            <input
                id="logo_file"
                type="file"
                name="logo_file"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="admin-help">
                JPG، PNG یا WebP — حداکثر ۲ مگابایت
            </small>

            @error('logo_file')
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
        href="{{ route('admin.brands.index') }}"
        class="admin-btn admin-btn--ghost"
    >
        انصراف
    </a>

    <button
        type="submit"
        class="admin-btn admin-btn--secondary"
    >
        {{ $brand->exists ? 'ذخیره تغییرات' : 'ایجاد برند' }}
    </button>

</div>


{{-- =========================================================
    BRAND FORM JS
========================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        const slugButton = document.getElementById('generate-brand-slug');

        const logoInput = document.getElementById('logo_file');
        const preview = document.getElementById('brand-logo-preview');
        const previewImage = document.getElementById('brand-logo-preview-image');


        /*
         * Persian / Arabic characters → Latin
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
            'ء': '',
            'ؤ': 'v'
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


        slugButton?.addEventListener('click', syncSlug);


        /*
         * Auto-generate slug only while the user has not manually
         * customized it.
         */

        nameInput?.addEventListener('input', () => {

            if (
                !slugInput.value ||
                slugInput.dataset.autoGenerated === 'true'
            ) {
                syncSlug();
                slugInput.dataset.autoGenerated = 'true';
            }

        });


        slugInput?.addEventListener('input', () => {

            slugInput.dataset.autoGenerated = 'false';

        });


        /*
         * Logo preview
         */

        logoInput?.addEventListener('change', () => {

            const file = logoInput.files?.[0];

            if (!file) {
                preview.hidden = true;
                previewImage.removeAttribute('src');
                return;
            }


            if (!file.type.startsWith('image/')) {

                logoInput.value = '';
                preview.hidden = true;
                previewImage.removeAttribute('src');

                return;

            }


            const reader = new FileReader();


            reader.onload = (event) => {

                previewImage.src = String(event.target?.result || '');
                preview.hidden = false;

            };


            reader.readAsDataURL(file);

        });


        /*
         * Initial slug state
         */

        if (
            slugInput?.value &&
            nameInput?.value
        ) {
            slugInput.dataset.autoGenerated = 'false';
        }

    });
</script>
