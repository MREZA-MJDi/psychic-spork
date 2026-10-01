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
                        </div>
                    </div>
                </div>

                <aside class="customer-surface wholesale-hero__side wholesale-status">
                    <span class="wholesale-status__label">CURRENT STATUS</span>

                    @if(session('success'))
                        <div class="alert alert--success" role="status">{{ session('success') }}</div>
                    @endif

                    @if($profile?->isApproved())
                        <div class="wholesale-status__state">مجاز برای خرید</div>
                        <div class="wholesale-status__hint">
                            خرید عمده آنلاین برای این حساب باز است. مجوز چک، در صورت وجود، جداگانه بررسی می‌شود.
                        </div>
                    @elseif($profile?->status === 'pending')
                        <div class="wholesale-status__state">خرید آنلاین آزاد</div>
                        <div class="wholesale-status__hint">
                            وضعیت درخواست قبلی شما مانع سفارش عمده آنلاین نیست؛ فقط پرداخت چکی نیازمند مجوز است.
                        </div>
                    @elseif($profile?->status === 'suspended')
                        <div class="wholesale-status__state">خرید آنلاین آزاد</div>
                        <div class="wholesale-status__hint">
                            حتی با وضعیت غیر‌فعال پروفایل عمده، سفارش آنلاین قابل ثبت است؛ مجوز چک مستقل است.
                        </div>
                    @else
                        <div class="wholesale-status__state">عمومی / آماده خرید</div>
                        <div class="wholesale-status__hint">
                            می‌توانی همین حالا کاتالوگ را ببینی و سفارش عمده را با پرداخت آنلاین ثبت کنی.
                        </div>
                    @endif
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
                                        <div>
                                            <strong>{{ $v?->product?->name ?? 'محصول' }}</strong>
                                            <span>
                                                {{ $v?->product?->brand?->name ?? 'بدون برند' }}
                                                · {{ $v?->display_name ?? 'Variant' }}
                                                · SKU {{ $v?->sku ?? '—' }}
                                            </span>
                                        </div>
                                        <b>× {{ number_format($item->quantity) }}</b>
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
                                @if($product->primaryGalleryMedia?->url)
                                    <img src="{{ $product->primaryGalleryMedia->url }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <span>JANAN</span>
                                @endif
                            </a>

                            <div class="wholesale-product-card__body">
                                <div class="wholesale-product-card__meta">
                                    <span>{{ $product->brand?->name ?? 'Janan' }}</span>
                                    <span>{{ $product->category?->name ?? 'محصول' }}</span>
                                </div>
                                <a href="{{ route('products.show', $product) }}" class="wholesale-product-card__title">{{ $product->name }}</a>

                                <div class="wholesale-variant-list">
                                    @foreach($product->activeVariants as $variant)
                                        <div class="wholesale-variant-row">
                                            <div>
                                                <strong>{{ $variant->display_name }}</strong>
                                                <span>SKU {{ $variant->sku ?: '—' }} · موجودی {{ number_format($variant->stock) }}</span>
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

    <section class="section-block section-block--soft" id="wholesale-form">
        <div class="container wholesale-content">
            <section class="customer-surface wholesale-form-card">
                <header class="section-head">
                    <div>
                        <span class="eyebrow">BUSINESS PROFILE</span>
                        <h2>{{ $profile ? 'اطلاعات پروفایل عمده' : 'خرید عمده و مجوزهای پرداخت' }}</h2>
                        <p>
                            سفارش آنلاین نیاز به تأیید عمده ندارد؛ فرم کسب‌وکار فقط برای ثبت یا تکمیل پروفایل شماست.
                        </p>
                    </div>
                </header>

                @if($profile?->isApproved())
                    <div class="customer-action-strip">
                        <div class="customer-action-strip__copy">
                            <small>WHOLESALE ACCOUNT</small>
                            <strong>حساب شما آماده سفارش عمده است.</strong>
                        </div>
                        <div class="customer-action-strip__actions">
                            <a class="button button--primary" href="{{ route('products.index') }}">انتخاب محصولات</a>
                            <a class="button button--ghost" href="{{ route('checkout') }}">رفتن به تسویه</a>
                        </div>
                    </div>
                @elseif($profile?->status === 'pending')
                    <div class="customer-inline-status">
                        <strong>درخواست شما قبلاً ثبت شده است.</strong>
                        <p>
                            تا مشخص شدن نتیجه، ارسال دوباره درخواست لازم نیست.
                        </p>
                    </div>
                @else
                    @auth
                    <form method="POST" action="{{ route('wholesale.apply') }}" class="checkout-form">
                        @csrf

                        <div class="form-grid">
                            <label>
                                نام فروشگاه یا مجموعه *
                                <input name="business_name" value="{{ old('business_name', $profile?->business_name) }}" required autocomplete="organization">
                            </label>

                            <label>
                                نوع فعالیت
                                <input name="business_type" value="{{ old('business_type', $profile?->business_type) }}">
                            </label>

                            <label>
                                شماره تماس کاری
                                <input name="business_phone" value="{{ old('business_phone', $profile?->business_phone) }}" inputmode="tel" autocomplete="tel">
                            </label>

                            <label class="form-grid__full">
                                آدرس کاری
                                <textarea name="business_address" rows="4">{{ old('business_address', $profile?->business_address) }}</textarea>
                            </label>
                        </div>

                        <button class="button button--primary" type="submit">
                            ارسال درخواست خرید عمده
                            <span aria-hidden="true">↗</span>
                        </button>
                    </form>
                    @else
                        <div class="customer-inline-status">
                            <strong>برای ثبت یا ویرایش پروفایل کسب‌وکار وارد شو.</strong>
                            <p>برای دیدن کاتالوگ و خرید عمده آنلاین نیازی به این فرم نداری.</p>
                            <div class="customer-action-strip__actions">
                                <a class="button button--primary" href="{{ route('login') }}">ورود</a>
                                <a class="button button--ghost" href="{{ route('register') }}">ساخت حساب</a>
                            </div>
                        </div>
                    @endauth
                @endif
            </section>

            <aside class="customer-surface wholesale-process">
                <span class="eyebrow">HOW IT WORKS</span>
                <h2 class="customer-panel-title">مسیر خرید عمده</h2>

                <ol>
                    <li>
                        <span>01</span>
                        <div>
                            <strong>انتخاب محصول</strong>
                            <p>از کاتالوگ، واریانت‌های دارای قیمت عمده را انتخاب کن.</p>
                        </div>
                    </li>
                    <li>
                        <span>02</span>
                        <div>
                            <strong>انتخاب خرید عمده</strong>
                            <p>در checkout نوع سفارش را روی خرید عمده بگذار.</p>
                        </div>
                    </li>
                    <li>
                        <span>03</span>
                        <div>
                            <strong>پرداخت آنلاین</strong>
                            <p>بدون تأیید قبلی پروفایل، سفارش را از درگاه پرداخت کن.</p>
                        </div>
                    </li>
                    <li>
                        <span>04</span>
                        <div>
                            <strong>پرداخت چکی</strong>
                            <p>فقط این روش به تأیید مدیریت و مجوز فعال همان حساب نیاز دارد.</p>
                        </div>
                    </li>
                </ol>
            </aside>
        </div>
    </section>

</div>
@endsection
