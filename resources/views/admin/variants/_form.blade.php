@php
    $variant = $variant ?? null;
@endphp

<div class="admin-card">

    <div class="admin-card-header">

        <div>
            <h2 class="admin-card-title">
                مشخصات واریانت
            </h2>

            <p class="admin-card-description">
                اطلاعات قابل فروش این گزینه محصول
            </p>
        </div>

    </div>


    <div class="admin-form-grid">

        <div class="admin-field">

            <label for="sku">
                SKU *
            </label>

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

            <label for="size">
                سایز
            </label>

            <input
                id="size"
                type="text"
                name="size"
                value="{{ old('size', $variant?->size) }}"
            >

        </div>


        <div class="admin-field">

            <label for="color">
                رنگ
            </label>

            <input
                id="color"
                type="text"
                name="color"
                value="{{ old('color', $variant?->color) }}"
            >

        </div>


        <div class="admin-field">

            <label for="color_code">
                کد رنگ
            </label>

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

            <label for="price">
                قیمت اصلی *
            </label>

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

            <label for="sale_price">
                قیمت فروش
            </label>

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

            <label for="stock">
                موجودی *
            </label>

            <input
                id="stock"
                type="number"
                name="stock"
                min="0"
                value="{{ old('stock', $variant?->stock ?? 0) }}"
                required
            >

            <small class="admin-help">
                موجودی فعلی این واریانت
            </small>

        </div>


        <div class="admin-field">

            <label for="low_stock_threshold">
                حد هشدار موجودی
            </label>

            <input
                id="low_stock_threshold"
                type="number"
                name="low_stock_threshold"
                min="0"
                value="{{ old('low_stock_threshold', $variant?->low_stock_threshold ?? 5) }}"
            >

        </div>


        <div class="admin-field">

            <label for="sort_order">
                ترتیب نمایش
            </label>

            <input
                id="sort_order"
                type="number"
                name="sort_order"
                min="0"
                value="{{ old('sort_order', $variant?->sort_order ?? 0) }}"
            >

        </div>


        <div class="admin-field admin-field-full">

            <label class="admin-checkbox">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $variant?->is_active ?? true))
                >

                <span>
                    این واریانت فعال و قابل خرید باشد
                </span>

            </label>

        </div>

    </div>

</div>


<div class="admin-card admin-form-section">

    <div class="admin-card-header">

        <div>
            <h2 class="admin-card-title">
                محصول والد
            </h2>
        </div>

    </div>

    <div class="admin-product-summary">

        <div>
            <strong>
                {{ $product->name }}
            </strong>

            <div class="admin-product-meta">
                SKU و مشخصات بالا متعلق به این محصول هستند.
            </div>
        </div>

    </div>

</div>
