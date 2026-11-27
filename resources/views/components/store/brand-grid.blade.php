@props([
    'brands',
    'titleId' => 'home-brands-title',
])

@if($brands->isNotEmpty())
    <section class="home-brand-shop" aria-labelledby="{{ $titleId }}">
        <div class="container">
            <header class="home-brand-shop__head">
                <div class="home-brand-shop__intro">
                    <h2 id="{{ $titleId }}">خرید بر اساس برند</h2>
                    <p>برند دلخواهت را انتخاب کن و مدل‌ها، قیمت‌ها و موجودی آن را ببین.</p>
                </div>
                <a class="janan-widget-link" href="{{ route('products.index') }}">همه محصولات</a>
            </header>
            <ul class="home-brand-shop__grid">
                @foreach($brands as $brand)
                    <li>
                        <a class="home-brand-shop__card" href="{{ route('products.index', ['brand' => $brand->slug]) }}">
                            <div class="home-brand-shop__media" aria-hidden="true">
                                <x-store.image
                                    :src="$brand->logoMedia?->url"
                                    alt=""
                                    fallback-tag="span"
                                    fallback-class="home-brand-shop__fallback"
                                    :fallback="$brand->name"
                                />
                            </div>
                            <div class="home-brand-shop__info">
                                <h3 dir="auto">{{ $brand->name }}</h3>
                                <p>{{ \App\Support\PersianNumber::format($brand->active_products_count) }} محصول</p>
                                <span class="home-brand-shop__action">مشاهده محصولات</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
