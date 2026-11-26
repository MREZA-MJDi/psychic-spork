@props([
    'name',
    'id' => null,
    'label' => 'انتخاب تصویر',
    'help' => 'تصویر را انتخاب کن، بعد کادر نمایش را با کشیدن و زوم تنظیم کن.',
    'currentUrl' => null,
    'currentAlt' => '',
    'ratio' => '1:1',
    'categoryCard' => false,
    'categoryName' => '',
    'outputWidth' => null,
    'outputHeight' => null,
])

@php($inputId = $id ?: str_replace(['[', ']'], ['-', ''], $name))

<div
    class="admin-media-picker"
    data-media-picker
    data-media-ratio="{{ $ratio }}"
    @if($categoryCard) data-category-cover data-media-width="{{ $outputWidth }}" data-media-height="{{ $outputHeight }}" @endif
    style="--media-ratio: {{ str_replace(':', ' / ', $ratio) }};"
>
    <div class="admin-media-picker__stage">
        @if($categoryCard)<div class="janan-category-card janan-category-card--preview" style="--category-cover-ratio: {{ \App\Support\CategoryCover::CSS_RATIO }}">@endif
        <div class="{{ $categoryCard ? 'janan-category-card__frame' : 'admin-media-picker__preview' }}" data-media-preview>
            @if($currentUrl)
                <img
                    src="{{ $currentUrl }}"
                    alt="{{ $currentAlt }}"
                    data-media-current-preview
                >
            @else
                <div class="admin-media-picker__placeholder">
                    <span aria-hidden="true">＋</span>
                    <strong>هنوز تصویری انتخاب نشده</strong>
                    <small>برای شروع روی انتخاب تصویر بزن</small>
                </div>
            @endif
        </div>

        @if($categoryCard)
            <div class="janan-category-card__body">
                <div><h3 data-category-preview-name>{{ $categoryName ?: 'نام دسته‌بندی' }}</h3><span class="janan-category-card__count">پیش‌نمایش کارت فروشگاه</span></div>
                <span class="janan-category-card__arrow" aria-hidden="true">←</span>
            </div>
        </div>
        @endif
        <div class="admin-media-picker__meta">
            <strong data-media-file-name>{{ $currentUrl ? 'تصویر فعلی' : 'آماده انتخاب تصویر' }}</strong>
            <small>{{ $help }}</small>
            @if($categoryCard)
                <small>نسبت ثابت ۴:۳؛ خروجی ۹۶۰ × ۷۲۰ پیکسل. پیش‌نمایش با همان شکل کارت عمومی به‌روز می‌شود.</small>
                <small data-category-crop-status role="status"></small>
            @endif
        </div>
    </div>

    <div class="admin-field">
        <label for="{{ $inputId }}">{{ $label }}</label>
        <input
            id="{{ $inputId }}"
            type="file"
            name="{{ $name }}"
            accept="{{ $categoryCard ? 'image/jpeg,image/png,image/webp' : 'image/jpeg,image/png,image/webp,image/avif' }}"
            data-media-input
        >
        <small class="admin-help">
            @if($categoryCard) JPG، PNG یا WebP — خروجی ذخیره حداکثر ۲ مگابایت @else JPG، PNG، WebP یا AVIF — حداکثر 5MB @endif
        </small>
    </div>

    <div class="admin-media-picker__editor" data-media-editor hidden>
        <div class="admin-media-picker__editor-head">
            <div>
                <strong>کادر نمایش را تنظیم کن</strong>
                <small>عکس را بکش و با زوم، دقیقاً همان بخشی را که می‌خواهی در فروشگاه دیده شود انتخاب کن.</small>
            </div>
            <span class="admin-badge admin-badge--info" data-media-ratio-label>{{ $ratio }}</span>
        </div>

        <canvas
            width="720"
            height="720"
            data-media-canvas
            aria-label="ویرایش کادر تصویر"
        ></canvas>

        <div class="admin-media-picker__controls">
            <label class="admin-field">
                <span>زوم</span>
                <input type="range" min="1" max="3" step="0.01" value="1" data-media-zoom>
            </label>

            <button type="button" class="admin-btn admin-btn--ghost" data-media-fit>
                فیت کردن
            </button>

            <button type="button" class="admin-btn admin-btn--secondary" data-media-apply>
                اعمال کادر
            </button>

            <button type="button" class="admin-btn admin-btn--ghost" data-media-cancel>
                انصراف
            </button>
        </div>
    </div>
</div>
