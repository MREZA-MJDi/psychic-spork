@extends('layouts.store')

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">BRAND / {{ strtoupper($brand->slug) }}</span><h1>{{ $brand->name }}</h1><p>{{ $brand->description ?: 'معرفی برند و محصولات مرتبط.' }}</p></div></section>
<section class="section-block"><div class="container"><div class="brand-detail">
<div class="brand-detail__logo">@if($brand->logoMedia?->url)<img src="{{ $brand->logoMedia->url }}" alt="{{ $brand->name }}">@else<span class="brand-card__fallback">{{ mb_substr($brand->name,0,1) }}</span>@endif</div>
<div class="brand-detail__body"><span class="eyebrow">ABOUT THE BRAND</span><h2>{{ $brand->name }}</h2><p>{{ $brand->description ?: 'معرفی این برند هنوز تکمیل نشده است.' }}</p><div class="brand-detail__meta"><span>{{ number_format($products->total()) }} محصول فعال</span></div><div class="split-callout__actions"><a class="button button--primary" href="{{ route('products.index',['brand'=>$brand->slug]) }}">محصولات این برند</a><a class="button button--ghost" href="{{ route('brands.index') }}">همه برندها</a></div></div>
</div></div></section>
@if($products->isNotEmpty())<section class="section-block section-block--soft"><div class="container"><div class="product-grid product-grid--related">@foreach($products as $product)<x-store.product-card :product="$product" />@endforeach</div><div class="store-pagination">{{ $products->links() }}</div></div></section>@endif
@endsection
