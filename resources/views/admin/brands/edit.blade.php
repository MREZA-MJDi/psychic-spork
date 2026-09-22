@extends('layouts.admin')

@section('title', 'ویرایش برند')
@section('page-title', 'ویرایش برند')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">ویرایش برند</h1>

            <p class="admin-page-description">
                {{ $brand->name }}
            </p>
        </div>

        <a
            href="{{ route('admin.brands.index') }}"
            class="admin-btn admin-btn-light"
        >
            بازگشت
        </a>

    </div>

    <form
        method="POST"
        action="{{ route('admin.brands.update', $brand) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        @include('admin.brands._form', [
            'brand' => $brand,
        ])

        <div class="admin-form-actions">

            <button
                type="submit"
                class="admin-btn admin-btn-primary"
            >
                ذخیره تغییرات
            </button>

            <a
                href="{{ route('admin.brands.index') }}"
                class="admin-btn admin-btn-light"
            >
                انصراف
            </a>

        </div>

    </form>

@endsection
