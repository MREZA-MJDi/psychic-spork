@extends('layouts.admin')

@section('title', 'افزودن دسته‌بندی')
@section('page-title', 'افزودن دسته‌بندی')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">افزودن دسته‌بندی</h1>
            <p class="admin-page-description">
                ایجاد دسته‌بندی جدید
            </p>
        </div>

        <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-light">
            بازگشت
        </a>

    </div>

    <form
        method="POST"
        action="{{ route('admin.categories.store') }}"
    >
        @csrf

        @include('admin.categories._form')

        <div class="admin-form-actions">

            <button type="submit" class="admin-btn admin-btn-primary">
                ذخیره دسته‌بندی
            </button>

            <a href="{{ route('admin.categories.index') }}" class="admin-btn admin-btn-light">
                انصراف
            </a>

        </div>

    </form>

@endsection
