@extends('layouts.admin')

@section('title', 'ویرایش واریانت')
@section('page-title', 'ویرایش واریانت')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">
                ویرایش واریانت
            </h1>

            <p class="admin-page-description">
                {{ $product->name }}
                —
                {{ $variant->sku }}
            </p>
        </div>

        <a
            href="{{ route('admin.products.variants.index', $product) }}"
            class="admin-btn admin-btn-light"
        >
            بازگشت
        </a>

    </div>

    <form
        method="POST"
        action="{{ route('admin.products.variants.update', [$product, $variant]) }}"
    >
        @csrf
        @method('PUT')

        @include('admin.variants._form', [
            'variant' => $variant,
        ])

        <div class="admin-form-actions">

            <button
                type="submit"
                class="admin-btn admin-btn-primary"
            >
                ذخیره تغییرات
            </button>

            <a
                href="{{ route('admin.products.variants.index', $product) }}"
                class="admin-btn admin-btn-light"
            >
                انصراف
            </a>

        </div>

    </form>

@endsection
