@php
    $brandName = $siteBrandNameFa ?? 'جانه جانان';
    $trust = config('app.trust', []);
@endphp

<footer class="store-footer">
    <div class="container">
        <div class="store-footer__main">
            <div class="store-footer__identity">
                <a href="{{ route('home') }}" class="store-footer__wordmark">{{ $brandName }}</a>
                @if(filled($siteFooterText ?? null))
                    <p>{{ $siteFooterText }}</p>
                @endif
                <div class="store-footer__actions">
                    <a class="store-footer__account" href="{{ auth()->check() ? route('account') : route('login') }}">
                        {{ auth()->check() ? 'حساب کاربری' : 'ورود به حساب' }}
                    </a>
                    <a href="{{ route('cart') }}" class="store-footer__icon" aria-label="سبد خرید">
                        <x-store.icon name="basket" />
                    </a>
                    @if(filled($siteStorePhone ?? null))
                        <a href="tel:{{ preg_replace('/\s+/', '', $siteStorePhone) }}" class="store-footer__icon" aria-label="تماس تلفنی با جانان">
                            <x-store.icon name="phone" />
                        </a>
                    @endif
                </div>
            </div>

            <nav aria-labelledby="footer-shop-heading">
                <h2 id="footer-shop-heading">خرید از جانان</h2>
                <ul>
                    <li><a href="{{ route('products.index') }}">محصولات</a></li>
                    <li><a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a></li>
                    <li><a href="{{ route('wholesale.show') }}">خرید عمده</a></li>
                </ul>
            </nav>

            <nav aria-labelledby="footer-help-heading">
                <h2 id="footer-help-heading">راهنمای سفارش</h2>
                <ul>
                    <li><a href="{{ route('shipping') }}">روش‌های ارسال</a></li>
                    <li><a href="{{ route('returns') }}">شرایط مرجوعی</a></li>
                    <li><a href="{{ route('faq') }}">سؤالات متداول</a></li>
                </ul>
            </nav>

            <section aria-labelledby="footer-contact-heading">
                <h2 id="footer-contact-heading">ارتباط با جانان</h2>
                <ul>
                    <li><a href="{{ route('about') }}#contact">فرم تماس با پشتیبانی</a></li>
                    @if(filled($siteStoreEmail ?? null))
                        <li><a href="mailto:{{ $siteStoreEmail }}"><bdi>{{ $siteStoreEmail }}</bdi></a></li>
                    @endif
                    @if(filled($siteStoreAddress ?? null))
                        <li>{{ $siteStoreAddress }}</li>
                    @endif
                    @if(!empty($trust['social_instagram']))
                        <li><a href="{{ $trust['social_instagram'] }}" target="_blank" rel="noopener noreferrer">اینستاگرام</a></li>
                    @endif
                    @if(!empty($trust['social_telegram']))
                        <li><a href="{{ $trust['social_telegram'] }}" target="_blank" rel="noopener noreferrer">تلگرام</a></li>
                    @endif
                </ul>
            </section>
        </div>

        <div class="store-footer__bottom">
            <p>© {{ \App\Support\PersianNumber::digits(date('Y')) }} {{ $brandName }}. تمامی حقوق محفوظ است.</p>
            @if(!empty($trust['enamad_url']) || !empty($trust['license_url']))
                <div class="store-footer__credentials">
                    @if(!empty($trust['enamad_url']))
                        <a href="{{ $trust['enamad_url'] }}" target="_blank" rel="noopener noreferrer">
                            @if(!empty($trust['enamad_image']))
                                <img src="{{ $trust['enamad_image'] }}" alt="نماد اعتماد الکترونیکی" loading="lazy">
                            @else
                                نماد اعتماد الکترونیکی
                            @endif
                        </a>
                    @endif
                    @if(!empty($trust['license_url']))
                        <a href="{{ $trust['license_url'] }}" target="_blank" rel="noopener noreferrer">مجوزهای فروشگاه</a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</footer>
