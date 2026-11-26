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
                    <x-store.icon-link
                        :href="auth()->check() ? route('account') : route('login')"
                        :icon="auth()->check() ? 'user' : 'login'"
                        :label="auth()->check() ? 'حساب کاربری' : 'ورود به حساب'"
                    />
                    <x-store.icon-link :href="route('cart')" icon="basket" label="سبد خرید" />
                    @if(filled($siteStorePhone ?? null))
                        <x-store.icon-link :href="'tel:' . preg_replace('/\s+/', '', $siteStorePhone)" icon="phone" label="تماس تلفنی با جانان" />
                    @endif
                </div>
            </div>

            <nav aria-labelledby="footer-shop-heading">
                <h2 id="footer-shop-heading">خرید از جانان</h2>
                <ul class="store-footer__icons">
                    <li><x-store.icon-link :href="route('products.index')" icon="bag" label="محصولات" /></li>
                    <li><x-store.icon-link :href="route('categories.index')" icon="grid" label="دسته‌بندی‌ها" /></li>
                    <li><x-store.icon-link :href="route('wholesale.show')" icon="layers" label="خرید عمده" /></li>
                </ul>
            </nav>

            <nav aria-labelledby="footer-help-heading">
                <h2 id="footer-help-heading">راهنمای سفارش</h2>
                <ul class="store-footer__icons">
                    <li><x-store.icon-link :href="route('shipping')" icon="truck" label="روش‌های ارسال" /></li>
                    <li><x-store.icon-link :href="route('returns')" icon="return" label="شرایط مرجوعی" /></li>
                    <li><x-store.icon-link :href="route('faq')" icon="help" label="سؤالات متداول" /></li>
                </ul>
            </nav>

            <section aria-labelledby="footer-contact-heading">
                <h2 id="footer-contact-heading">ارتباط با جانان</h2>
                <ul class="store-footer__icons">
                    <li><x-store.icon-link :href="route('about') . '#contact'" icon="message" label="فرم تماس با پشتیبانی" /></li>
                    @if(filled($siteStoreEmail ?? null))
                        <li><x-store.icon-link :href="'mailto:' . $siteStoreEmail" icon="mail" label="ایمیل پشتیبانی" /></li>
                    @endif
                    @if(!empty($trust['social_instagram']))
                        <li><x-store.icon-link :href="$trust['social_instagram']" icon="instagram" label="اینستاگرام" target="_blank" rel="noopener noreferrer" /></li>
                    @endif
                    @if(!empty($trust['social_telegram']))
                        <li><x-store.icon-link :href="$trust['social_telegram']" icon="telegram" label="تلگرام" target="_blank" rel="noopener noreferrer" /></li>
                    @endif
                </ul>
                @if(filled($siteStoreAddress ?? null))
                    <p class="store-footer__address">{{ $siteStoreAddress }}</p>
                @endif
            </section>
        </div>

        <div class="store-footer__bottom">
            <p>© {{ \App\Support\PersianNumber::digits(date('Y')) }} {{ $brandName }}. تمامی حقوق محفوظ است.</p>
            @if(!empty($trust['enamad_url']) || !empty($trust['license_url']))
                <div class="store-footer__credentials">
                    @if(!empty($trust['enamad_url']))
                        @if(!empty($trust['enamad_image']))
                            <a href="{{ $trust['enamad_url'] }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $trust['enamad_image'] }}" alt="نماد اعتماد الکترونیکی" loading="lazy">
                            </a>
                        @else
                            <x-store.icon-link :href="$trust['enamad_url']" icon="shield" label="نماد اعتماد الکترونیکی" target="_blank" rel="noopener noreferrer" />
                        @endif
                    @endif
                    @if(!empty($trust['license_url']))
                        <x-store.icon-link :href="$trust['license_url']" icon="document" label="مجوزهای فروشگاه" target="_blank" rel="noopener noreferrer" />
                    @endif
                </div>
            @endif
        </div>
    </div>
</footer>
