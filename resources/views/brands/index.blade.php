@extends('layouts.store')

@section('title','برندها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">BRANDS</span><h1>برندها</h1><p>برندهای فعال فروشگاه.</p></div></section>
<section class="section-block"><div class="container">
@if($brands->isNotEmpty())
<div class="brand-grid">
@foreach($brands as $brand)
<a class="brand-card reveal-up" href="{{ route('brands.show',$brand) }}">
<div class="brand-card__logo">@if($brand->logoMedia?->url)<img src="{{ $brand->logoMedia->url }}" alt="{{ $brand->name }}" loading="lazy">@else<span class="brand-card__fallback">{{ mb_substr($brand->name,0,1) }}</span>@endif</div>
<div class="brand-card__body"><h3>{{ $brand->name }}</h3><p>{{ $brand->description ?: 'معرفی این برند هنوز تکمیل نشده است.' }}</p><div class="brand-card__meta"><span>{{ number_format($brand->active_products_count) }} محصول فعال</span><b class="brand-card__arrow">←</b></div></div>
</a>
@endforeach
</div>
@else<div class="empty-state"><h2>هنوز برندی فعال نشده.</h2></div>@endif
</div></section>
@endsection
