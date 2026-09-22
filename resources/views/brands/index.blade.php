@extends('layouts.store')

@section('title','برندها — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium">
    <div class="container page-hero__layout">
        <div><span class="eyebrow">02 / HOUSES</span><h1>برندها</h1><p>نام‌هایی که در جهان جانان انتخاب شده‌اند؛ هرکدام با زبان طراحی و شخصیت خودشان.</p></div>
        <div class="page-hero__stat"><b>{{ number_format($brands->count()) }}</b><span>برند فعال</span></div>
    </div>
</section>
<section class="section-block brand-stage"><div class="container">
@if($brands->isNotEmpty())
<div class="brand-mosaic">
@foreach($brands as $brand)
<a class="brand-mosaic__item" href="{{ route('brands.show',$brand) }}">
<div class="brand-mosaic__top"><span>{{ sprintf('%02d', $loop->iteration) }}</span><span>BRAND / {{ strtoupper($brand->slug) }}</span></div>
<div class="brand-mosaic__logo">@if($brand->logoMedia?->url)<img src="{{ $brand->logoMedia->url }}" alt="{{ $brand->name }}" loading="lazy">@else<span>{{ mb_substr($brand->name,0,1) }}</span>@endif</div>
<div class="brand-mosaic__bottom"><div><h2>{{ $brand->name }}</h2><p>{{ $brand->description ?: 'معرفی این برند هنوز تکمیل نشده است.' }}</p></div><div class="brand-mosaic__meta"><small>{{ number_format($brand->active_products_count) }} محصول</small><b>↗</b></div></div>
</a>
@endforeach
</div>
@else<div class="empty-state"><h2>هنوز برندی فعال نشده.</h2></div>@endif
</div></section>
@endsection
