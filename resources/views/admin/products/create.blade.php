@extends('layouts.admin')

@section('title', 'افزودن محصول')
@section('page-title', 'افزودن محصول')

@section('content')

    <div class="admin-page-head">
        <div>
            <h1 class="admin-page-title">افزودن محصول</h1>
            <p class="admin-page-description">
                اطلاعات محصول و اولین واریانت آن را ثبت کنید.
            </p>
        </div>

        <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-light">
            بازگشت
        </a>
    </div>

    <form
        method="POST"
        action="{{ route('admin.products.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        @include('admin.products._form')

        <div class="admin-form-actions">
            <button type="submit" class="admin-btn admin-btn-primary">
                ذخیره محصول
            </button>

            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-light">
                انصراف
            </a>
        </div>
    </form>

@endsection
