@php
    $brand = $brand ?? null;
    $logo = $brand?->logoMedia;
@endphp

<div class="admin-card">

    <div class="admin-card-header">

        <div>
            <h2 class="admin-card-title">
                اطلاعات برند
            </h2>

            <p class="admin-card-description">
                اطلاعات اصلی برند
            </p>
        </div>

    </div>

    <div class="admin-form-grid">

        <div class="admin-field">

            <label for="name">
                نام برند *
            </label>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $brand?->name) }}"
                required
            >

        </div>

        <div class="admin-field">

            <label for="slug">
                Slug *
            </label>

            <input
                id="slug"
                type="text"
                name="slug"
                value="{{ old('slug', $brand?->slug) }}"
                dir="ltr"
                required
            >

        </div>

        <div class="admin-field admin-field-full">

            <label for="description">
                توضیحات
            </label>

            <textarea
                id="description"
                name="description"
                rows="7"
            >{{ old('description', $brand?->description) }}</textarea>

        </div>

        <div class="admin-field admin-field-full">

            <label class="admin-checkbox">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $brand?->is_active ?? true))
                >

                <span>
                    برند فعال باشد
                </span>

            </label>

        </div>

    </div>

</div>


<div class="admin-card admin-form-section">

    <div class="admin-card-header">

        <div>
            <h2 class="admin-card-title">
                لوگوی برند
            </h2>
        </div>

    </div>

    @if($logo?->url)

        <div class="admin-current-image">

            <img
                src="{{ $logo->url }}"
                alt="{{ $brand->name }}"
            >

            <div>

                <strong>
                    لوگوی فعلی
                </strong>

                <p class="admin-muted">
                    برای جایگزینی، تصویر جدید انتخاب کنید.
                </p>

            </div>

        </div>

    @endif

    <div class="admin-field">

        <label for="logo_file">
            لوگو
        </label>

        <input
            id="logo_file"
            type="file"
            name="logo_file"
            accept="image/jpeg,image/png,image/webp"
        >

        <small class="admin-help">
            فرمت‌های JPG، PNG و WebP
        </small>

    </div>

</div>
