@props(['products' => collect()])

@php
    $picks = collect($products)->filter(fn ($product) => $product?->primaryActiveVariant)->take(2);
@endphp

@if($picks->isNotEmpty())
    <section class="home-picks" aria-labelledby="home-discovery-title">
        <div class="container">
            <header class="home-picks__header">
                <h2 id="home-discovery-title">پیشنهاد جانان</h2>
                <a class="janan-widget-link" href="{{ route('products.index') }}">همهٔ محصولات</a>
            </header>

            <div class="home-picks__grid">
                @foreach($picks as $product)
                    @php
                        $variant = $product->primaryActiveVariant;
                        $image = $product->primaryGalleryMedia?->url ?: $variant->primaryGalleryMedia?->url;
                    @endphp
                    <a class="home-picks__card" href="{{ route('products.show', $product) }}">
                        <div class="home-picks__image" aria-hidden="true">
                            <x-store.image :src="$image" alt="" fallback="جانان" />
                        </div>
                        <div class="home-picks__details">
                            <h3>{{ \App\Support\PersianNumber::digits($product->name) }}</h3>
                            <strong>{{ \App\Support\PersianNumber::format($variant->effective_price) }} تومان</strong>
                            <span class="home-picks__stock">{{ $variant->stock > 0 ? 'موجود' : 'ناموجود' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
