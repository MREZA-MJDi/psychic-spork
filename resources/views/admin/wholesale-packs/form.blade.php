@extends('layouts.admin')

@php
    $editing = $pack->exists;
    $selected = $pack->exists
        ? $pack->items->keyBy('product_variant_id')
        : collect();
    $oldItems = collect(old('items', []))->keyBy('variant_id');
@endphp

@section('title', $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده')
@section('page-title', $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده')

@section('content')
<div class="wholesale-pack-editor">
    <div class="admin-page-head wholesale-pack-editor__head">
        <div>
            <span class="wholesale-pack-editor__eyebrow">فروش عمده / {{ $editing ? 'ویرایش' : 'پک جدید' }}</span>
            <h1 class="admin-page-head__title">{{ $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده' }}</h1>
            <p class="admin-page-head__text">عکس، قیمت و اقلام بسته را وارد کن؛ تعداد کل بسته از روی تعداد اقلام انتخاب‌شده محاسبه می‌شود.</p>
        </div>
        <a href="{{ route('admin.wholesale-packs.index') }}" class="admin-btn admin-btn--ghost">بازگشت به پک‌ها</a>
    </div>



    <form class="wholesale-pack-editor__form" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.wholesale-packs.update', $pack) : route('admin.wholesale-packs.store') }}" data-wholesale-pack-form data-lookup-url="{{ route('admin.variant-lookup', ['wholesale' => 1]) }}">
        @csrf
        @if($editing) @method('PUT') @endif

        <section class="admin-card wholesale-pack-editor__section" aria-labelledby="pack-details-title">
            <header class="admin-card-header wholesale-pack-editor__section-head">
                <span class="wholesale-pack-editor__step">۱</span>
                <div>
                    <h2 class="admin-card-title" id="pack-details-title">مشخصات بسته</h2>
                    <p class="admin-card-description">اطلاعاتی که مشتری در صفحهٔ خرید عمده می‌بیند.</p>
                </div>
            </header>

            <div class="admin-card-body">
                <div class="wholesale-pack-editor__details-grid">
                    <div class="admin-field wholesale-pack-editor__name">
                        <label for="name">نام قابل نمایش بسته <span class="required">*</span></label>
                        <input id="name" name="name" required value="{{ old('name', $pack->name) }}" placeholder="مثلاً پک ۱۲ عددی شورت ایزابلا" aria-describedby="pack-name-help">
                        <small class="admin-help" id="pack-name-help">نام محصول یا برند و تعداد بسته را واضح بنویس.</small>
                    </div>

                    <div class="admin-field wholesale-pack-editor__price">
                        <label for="pack_price">قیمت کل این بسته <span class="required">*</span></label>
                        <div class="wholesale-pack-editor__money">
                            <input id="pack_price" type="text" name="pack_price" inputmode="numeric" autocomplete="off" data-money-input required value="{{ old('pack_price', $pack->pack_price) }}" placeholder="مثلاً ۵٬۴۰۰٬۰۰۰" aria-describedby="pack-price-help">
                            <span>تومان</span>
                        </div>
                        <small class="admin-help" id="pack-price-help">قیمت نهایی همهٔ اقلام داخل بسته است؛ با قیمت عمدهٔ هر قلم فرق دارد.</small>
                    </div>

                    <details class="admin-field wholesale-pack-editor__slug" @if($errors->has('slug')) open @endif>
                        <summary>تنظیمات آدرس بسته</summary>
                        <label for="slug">نامک پیوند <span class="wholesale-pack-editor__optional">اختیاری</span></label>
                        <input id="slug" name="slug" dir="ltr" value="{{ old('slug', $pack->slug) }}" placeholder="isabella-12-pack" aria-describedby="pack-slug-help">
                        <small class="admin-help" id="pack-slug-help">اگر خالی بماند، خودکار از نام بسته ساخته می‌شود.</small>
                    </details>

                    <div class="admin-field wholesale-pack-editor__description">
                        <label for="description">توضیح کوتاه برای مشتری <span class="wholesale-pack-editor__optional">اختیاری</span></label>
                        <textarea id="description" name="description" rows="3" maxlength="5000" placeholder="مثلاً ۱۲ عدد در سه رنگ؛ مناسب سایزهای A، B و C.">{{ old('description', $pack->description) }}</textarea>
                    </div>

                    <div class="wholesale-pack-image">
                        <div class="wholesale-pack-image__preview" data-pack-image-preview>
                            @if($pack->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($pack->image_path) }}" alt="تصویر فعلی {{ $pack->name }}" data-pack-image-current>
                            @else
                                <span data-pack-image-empty>برای بسته هنوز عکسی انتخاب نشده</span>
                            @endif
                        </div>
                        <div class="wholesale-pack-image__controls">
                            <label for="pack_image">عکس خودِ بسته</label>
                            <input id="pack_image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-pack-image-input aria-describedby="pack-image-help">
                            <small class="admin-help" id="pack-image-help">JPG، PNG یا WebP؛ حداکثر ۵ مگابایت. این عکس در کارت پک عمده نمایش داده می‌شود. {{ $editing && $pack->image_path ? 'با انتخاب عکس تازه، عکس فعلی جایگزین می‌شود.' : '' }}</small>
                            @if($editing && $pack->image_path)
                                <label class="wholesale-pack-image__remove"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> حذف عکس فعلی</label>
                            @endif
                        </div>
                    </div>

                    <div class="wholesale-pack-editor__display-settings">
                        <div class="admin-field wholesale-pack-editor__sort">
                            <label for="sort_order">جایگاه نمایش</label>
                            <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $pack->sort_order ?? 0) }}">
                            <small class="admin-help">عدد کمتر زودتر نمایش داده می‌شود.</small>
                        </div>
                        <label class="admin-checkbox wholesale-pack-editor__active">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $pack->is_active : true))>
                            <span><strong>نمایش در صفحهٔ خرید عمده</strong><small>خاموش‌کردن، پک را از دید مشتری پنهان می‌کند.</small></span>
                        </label>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-card wholesale-pack-editor__section" aria-labelledby="pack-items-title">
            <header class="admin-card-header wholesale-pack-editor__section-head wholesale-pack-editor__items-head">
                <span class="wholesale-pack-editor__step">۲</span>
                <div>
                    <h2 class="admin-card-title" id="pack-items-title">انتخاب اقلام بسته</h2>
                    <p class="admin-card-description">محصول را پیدا کن، مدل موردنظر را انتخاب کن و تعداد آن را در بسته مشخص کن.</p>
                </div>
            </header>

            <div class="admin-card-body">
                <div class="wholesale-pack-price-summary" aria-live="polite">
                    <div><span>جمع قیمت عمده اقلام</span><strong><b data-pack-base-price>۰</b> تومان</strong></div>
                    <div><span>تخفیف نسبت به خرید جداگانه</span><strong><b data-pack-discount>۰</b> تومان</strong></div>
                    <p data-pack-price-status>بعد از انتخاب اقلام، جمع قیمت و تخفیف اینجا نمایش داده می‌شود.</p>
                </div>
                <div class="wholesale-pack-editor__selection-summary" aria-live="polite" data-pack-selection-summary>
                    <div><strong data-pack-selected-count>۰</strong><span>مدل انتخاب‌شده</span></div>
                    <div><strong data-pack-unit-count>۰</strong><span>تعداد کل داخل پک</span></div>
                    <div class="wholesale-pack-editor__summary-note">برای ساخت پک، دست‌کم یک مدل انتخاب کن.</div>
                </div>

                <div class="wholesale-pack-editor__tools">
                    <label class="wholesale-pack-editor__search">
                        <span class="sr-only">جستجو در واریانت‌ها</span>
                        <input id="variant-search" type="search" placeholder="مثلاً ایزابلا مشکی یا ۰۹۰۲۱۰۷" autocomplete="off" aria-describedby="variant-search-help">
                        <small class="admin-help" id="variant-search-help">با نوشتن نام، رنگ، سایز، برند یا کد کالا، پیشنهادها نمایش داده می‌شوند.</small>
                    </label>
                    <div class="wholesale-pack-editor__bulk-actions">
                        <label class="admin-checkbox"><input type="checkbox" data-pack-in-stock> فقط مدل‌های موجود</label>
                        <button type="button" class="admin-btn admin-btn--ghost admin-btn--sm" data-pack-clear-all>پاک‌کردن انتخاب‌ها</button>
                    </div>
                </div>

                <p class="admin-help" role="status" data-pack-search-status>در حال دریافت مدل‌ها…</p>
                <button type="button" class="admin-btn admin-btn--ghost" data-pack-search-retry hidden>تلاش دوباره</button>
                <h3 class="admin-card-title">پیشنهادهای محصول</h3>
                <div class="wholesale-pack-variant-list" data-pack-variant-list>
                    @foreach($variants as $variant)
                        @php
                            $item = $selected->get($variant->id);
                            $oldItem = $oldItems->get((string) $variant->id, $oldItems->get($variant->id));
                            $isSelected = $oldItem !== null ? (string) data_get($oldItem, 'variant_id') === (string) $variant->id : (bool) $item;
                            $quantity = data_get($oldItem, 'quantity', $item?->quantity ?? 1);
                        @endphp
                        <article class="wholesale-pack-variant" hidden data-variant-row data-wholesale-price="{{ $variant->wholesale_price ?? '' }}">
                            <label class="wholesale-pack-variant__pick">
                                <input type="checkbox" value="{{ $variant->id }}" data-pack-variant-toggle data-wholesale-price="{{ $variant->wholesale_price ?? '' }}" @checked($isSelected) @disabled($variant->wholesale_price === null && !$isSelected)>
                                <span class="wholesale-pack-variant__copy">
                                    <strong>{{ $variant->product?->name ?? 'محصول بدون نام' }}</strong>
                                    <span class="wholesale-pack-variant__meta">
                                        <b>{{ $variant->product?->brand?->name ?? 'بدون برند' }}</b>
                                        <span>{{ $variant->display_name ?: 'بدون رنگ و سایز' }}</span>
                                        <code dir="ltr">{{ $variant->sku ?: 'بدون SKU' }}</code>
                                    </span>
                                    <small>قیمت عمدهٔ تکی: {{ $variant->wholesale_price !== null ? \App\Support\PersianNumber::format($variant->wholesale_price) . ' تومان' : 'ثبت نشده' }} <span aria-hidden="true">·</span> موجودی: {{ \App\Support\PersianNumber::format($variant->stock) }}</small>
                                </span>
                            </label>
                            <input type="hidden" value="{{ is_scalar($quantity) ? $quantity : '' }}" data-pack-quantity>
                        </article>
                    @endforeach
                </div>
                <div class="admin-pagination" data-pack-variant-pages hidden></div>
                <p class="wholesale-pack-editor__no-results" data-pack-no-results hidden>مدل فعالی با قیمت عمده پیدا نشد. عبارت دیگری را امتحان کن یا فیلتر موجودی را بردار.</p>

                <section class="pack-selected" aria-labelledby="pack-selected-title">
                    <h3 class="admin-card-title" id="pack-selected-title">اقلام انتخاب‌شده</h3>
                    <p class="admin-help" data-pack-selection-empty>هنوز مدلی انتخاب نشده است.</p>
                    <ul class="pack-selected__list" data-pack-selected-list></ul>
                </section>
                @error('items')<p class="wholesale-pack-editor__items-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </section>

        <div class="wholesale-pack-editor__actions">
            <a href="{{ route('admin.wholesale-packs.index') }}" class="admin-btn admin-btn--ghost">انصراف</a>
            <button class="admin-btn admin-btn--secondary" type="submit">{{ $editing ? 'ذخیره تغییرات پک' : 'ساخت پک عمده' }}</button>
        </div>
    </form>
</div>
@endsection
