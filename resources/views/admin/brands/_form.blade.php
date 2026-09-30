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
            <h2 class="admin-card-title">لوگوی برند</h2>
            <p class="admin-card-description">
                تصویر را انتخاب کن و قبل از ذخیره، کادر دقیق نمایش لوگو را مشخص کن.
            </p>
        </div>
    </div>

    <div class="admin-card-body">
        @include('admin.components.media-picker', [
            'name' => 'logo_file',
            'id' => 'logo_file',
            'label' => 'انتخاب لوگو',
            'help' => 'لوگو را بکش و زوم کن تا دقیقاً همان قسمت موردنظر در فروشگاه نمایش داده شود.',
            'currentUrl' => $logo?->url,
            'currentAlt' => $brand->name,
            'ratio' => '1:1',
        ])
        @error('logo_file')
            <small class="admin-help" style="color:var(--admin-danger);">{{ $message }}</small>
        @enderror
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


