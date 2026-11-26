@extends('layouts.admin')

@php($editing = $pack->exists)

@section('title', $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده')
@section('page-title', $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده')

@section('content')
<div class="wholesale-pack-editor">
    <div class="admin-page-head wholesale-pack-editor__head">
        <div>
            <span class="wholesale-pack-editor__eyebrow">فروش عمده / {{ $editing ? 'ویرایش' : 'پک جدید' }}</span>
            <h1 class="admin-page-head__title">{{ $editing ? 'ویرایش پک عمده' : 'ساخت پک عمده' }}</h1>
            <p class="admin-page-head__text">عکس، قیمت و اقلام بسته را وارد کن؛ تعداد کل بسته از روی تعداد اقلام انتخاب‌شده محاسبه می‌شود.</p>
        </div>
        <a href="{{ route('admin.wholesale-packs.index') }}" class="admin-btn admin-btn--ghost">بازگشت به پک‌ها</a>
    </div>



    @include('admin.wholesale-packs.fields')

</div>
@endsection
