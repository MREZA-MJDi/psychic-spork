@extends('layouts.admin')

@php
    $editing = $pack->exists;
    $selected = $pack->exists
        ? $pack->items->keyBy('product_variant_id')
        : collect();
@endphp

@section('title', $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده')
@section('page-title', $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">{{ $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده' }}</h1>
        <p class="admin-page-head__text">هر ردیف یک Variant واقعی است؛ تعداد را برای اقلام داخل پک تعیین کن.</p>
    </div>
    <a href="{{ route('admin.wholesale-packs.index') }}" class="admin-btn admin-btn--ghost">بازگشت</a>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ $editing ? route('admin.wholesale-packs.update', $pack) : route('admin.wholesale-packs.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">اطلاعات پک</h2>
                <p class="admin-card-description">مثلاً «پک ۱۲ تایی Isabela».</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="admin-form-grid">
                <div class="admin-field">
                    <label for="name">نام پک *</label>
                    <input id="name" name="name" required value="{{ old('name', $pack->name) }}" placeholder="پک ۱۲ تایی Isabela">
                </div>
                <div class="admin-field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" dir="ltr" value="{{ old('slug', $pack->slug) }}" placeholder="isabela-12-pack">
                </div>
                <div class="admin-field">
                    <label for="pack_price">قیمت نهایی پک عمده</label>
                    <input id="pack_price" type="number" min="0" step="1" name="pack_price" value="{{ old('pack_price', $pack->pack_price) }}" placeholder="خالی = جمع قیمت عمده اقلام">
                    <small class="admin-help">اگر خالی باشد، قیمت پک از قیمت عمده Variantهای داخل آن محاسبه می‌شود.</small>
                </div>
                <div class="admin-field">
                    <label for="sort_order">ترتیب</label>
                    <input id="sort_order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $pack->sort_order ?? 0) }}">
                </div>
                <div class="admin-field admin-field-full">
                    <label for="description">توضیحات</label>
                    <textarea id="description" name="description" rows="3">{{ old('description', $pack->description) }}</textarea>
                </div>
                <div class="admin-field admin-field-full">
                    <label class="admin-checkbox">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing ? $pack->is_active : true))>
                        <span>این پک در صفحه عمده نمایش داده شود</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <div>
                <h2 class="admin-card-title">اقلام پک</h2>
                <p class="admin-card-description">Product و Variant را از کاتالوگ واقعی انتخاب کن؛ برند محصول هم کنار آن نمایش داده می‌شود.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <input id="variant-search" class="admin-field-input" type="search" placeholder="جستجو در محصول، برند، SKU، سایز یا رنگ..." style="width:100%;margin-bottom:14px;">
            <div style="display:grid;gap:8px;">
                @foreach($variants as $variant)
                    @php $item = $selected->get($variant->id); @endphp
                    <label class="wholesale-pack-variant-row" data-variant-row data-search="{{ strtolower(($variant->product?->name ?? '') . ' ' . ($variant->product?->brand?->name ?? '') . ' ' . ($variant->sku ?? '') . ' ' . ($variant->size ?? '') . ' ' . ($variant->color ?? '')) }}">
                        <input type="checkbox" name="items[{{ $variant->id }}][variant_id]" value="{{ $variant->id }}" @checked($item)>
                        <div class="wholesale-pack-variant-copy">
                            <strong>{{ $variant->product?->name }}</strong>
                            <span>
                                {{ $variant->product?->brand?->name ?? 'بدون برند' }}
                                · Variant: {{ $variant->display_name }}
                                · SKU: {{ $variant->sku ?: '—' }}
                            </span>
                            <small>قیمت عمده: {{ $variant->wholesale_price !== null ? number_format($variant->wholesale_price) . ' تومان' : 'ثبت نشده' }} · موجودی: {{ number_format($variant->stock) }}</small>
                        </div>
                        <input class="wholesale-pack-qty" type="number" name="items[{{ $variant->id }}][quantity]" min="1" max="100000" value="{{ old('items.' . $variant->id . '.quantity', $item?->quantity ?? 1) }}" @disabled(!$item) aria-label="تعداد {{ $variant->product?->name }}">
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="admin-form-actions">
        <a href="{{ route('admin.wholesale-packs.index') }}" class="admin-btn admin-btn--ghost">انصراف</a>
        <button class="admin-btn admin-btn--secondary" type="submit">ذخیره پک</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('variant-search');
    const rows = [...document.querySelectorAll('[data-variant-row]')];

    search?.addEventListener('input', () => {
        const term = search.value.trim().toLowerCase();
        rows.forEach(row => {
            row.hidden = term && !row.dataset.search.includes(term);
        });
    });

    rows.forEach(row => {
        const checkbox = row.querySelector('input[type="checkbox"]');
        const qty = row.querySelector('.wholesale-pack-qty');

        checkbox?.addEventListener('change', () => {
            qty.disabled = !checkbox.checked;
            if (checkbox.checked && Number(qty.value || 0) < 1) qty.value = 1;
        });
    });
});
</script>
@endsection
