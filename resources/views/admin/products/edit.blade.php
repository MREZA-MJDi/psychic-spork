@extends('layouts.admin')

@section('title', 'ویرایش محصول')
@section('page-title', 'ویرایش محصول')

@section('content')

    @php
        $variant = $product->variants->first();
    @endphp

    <div class="admin-page-head">
        <div>
            <h1 class="admin-page-title">ویرایش محصول</h1>
            <p class="admin-page-description">
                {{ $product->name }}
            </p>
        </div>

        <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-light">
            بازگشت
        </a>
    </div>

    <form
        method="POST"
        action="{{ route('admin.products.update', $product) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        @include('admin.products._form', [
            'product' => $product,
            'variant' => $variant,
        ])

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">
                ذخیره تغییرات
            </button>

            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-light">
                انصراف
            </a>
        </div>
    </form>

@endsection
