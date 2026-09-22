@php
    $product = $product ?? null;
    $variant = $variant ?? null;

    $attributesJson = old(
        'attributes_json',
        $product?->attributes
            ? json_encode(
                $product->attributes,
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
            )
            : ''
    );

    $galleryImage = $product?->galleryMedia?->first();
@endphp

<div class="admin-form-grid">

    <div class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">اطلاعات اصلی</h2>
                <p class="admin-card-description">
                    مشخصات اصلی محصول
                </p>
            </div>
        </div>

        <div class="admin-form-grid">

            <div class="admin-field">
                <label for="name">نام محصول *</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $product?->name) }}"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="slug">Slug *</label>
                <input
                    id="slug"
                    type="text"
                    name="slug"
                    value="{{ old('slug', $product?->slug) }}"
                    dir="ltr"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="category_id">دسته‌بندی *</label>

                <select id="category_id" name="category_id" required>
                    <option value="">انتخاب دسته‌بندی</option>

                    @foreach($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected((string) old('category_id', $product?->category_id) === (string) $category->id)
                        >
                        {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-field">
                <label for="brand_id">برند</label>

                <select id="brand_id" name="brand_id">
                    <option value="">بدون برند</option>

                    @foreach($brands as $brand)
                        <option
                            value="{{ $brand->id }}"
                            @selected((string) old('brand_id', $product?->brand_id) === (string) $brand->id)
                        >
                        {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-field admin-field-full">
                <label for="short_description">توضیح کوتاه</label>

                <textarea
                    id="short_description"
                    name="short_description"
                    rows="3"
                >{{ old('short_description', $product?->short_description) }}</textarea>
            </div>

            <div class="admin-field admin-field-full">
                <label for="description">توضیحات محصول</label>

                <textarea
                    id="description"
                    name="description"
                    rows="8"
                >{{ old('description', $product?->description) }}</textarea>
            </div>

            <div class="admin-field admin-field-full">
                <label for="attributes_json">
                    ویژگی‌ها
                </label>

                <textarea
                    id="attributes_json"
                    name="attributes_json"
                    rows="8"
                    dir="ltr"
                    placeholder='{"جنس":"ساتن","کشور تولید":"ایران"}'
                >{{ $attributesJson }}</textarea>

                <small class="admin-help">
                    مقدار باید JSON معتبر باشد.
                </small>
            </div>

        </div>

    </div>


    <div class="admin-card admin-form-section">

        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">وضعیت</h2>
            </div>
        </div>

        <div class="admin-checkbox-list">

            <label class="admin-checkbox">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $product?->is_active ?? true))
                >
                <span>محصول فعال باشد</span>
            </label>

            <label class="admin-checkbox">
                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    @checked(old('is_featured', $product?->is_featured ?? false))
                >
                <span>محصول ویژه باشد</span>
            </label>

        </div>

    </div>


    <div class="admin-card admin-form-section">

        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">ترتیب نمایش</h2>
            </div>
        </div>

        <div class="admin-field">
            <label for="sort_order">ترتیب</label>

            <input
                id="sort_order"
                type="number"
                name="sort_order"
                min="0"
                value="{{ old('sort_order', $product?->sort_order ?? 0) }}"
            >
        </div>

    </div>


    <div class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">واریانت اصلی</h2>
                <p class="admin-card-description">
                    قیمت، موجودی و مشخصات قابل فروش محصول
                </p>
            </div>
        </div>

        <div class="admin-form-grid">

            <div class="admin-field">
                <label for="sku">SKU *</label>

                <input
                    id="sku"
                    type="text"
                    name="sku"
                    value="{{ old('sku', $variant?->sku) }}"
                    dir="ltr"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="size">سایز</label>

                <input
                    id="size"
                    type="text"
                    name="size"
                    value="{{ old('size', $variant?->size) }}"
                >
            </div>

            <div class="admin-field">
                <label for="color">رنگ</label>

                <input
                    id="color"
                    type="text"
                    name="color"
                    value="{{ old('color', $variant?->color) }}"
                >
            </div>

            <div class="admin-field">
                <label for="color_code">کد رنگ</label>

                <input
                    id="color_code"
                    type="text"
                    name="color_code"
                    value="{{ old('color_code', $variant?->color_code) }}"
                    dir="ltr"
                    placeholder="#000000"
                >
            </div>

            <div class="admin-field">
                <label for="price">قیمت *</label>

                <input
                    id="price"
                    type="number"
                    name="price"
                    min="0"
                    step="1"
                    value="{{ old('price', $variant?->price) }}"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="sale_price">قیمت فروش</label>

                <input
                    id="sale_price"
                    type="number"
                    name="sale_price"
                    min="0"
                    step="1"
                    value="{{ old('sale_price', $variant?->sale_price) }}"
                >
            </div>

            <div class="admin-field">
                <label for="stock">موجودی *</label>

                <input
                    id="stock"
                    type="number"
                    name="stock"
                    min="0"
                    value="{{ old('stock', $variant?->stock ?? 0) }}"
                    required
                >
            </div>

            <div class="admin-field">
                <label for="low_stock_threshold">حد هشدار موجودی</label>

                <input
                    id="low_stock_threshold"
                    type="number"
                    name="low_stock_threshold"
                    min="0"
                    value="{{ old('low_stock_threshold', $variant?->low_stock_threshold ?? 5) }}"
                >
            </div>

        </div>

    </div>


    <div class="admin-card admin-form-section admin-form-section-full">

        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">تصویر محصول</h2>
            </div>
        </div>

        @if($galleryImage?->url)
            <div class="admin-current-image">

                <img
                    src="{{ $galleryImage->url }}"
                    alt="{{ $product?->name }}"
                >

                <div>
                    <strong>تصویر فعلی</strong>
                    <p class="admin-muted">
                        در صورت انتخاب تصویر جدید، تصویر جدید به گالری اضافه می‌شود.
                    </p>
                </div>

            </div>
        @endif

        <div class="admin-field">
            <label for="image_file">تصویر</label>

            <input
                id="image_file"
                type="file"
                name="image_file"
                accept="image/jpeg,image/png,image/webp"
            >

            <small class="admin-help">
                فرمت‌های JPG، PNG و WebP
            </small>
        </div>

    </div>

</div>
