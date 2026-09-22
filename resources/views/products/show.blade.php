@extends('layouts.store')

@section('content')
@php($variant = $product->variants->first())

<section class="page-hero page-hero--motion">
    <div class="container"><span class="eyebrow">{{ $product->category?->name ?: 'JANAN' }}</span><h1>{{ $product->name }}</h1><p>{{ $product->short_description ?: ($product->category?->name ?: 'کالکشن جانان') }}</p></div>
</section>

<section class="section-block">
    <div class="container">
        <div class="product-detail">
            <div class="product-detail__media">
                @if($product->galleryMedia->first()?->url)
                    <img src="{{ $product->galleryMedia->first()->url }}" alt="{{ $product->name }}">
                @else
                    <div class="product-image-placeholder"><span>{{ $product->name }}</span></div>
                @endif
            </div>

            <div class="product-detail__body">
                @if($product->brand)<a class="eyebrow product-brand-link" href="{{ route('brands.show',$product->brand) }}">{{ $product->brand->name }}</a>@endif
                @if($variant)<span class="eyebrow">{{ $variant->sku }}</span>@endif
                <h2>{{ $product->name }}</h2>
                @if($product->description)<p>{{ $product->description }}</p>@endif

                @if($variant)
                    <div class="detail-price"><strong>{{ number_format($variant->effective_price) }}</strong><span>تومان</span>@if($variant->is_on_sale)<del>{{ number_format($variant->price) }}</del>@endif</div>
                    <div class="detail-stock {{ $variant->stock<1?'is-out':($variant->is_low_stock?'is-low':'') }}">{{ $variant->stock<1?'ناموجود':($variant->is_low_stock?'موجودی محدود':'آماده ارسال') }}</div>
                    @if($variant->size || $variant->color)
                        <div class="detail-attributes">
                            @if($variant->size)<div><span>سایز</span><strong>{{ $variant->size }}</strong></div>@endif
                            @if($variant->color)<div><span>رنگ</span><strong>{{ $variant->color }}</strong></div>@endif
                        </div>
                    @endif
                    <form method="POST" action="{{ route('cart.store',$variant) }}"><div>@csrf</div><button type="submit" class="button button--primary detail-add-button" {{ $variant->stock<1?'disabled':'' }}>{{ $variant->stock>0?'افزودن به سبد':'ناموجود' }}</button></form>
                @endif

                @if($product->attributes)
                    <div class="detail-attributes">@foreach($product->attributes as $key=>$values)<div><span>{{ $key }}</span><strong>{{ is_array($values)?implode(' · ',$values):$values }}</strong></div>@endforeach</div>
                @endif
            </div>
        </div>
    </div>
</section>

@if($relatedProducts->isNotEmpty())
<section class="section-block section-block--soft">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">YOU MAY ALSO LIKE</span><h2>انتخاب‌های مشابه</h2></div></div>
        <div class="product-grid product-grid--related">@foreach($relatedProducts as $relatedProduct)<x-store.product-card :product="$relatedProduct" />@endforeach</div>
    </div>
</section>
@endif
@endsection
