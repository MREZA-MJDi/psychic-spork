@extends('layouts.admin')

@section('title', 'افزودن واریانت')
@section('page-title', 'افزودن واریانت')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">
                افزودن واریانت
            </h1>

            <p class="admin-page-description">
                {{ $product->name }}
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
        action="{{ route('admin.products.variants.store', $product) }}"
    >
        @csrf

        @include('admin.variants._form')

        <div class="admin-form-actions">

            <button
                type="submit"
                class="admin-btn admin-btn-primary"
            >
                ذخیره واریانت
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
