@extends('layouts.store')

@section('title', 'خرید عمده — ' . ($siteBrandNameLatin ?? 'Janan'))

@section('content')
<div class="wholesale-page">

    <section class="section-block section-block--compact">
        <div class="container">
            <div class="wholesale-hero">
                <div class="customer-surface wholesale-hero__main">
                    <span class="eyebrow">JANAN / WHOLESALE</span>
                    <h1>برای خرید عمده، یک مسیر حرفه‌ای داشته باش.</h1>
                    <p>
                        مسیر خرید عمده جانان برای سفارش آنلاین باز است و برای قیمت عمده لازم نیست منتظر تأیید مدیریت بمانی.
                        تنها پرداخت چکی یک مجوز جداگانه روی حساب کاربری می‌خواهد.
                    </p>

                    <div class="customer-action-strip customer-action-strip--spaced-top">
                        <div class="customer-action-strip__copy">
                            <small>YOUR BUSINESS ACCESS</small>
                            <strong>خرید عمده آنلاین برای همه باز است.</strong>
                        </div>
                        <div class="customer-action-strip__actions">
                            <a class="button button--ghost" href="{{ route('products.index') }}">دیدن کاتالوگ</a>
                            <a class="button button--primary" href="{{ route('products.index') }}">شروع خرید عمده</a>
                            <a class="button button--ghost" href="#cheque-application">درخواست خرید چکی</a>
                        </div>
                    </div>
                </div>

                <aside class="customer-surface wholesale-hero__side wholesale-status">
                    <span class="wholesale-status__label">CURRENT STATUS</span>

                    @if(session('success'))
                        <div class="alert alert--success" role="status">{{ session('success') }}</div>
                    @endif

                    <div class="wholesale-status__state">خرید آنلاین عمده باز است</div>
                    <div class="wholesale-status__hint">
                        قیمت عمده برای همه قابل استفاده است؛ فقط پرداخت چکی به حساب مشتری و تأیید مدیر نیاز دارد.
                    </div>
                </aside>
            </div>

            <div class="wholesale-benefit-grid">
                <article class="wholesale-benefit">
                    <span>01</span>
                    <div>
                        <strong>قیمت عمده</strong>
                        <p>قیمت عمده روی واریانت‌هایی اعمال می‌شود که قیمت عمده برایشان ثبت شده است.</p>
                    </div>
                </article>

                <article class="wholesale-benefit">
                    <span>02</span>
                    <div>
                        <strong>سفارش آزاد</strong>
                        <p>برای خرید آنلاین عمده، تأیید قبلی پروفایل مانع شروع سفارش نیست.</p>
                    </div>
                </article>

                <article class="wholesale-benefit">
                    <span>03</span>
                    <div>
                        <strong>چک با مجوز</strong>
                        <p>فقط پرداخت چکی به مجوز فعال مدیر برای همان حساب کاربری وابسته است.</p>
                    </div>
                </article>

                <article class="wholesale-benefit">
                    <span>04</span>
                    <div>
                        <strong>درگاه آنلاین</strong>
                        <p>سفارش عمده از همان checkout فروشگاه به پرداخت آنلاین می‌رود.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section-block wholesale-catalog" id="wholesale-catalog">
        <div class="container">
            <header class="wholesale-catalog__head">
                <div>
                    <span class="eyebrow">JANAN / WHOLESALE CATALOG</span>
                    <h2>محصولات و پک‌های عمده</h2>
                    <p>هر محصول با Variantهای واقعی خودش و قیمت عمده نمایش داده می‌شود.</p>
                </div>
                <a class="button button--ghost" href="{{ route('products.index') }}">کاتالوگ عادی</a>
            </header>

            @if($packs->isNotEmpty())
                <div class="wholesale-pack-grid">
                    @foreach($packs as $pack)
                        <article class="wholesale-pack-card">
                            @php
                                $firstPackVariant = $pack->items->first()?->variant;
                                $packImage = $pack->image_path
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($pack->image_path)
                                    : ($firstPackVariant?->primaryGalleryMedia?->url ?? $firstPackVariant?->product?->primaryGalleryMedia?->url);
                            @endphp
                            <div class="wholesale-pack-card__cover">
                                @if($packImage)
                                    <img src="{{ $packImage }}" alt="{{ $pack->image_path ? $pack->name : 'تصویر نمونه از اقلام ' . $pack->name }}" loading="lazy">
                                    @unless($pack->image_path)<span class="wholesale-pack-card__cover-note">تصویر نمونه از اقلام پک</span>@endunless
                                @else
                                    <div class="wholesale-pack-card__cover-empty">برای این بسته هنوز عکسی ثبت نشده</div>
                                @endif
                            </div>
                            <div class="wholesale-pack-card__top">
                                <div>
                                    <span class="wholesale-pack-card__eyebrow">WHOLESALE PACK</span>
                                    <h3>{{ $pack->name }}</h3>
                                </div>
                                <strong>{{ number_format($pack->pack_quantity) }} عدد</strong>
                            </div>

                            @if($pack->description)
                                <p class="wholesale-pack-card__description">{{ $pack->description }}</p>
                            @endif

                            <div class="wholesale-pack-card__items">
                                @foreach($pack->items as $item)
                                    @php $v = $item->variant; @endphp
                                    <a href="{{ $v?->product ? route('products.show', $v->product) : '#' }}" class="wholesale-pack-item">
                                        <span class="wholesale-pack-item__media">
                                            <x-store.image
                                                :src="$v?->primaryGalleryMedia?->url ?? $v?->product?->primaryGalleryMedia?->url"
                                                :alt="$v?->display_name ?? $v?->product?->name ?? 'واریانت پک عمده'"
                                                fallback-tag="span"
                                                fallback-class=""
                                                fallback="JANAN"
                                            />
                                        </span>
                                        <span class="wholesale-pack-item__copy">
                                            <strong>{{ $v?->product?->name ?? 'محصول' }}</strong>
                                            <span>{{ $v?->product?->brand?->name ?? 'بدون برند' }} · {{ $v?->display_name ?? 'واریانت' }}</span>
                                            <small>SKU {{ $v?->sku ?? '—' }} · {{ $v?->stock !== null ? 'موجودی '.number_format($v->stock) : 'موجودی نامشخص' }}</small>
                                        </span>
                                        <b class="wholesale-pack-item__quantity">× {{ number_format($item->quantity) }}</b>
                                    </a>
                                @endforeach
                            </div>

                            <div class="wholesale-pack-card__foot">
                                <span>قیمت پک عمده</span>
                                <strong>{{ number_format($pack->display_price) }} تومان</strong>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="wholesale-products-head">
                <div>
                    <span class="eyebrow">WHOLESALE PRODUCTS</span>
                    <h3>محصولات دارای قیمت عمده</h3>
                </div>
                <span>{{ number_format($products->total()) }} محصول</span>
            </div>

            @if($products->count())
                <div class="wholesale-product-grid">
                    @foreach($products as $product)
                        <article class="wholesale-product-card">
                            <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__media">
                                <x-store.image
                                    :src="$product->primaryGalleryMedia?->url"
                                    :alt="$product->name"
                                    fallback-tag="span"
                                    fallback-class=""
                                    fallback="JANAN"
                                />
                            </a>

                            <div class="wholesale-product-card__body">
                                <div class="wholesale-product-card__meta">
                                    <span>{{ $product->brand?->name ?? 'Janan' }}</span>
                                    <span>{{ $product->category?->name ?? 'محصول' }}</span>
                                </div>
                                @php
                                    $wholesaleColorCount = $product->activeVariants
                                        ->pluck('color')->filter(fn ($value) => filled(trim((string) $value)))
                                        ->map(fn ($value) => mb_strtolower(trim((string) $value)))->unique()->count();
                                    $wholesaleSizeCount = $product->activeVariants
                                        ->pluck('size')->filter(fn ($value) => filled(trim((string) $value)))
                                        ->map(fn ($value) => mb_strtolower(trim((string) $value)))->unique()->count();
                                @endphp
                                <div class="wholesale-product-card__facts" aria-label="{{ $wholesaleColorCount }} رنگ، {{ $wholesaleSizeCount }} سایز">
                                    <span>{{ $wholesaleColorCount > 0 ? number_format($wholesaleColorCount).' رنگ' : 'رنگ نامشخص' }}</span>
                                    <span>{{ $wholesaleSizeCount > 0 ? number_format($wholesaleSizeCount).' سایز' : 'سایز نامشخص' }}</span>
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__title">{{ $product->name }}</a>

                                <div class="wholesale-variant-list">
                                    @foreach($product->activeVariants as $variant)
                                        <div class="wholesale-variant-row">
                                            <div>
                                                <strong>{{ $variant->display_name }}</strong>
                                                <span>موجودی {{ number_format($variant->stock) }}</span>
                                            </div>
                                            <div class="wholesale-variant-price">
                                                {{ number_format($variant->wholesale_price) }}
                                                <small>تومان</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <a class="button button--ghost wholesale-product-card__action" href="{{ route('products.show', $product) }}">
                                    انتخاب Variant
                                    <span aria-hidden="true">↗</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($products->hasPages())
                    <div class="store-pagination">{{ $products->links() }}</div>
                @endif
            @else
                <div class="customer-surface wholesale-empty">
                    <strong>هنوز محصولی با قیمت عمده ثبت نشده است.</strong>
                    <span>از مدیریت محصول، برای Variantهای قابل فروش قیمت عمده تعیین کن.</span>
                </div>
            @endif
        </div>
    </section>

    <section class="section-block section-block--soft" id="cheque-application">
        <div class="container wholesale-content">
            <aside class="customer-surface wholesale-process">
                <span class="eyebrow">HOW IT WORKS</span>
                <h2 class="customer-panel-title">مسیر خرید عمده</h2>
                <ol>
                    <li><span>01</span><div><strong>انتخاب محصول</strong><p>واریانت‌هایی را انتخاب کن که قیمت عمده دارند.</p></div></li>
                    <li><span>02</span><div><strong>پرداخت آنلاین</strong><p>خرید عمده آنلاین برای همه باز است و تأیید حساب نمی‌خواهد.</p></div></li>
                    <li><span>03</span><div><strong>درخواست پرداخت چکی</strong><p>برای درخواست، با حساب مشتری وارد شو و مبلغ اعتبار مدنظرت را ثبت کن.</p></div></li>
                    <li><span>04</span><div><strong>تأیید مدیر</strong><p>مدیر درخواست را بررسی و سقف مجاز هر سفارش را تعیین می‌کند.</p></div></li>
                </ol>
            </aside>

            <section class="customer-surface wholesale-form-card" aria-labelledby="cheque-application-title">
                <header class="section-head">
                    <div>
                        <span class="eyebrow">CHEQUE PAYMENT</span>
                        <h2 id="cheque-application-title">درخواست اعتبار خرید چکی</h2>
                        <p>خرید آنلاین عمده برای همه باز است؛ این درخواست فقط برای فعال‌شدن پرداخت چکی است.</p>
                    </div>
                </header>

                @if(session('success'))
                    <div class="alert alert--success" role="status">{{ session('success') }}</div>
                @endif

                @if($chequePermission?->isApproved())
                    <div class="customer-inline-status">
                        <strong>مجوز پرداخت چکی فعال است.</strong>
                        <p>سقف هر سفارش: {{ $chequePermission->max_order_amount !== null ? number_format((float) $chequePermission->max_order_amount) . ' تومان' : 'بدون سقف تعیین‌شده' }}.</p>
                        <a class="button button--primary" href="{{ route('checkout') }}#payment-options">رفتن به پرداخت</a>
                    </div>
                @elseif($chequePermission?->isPending())
                    <div class="customer-inline-status" role="status">
                        <strong>درخواستت در انتظار بررسی مدیر است.</strong>
                        <p>مبلغ درخواستی: {{ number_format((float) $chequePermission->requested_amount) }} تومان.</p>
                    </div>
                @elseif($isCustomer)
                    <form method="POST" action="{{ route('wholesale.cheque.request') }}" class="checkout-form cheque-request-form">
                        @csrf
                        <label for="cheque-request-amount">مبلغ اعتبار درخواستی (تومان)</label>
                        <div class="cheque-request-form__amount">
                            <input id="cheque-request-amount" name="requested_amount" type="text" inputmode="numeric" autocomplete="off" required maxlength="20" value="{{ old('requested_amount') }}" placeholder="مثلاً ۵۰٬۰۰۰٬۰۰۰" data-money-input aria-describedby="cheque-request-amount-help">
                            <span>تومان</span>
                        </div>
                        <small id="cheque-request-amount-help">این مبلغ پیشنهاد توست؛ سقف نهایی را مدیر تأیید می‌کند.</small>
                        @error('requested_amount')<small class="form-error" role="alert">{{ $message }}</small>@enderror
                        <button class="button button--primary" type="submit">ارسال درخواست به مدیر</button>
                    </form>
                @elseif(auth()->check())
                    <div class="customer-inline-status"><strong>این درخواست برای حساب مشتری است.</strong><p>با حساب مدیریت امکان ثبت درخواست پرداخت چکی وجود ندارد.</p></div>
                @else
                    <div class="customer-inline-status">
                        <strong>برای پرداخت چکی، حساب مشتری بساز یا وارد شو.</strong>
                        <p>ثبت و تأیید درخواست فقط برای پرداخت چکی است؛ خرید آنلاین عمده نیازی به حساب ندارد.</p>
                        <div class="customer-action-strip__actions">
                            <a class="button button--primary" href="{{ route('login', ['continue' => 'cheque']) }}">ورود</a>
                            <a class="button button--ghost" href="{{ route('register', ['continue' => 'cheque']) }}">ساخت حساب</a>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </section>
</div>
@endsection
