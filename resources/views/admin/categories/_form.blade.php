@php
    $category = $category ?? null;
@endphp

<div class="admin-card">

    <div class="admin-card-header">

        <div>
            <h2 class="admin-card-title">اطلاعات دسته‌بندی</h2>
            <p class="admin-card-description">
                اطلاعات پایه دسته‌بندی
            </p>
        </div>

    </div>

    <div class="admin-form-grid">

        <div class="admin-field">
            <label for="name">نام دسته‌بندی *</label>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $category?->name) }}"
                required
            >
        </div>

        <div class="admin-field">
            <label for="slug">Slug *</label>

            <input
                id="slug"
                type="text"
                name="slug"
                value="{{ old('slug', $category?->slug) }}"
                dir="ltr"
                required
            >
        </div>

        <div class="admin-field">
            <label for="parent_id">دسته والد</label>

            <select id="parent_id" name="parent_id">

                <option value="">
                    دسته اصلی
                </option>

                @foreach($parentCategories as $parent)
                    <option
                        value="{{ $parent->id }}"
                        @selected((string) old('parent_id', $category?->parent_id) === (string) $parent->id)
                    >
                    {{ $parent->name }}
                    </option>
                @endforeach

            </select>

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
                value="{{ old('sort_order', $category?->sort_order ?? 0) }}"
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
            >{{ old('description', $category?->description) }}</textarea>

        </div>

        <div class="admin-field admin-field-full">

            <label class="admin-checkbox">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    @checked(old('is_active', $category?->is_active ?? true))
                >

                <span>
                    دسته‌بندی فعال باشد
                </span>

            </label>

        </div>

    </div>

</div>
