@extends('layouts.store')

@section('title', $brand->name . ' — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium page-hero--brand">
<div class="container brand-hero">
<div class="brand-hero__logo">@if($brand->logoMedia?->url)<img src="{{ $brand->logoMedia->url }}" alt="{{ $brand->name }}">@else<span>{{ mb_substr($brand->name,0,1) }}</span>@endif</div>
<div class="brand-hero__copy"><a class="page-kicker" href="{{ route('brands.index') }}">↖ بازگشت به برندها</a><span class="eyebrow">BRAND / {{ strtoupper($brand->slug) }}</span><h1>{{ $brand->name }}</h1><p>{{ $brand->description ?: 'معرفی برند و محصولات مرتبط.' }}</p><div class="brand-hero__meta"><span>{{ number_format($products->total()) }} محصول فعال</span><i></i><span>JANAN SELECT</span></div></div>
</div>
</section>
<section class="section-block catalog-stage"><div class="container">
<div class="catalog-toolbar"><div><span class="eyebrow">SELECTED BY JANAN</span><strong>محصولات {{ $brand->name }}</strong></div><a class="text-link" href="{{ route('products.index',['brand'=>$brand->slug]) }}">مشاهده همه <span>↗</span></a></div>
@if($products->isNotEmpty())
<div class="product-grid product-grid--editorial">@foreach($products as $product)<x-store.product-card :product="$product" />@endforeach</div>
<div class="store-pagination">{{ $products->links() }}</div>
@else
<div class="empty-state"><span class="eyebrow">NO ACTIVE PRODUCTS</span><h2>در حال حاضر محصول فعالی از این برند نیست.</h2><a class="button button--primary" href="{{ route('brands.index') }}">همه برندها</a></div>
@endif
</div></section>
@endsection
