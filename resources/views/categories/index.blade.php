@extends('layouts.store')

@section('title','دسته‌بندی‌ها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium">
    <div class="container page-hero__layout">
        <div>
            <span class="eyebrow">01 / COLLECTIONS</span>
            <h1>دسته‌بندی‌ها</h1>
            <p>فصل‌های مختلف جانان را بر اساس فرم، کاربرد و حال‌وهوای انتخابت مرور کن.</p>
        </div>
        <div class="page-hero__stat"><b>{{ number_format($categories->count()) }}</b><span>دسته فعال</span></div>
    </div>
</section>
<section class="section-block collection-stage"><div class="container">
@if($categories->isNotEmpty())
<div class="collection-grid">
@foreach($categories as $category)
<a href="{{ route('categories.show',$category) }}" class="collection-card">
<div class="collection-card__image">
@if($category->coverMedia?->url)<img src="{{ $category->coverMedia->url }}" alt="{{ $category->name }}" loading="lazy">@else<div class="card-image-placeholder"><span>{{ $category->name }}</span></div>@endif
<span class="collection-card__number">{{ sprintf('%02d', $loop->iteration) }}</span>
</div>
<div class="collection-card__body"><div><small>JANAN / COLLECTION</small><h2>{{ $category->name }}</h2><p>{{ $category->description ?: 'کالکشن جانان' }}</p></div><div class="collection-card__meta"><span>{{ number_format($category->active_products_count) }} محصول</span><b>↗</b></div></div>
</a>
@endforeach
</div>
@else<div class="empty-state"><h2>دسته‌بندی فعالی وجود ندارد.</h2></div>@endif
</div></section>
@endsection
