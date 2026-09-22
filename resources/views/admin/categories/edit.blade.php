@extends('layouts.admin')

@section('title', 'ویرایش دسته‌بندی')
@section('page-title', 'ویرایش دسته‌بندی')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">ویرایش دسته‌بندی</h1>
            <p class="admin-page-description">
                {{ $category->name }}
            </p>
        </div>

        <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-light">
            بازگشت
        </a>

    </div>

    <form
        method="POST"
        action="{{ route('admin.categories.update', $category) }}"
    >
        @csrf
        @method('PUT')

        @include('admin.categories._form', [
            'category' => $category,
        ])

        <div class="admin-form-actions">

            <button type="submit" class="admin-btn admin-btn-primary">
                ذخیره تغییرات
            </button>

            <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-light">
                انصراف
            </a>

        </div>

    </form>

@endsection
