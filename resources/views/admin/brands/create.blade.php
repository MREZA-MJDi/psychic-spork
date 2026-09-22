@extends('layouts.admin')

@section('title', 'افزودن برند')
@section('page-title', 'افزودن برند')

@section('content')

    <div class="admin-page-head">

        <div>
            <h1 class="admin-page-title">افزودن برند</h1>

            <p class="admin-page-description">
                ثبت برند جدید
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
        action="{{ route('admin.brands.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        @include('admin.brands._form')

        <div class="admin-form-actions">

            <button
                type="submit"
                class="admin-btn admin-btn-primary"
            >
                ذخیره برند
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
