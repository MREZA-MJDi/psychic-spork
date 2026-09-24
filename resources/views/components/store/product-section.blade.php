<section class="section-block section-block--products section-block--soft">
    <div class="container">

        <header class="section-head">
            <div>
                <span class="eyebrow">
                    JANAN EDIT
                </span>

                <h2>
                    محصولات ویژه
                </h2>

                <p>
                    انتخابی از محصولات فعال و ویژه جانان.
                </p>
            </div>

            <a
                href="{{ route('products.index') }}"
                class="text-link"
            >
                مشاهده همه
                <span aria-hidden="true">←</span>
            </a>
        </header>


        @if($products->isNotEmpty())

            <div class="product-grid">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>

        @else

            <div class="empty-state">

                <span class="eyebrow">
                    JANAN COLLECTION
                </span>

                <h2>
                    هنوز محصول فعالی برای نمایش وجود ندارد.
                </h2>

                <p>
                    اولین محصول فعال فروشگاه را از پنل مدیریت ایجاد کن.
                </p>

                <a
                    href="{{ route('products.index') }}"
                    class="button button--primary"
                >
                    مشاهده محصولات
                </a>

            </div>

        @endif

    </div>
</section>
