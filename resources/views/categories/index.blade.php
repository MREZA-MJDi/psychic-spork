@extends('layouts.store')

@section('title','دسته‌بندی‌ها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">EXPLORE</span><h1>دسته‌بندی‌ها</h1><p>دسته‌بندی‌های فعال فروشگاه.</p></div></section>
<section class="section-block"><div class="container">
@if($categories->isNotEmpty())
<div class="category-grid category-grid--large">
@foreach($categories as $category)
<a href="{{ route('categories.show',$category) }}" class="category-card">
<div class="category-card__image">@if($category->coverMedia?->url)<img src="{{ $category->coverMedia->url }}" alt="{{ $category->name }}" loading="lazy">@else<div class="card-image-placeholder"><span>{{ $category->name }}</span></div>@endif</div>
<div class="category-card__content"><div><h3>{{ $category->name }}</h3><span>{{ $category->description ?: 'کالکشن جانان' }}</span></div><strong>{{ number_format($category->active_products_count) }}</strong></div>
</a>
@endforeach
</div>
@else<div class="empty-state"><h2>دسته‌بندی فعالی وجود ندارد.</h2></div>@endif
</div></section>
@endsection
