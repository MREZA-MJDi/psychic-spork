<section class="section-block section-block--products section-block--soft">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">JANAN EDIT</span>
                <h2>محصولات ویژه</h2>
                <p>محصولات فعال و ویژه‌ای که در پنل مدیریت انتخاب شده‌اند.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-link">مشاهده همه <span>←</span></a>
        </div>

        @if($products->isNotEmpty())
            <div class="product-grid">
                @foreach($products as $product)
                    <x-store.product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <span class="eyebrow">JANAN COLLECTION</span>
                <h2>هنوز محصول فعالی برای نمایش وجود ندارد.</h2>
                <p>محصول را از پنل مدیریت بساز و فعال کن؛ Home به‌صورت خودکار به‌روزرسانی می‌شود.</p>
                <a class="button button--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
            </div>
        @endif
    </div>
</section>
