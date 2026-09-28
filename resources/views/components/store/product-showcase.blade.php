@props([
    'products',
])

@php
    $products = collect($products);
    $featured = $products->first();
    $sideProducts = $products->slice(1, 4)->values();
    $railProducts = $products->slice(5)->values();
@endphp

<section class="home-product-showcase" aria-labelledby="home-products-title">
    <div class="container">

        <header class="store-section-heading">
            <div>
                <span class="eyebrow">JANAN / EDIT 03</span>
                <h2 id="home-products-title">محصولات منتخب</h2>
                <p>چهار انتخاب اصلی، بعد یک مسیر روان برای دیدن بقیه‌ی کالکشن.</p>
            </div>

            <a href="{{ route('products.index') }}" class="text-link">
                همه محصولات
                <span aria-hidden="true">↗</span>
            </a>
        </header>

        @if($featured)
            <div class="home-product-stage">

                <article class="home-product-stage__feature">
                    <div class="home-product-stage__feature-media">
                        @if($featured->galleryMedia->first()?->url)
                            <img
                                src="{{ $featured->galleryMedia->first()->url }}"
                                alt="{{ $featured->name }}"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <div class="home-product-stage__placeholder">
                                <span>JANAN</span>
                            </div>
                        @endif

                        @php
                            $variant = $featured->variants->first();
                        @endphp

                        @if($variant?->is_on_sale)
                            <span class="home-product-stage__sale">
                                {{ max(1, round((1 - ($variant->effective_price / max(1, (float) $variant->price))) * 100)) }}٪
                            </span>
                        @endif
                    </div>

                    <div class="home-product-stage__feature-copy">
                        <div>
                            <span class="home-product-stage__eyebrow">
                                {{ $featured->category?->name ?? 'JANAN' }}
                            </span>

                            <h3>{{ $featured->name }}</h3>

                            @if($featured->short_description)
                                <p>{{ $featured->short_description }}</p>
                            @endif
                        </div>

                        <div class="home-product-stage__feature-bottom">
                            @if($variant)
                                <div class="home-product-stage__price">
                                    <strong>
                                        {{ number_format($variant->effective_price) }}
                                    </strong>
                                    <span>تومان</span>

                                    <small class="{{ $variant->stock > 0 ? '' : 'is-out' }}">
                                        {{ $variant->stock > 0 ? 'موجود' : 'ناموجود' }}
                                    </small>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('cart.store', $variant) }}"
                                    class="quick-add-form home-product-stage__add"
                                    data-cart-add
                                >
                                    @csrf
                                    <input type="hidden" name="quantity" value="1">

                                    <button
                                        class="button button--dark"
                                        type="submit"
                                        @disabled($variant->stock < 1)
                                    >
                                        افزودن به سبد
                                    </button>
                                </form>
                            @endif

                            <a
                                href="{{ route('products.show', $featured) }}"
                                class="home-product-stage__detail"
                            >
                                جزئیات
                                <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </div>
                </article>

                <div class="home-product-stage__grid">
                    @foreach($sideProducts as $product)
                        <x-store.product-card
                            :product="$product"
                            variant="compact"
                        />
                    @endforeach
                </div>

            </div>

            @if($railProducts->isNotEmpty())
                <div class="home-product-rail-wrap">
                    <div class="home-product-rail__label">
                        <span>MORE FROM THE EDIT</span>
                        <span>{{ $railProducts->count() }} انتخاب دیگر</span>
                    </div>

                    <div class="home-product-rail">
                        @foreach($railProducts as $product)
                            <x-store.product-card
                                :product="$product"
                                variant="rail"
                            />
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <div class="empty-state">
                <span class="eyebrow">JANAN COLLECTION</span>
                <h2>هنوز محصول فعالی برای نمایش وجود ندارد.</h2>
                <a href="{{ route('products.index') }}" class="button button--primary">
                    مشاهده محصولات
                </a>
            </div>
        @endif

    </div>
</section>
