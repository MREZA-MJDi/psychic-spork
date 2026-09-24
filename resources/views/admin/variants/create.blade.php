@extends('layouts.admin')

@section('title', 'افزودن واریانت')
@section('page-title', 'افزودن واریانت')

@section('content')

    <div class="admin-page-head">

        <div>

            <h1 class="admin-page-head__title">
                افزودن واریانت
            </h1>

            <p class="admin-page-head__text">
                {{ $product->name }}
            </p>

        </div>

        <a
            href="{{ route('admin.products.variants.index', $product) }}"
            class="admin-btn admin-btn--ghost"
        >
            بازگشت
        </a>

    </div>


    <form
        method="POST"
        action="{{ route('admin.products.variants.store', $product) }}"
    >

        @csrf

        @include('admin.variants._form', [
            'product' => $product,
            'variant' => $variant ?? new \App\Models\ProductVariant(),
        ])

    </form>

@endsection
