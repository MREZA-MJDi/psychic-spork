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
                            <a class="button button--ghost" href="{{ route('wholesale.catalog') }}">دیدن کاتالوگ عمده</a>
                            <a class="button button--primary" href="{{ route('wholesale.catalog') }}">شروع خرید عمده</a>
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
                    <h2>پک‌های آماده عمده</h2>
                    <p>بسته‌های آماده را ببین؛ برای جستجو و انتخاب محصولات تکی، کاتالوگ عمده جداگانه در دسترس است.</p>
                </div>
                <a class="button button--ghost" href="{{ route('wholesale.catalog') }}">مشاهده کاتالوگ محصولات عمده</a>
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
                            @php
                                $packAvailable = $pack->items->isNotEmpty() && $pack->items->every(
                                    fn ($item) => $item->variant
                                        && $item->variant->is_active
                                        && $item->variant->product?->is_active
                                        && $item->variant->stock >= $item->quantity
                                        && $item->variant->wholesale_price !== null
                                );
                            @endphp
                            <form method="POST" action="{{ route('cart.wholesale-packs.store', $pack) }}" class="wholesale-pack-card__purchase">
                                @csrf
                                <label>
                                    تعداد پک
                                    <input type="number" name="quantity" min="1" max="99" value="1" @disabled(!$packAvailable) aria-label="تعداد پک {{ $pack->name }}">
                                </label>
                                <button class="button button--primary" type="submit" @disabled(!$packAvailable)>
                                    {{ $packAvailable ? 'افزودن پک به سبد خرید' : 'موجودی کافی نیست' }}
                                </button>
                            </form>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="customer-surface wholesale-empty">
                    <strong>هنوز پک آماده‌ای برای خرید عمده ثبت نشده است.</strong>
                    <span>می‌توانی محصولات دارای قیمت عمده را در کاتالوگ جداگانه ببینی.</span>
                </div>
            @endif

            <div class="customer-surface wholesale-empty">
                <strong>دنبال محصول تکی یا تنوع خاصی هستی؟</strong>
                <span>کاتالوگ محصولات عمده با جستجو، فیلتر و صفحه‌بندی مستقل در دسترس است.</span>
                <a class="button button--primary" href="{{ route('wholesale.catalog') }}">رفتن به کاتالوگ عمده</a>
            </div>
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
