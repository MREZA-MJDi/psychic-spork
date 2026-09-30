<footer class="store-footer">

    <div class="container footer-top">

        {{-- =====================================================
             BRAND
        ====================================================== --}}

        <div class="footer-brand">

            <a
                href="{{ route('home') }}"
                class="brand brand--footer"
                aria-label="{{ $siteBrandNameLatin ?? 'Janan' }}"
            >
                <span class="brand__latin">
                    {{ $siteBrandNameLatin ?? 'Janan' }}
                </span>

                <span class="brand__fa">
                    {{ $siteBrandNameFa ?? 'جانان' }}
                </span>
            </a>

            <p class="footer-brand__text">
                {{ $siteFooterText ?: "فروشگاه آنلاین {$siteBrandNameLatin}؛ ترکیبی از کیفیت، زیبایی و انتخابی که برای شما طراحی شده است." }}            </p>

            <div
                class="footer-social"
                aria-label="دسترسی سریع"
            >
                <a href="{{ route('categories.index') }}">
                    <span>دسته‌ها</span>
                    <b aria-hidden="true">↗</b>
                </a>

                <a href="{{ route('cart') }}">
                    <span>سبد خرید</span>
                    <b aria-hidden="true">↗</b>
                </a>

                <a href="{{ route('about') }}#contact">
                    <span>درباره و تماس</span>
                    <b aria-hidden="true">↗</b>
                </a>

                <a href="{{ route('wholesale.show') }}">
                    <span>خرید عمده</span>
                    <b aria-hidden="true">↗</b>
                </a>
            </div>

        </div>


        {{-- =====================================================
             QUICK ACCESS
        ====================================================== --}}

        <div class="footer-col">

            <span class="footer-col__index">
                01
            </span>

            <h3>
                دسترسی سریع
            </h3>

            <nav aria-label="دسترسی سریع">

                <a href="{{ route('home') }}">
                    خانه
                </a>

                <a href="{{ route('products.index') }}">
                    محصولات
                </a>

                <a href="{{ route('categories.index') }}">
                    دسته‌بندی‌ها
                </a>

                <a href="{{ route('brands.index') }}">
                    برندها
                </a>

            </nav>

        </div>


        {{-- =====================================================
             CUSTOMER SERVICE
        ====================================================== --}}

        <div class="footer-col">

            <span class="footer-col__index">
                02
            </span>

            <h3>
                خدمات مشتری
            </h3>

            <nav aria-label="خدمات مشتری">

                <a href="{{ route('shipping') }}">
                    روش‌های ارسال
                </a>

                <a href="{{ route('returns') }}">
                    شرایط مرجوعی
                </a>

                <a href="{{ route('faq') }}">
                    سوالات متداول
                </a>

                <a href="{{ route('about') }}#contact">
                    درباره و تماس
                </a>

                <a href="{{ route('wholesale.show') }}">
                    خرید عمده
                </a>

            </nav>

        </div>


        {{-- =====================================================
             STORE CONTACT
        ====================================================== --}}

        <div class="footer-newsletter">

            <span class="footer-col__index">
                03
            </span>

            <h3>
                ارتباط با جانان
            </h3>

            <div class="footer-contact-list">

                @if($siteStorePhone)
                    <a
                        href="tel:{{ preg_replace('/\s+/', '', $siteStorePhone) }}"
                        class="footer-contact-line"
                        dir="ltr"
                    >
                        <small>PHONE</small>
                        <span>{{ $siteStorePhone }}</span>
                    </a>
                @endif

                @if($siteStoreEmail)
                    <a
                        href="mailto:{{ $siteStoreEmail }}"
                        class="footer-contact-line"
                        dir="ltr"
                    >
                        <small>EMAIL</small>
                        <span>{{ $siteStoreEmail }}</span>
                    </a>
                @endif

                @if($siteStoreAddress)
                    <span class="footer-contact-line">
                        <small>ADDRESS</small>
                        <span>{{ $siteStoreAddress }}</span>
                    </span>
                @endif

            </div>

            <a
                href="{{ auth()->check() ? route('account') : route('login') }}"
                class="footer-account-link"
            >
                <span>
                    {{ auth()->check() ? 'حساب جانان' : 'ورود به حساب' }}
                </span>

                <b aria-hidden="true">
                    ↗
                </b>
            </a>

        </div>

    </div>


    {{-- =====================================================
         TRUST / LEGAL
    ====================================================== --}}

    @php
        $trust = config('app.trust', []);
    @endphp

    <div class="container footer-trust">
        <div class="footer-trust__head">
            <div>
                <span class="footer-col__index">04</span>
                <h3>اعتبار، اطلاعات و مسیرهای رسمی</h3>
            </div>

            <p>
                اطلاعات قانونی و نشان‌های رسمی فروشگاه باید از مقادیر واقعی محیط production تأمین شوند.
                هیچ مجوز یا نمادی بدون اتصال اطلاعات معتبر نمایش داده نمی‌شود.
            </p>
        </div>

        <div class="footer-trust__grid">

            @if(!empty($trust['enamad_url']))
                <a
                    href="{{ $trust['enamad_url'] }}"
                    class="footer-trust__item"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <span class="footer-trust__label">VERIFIED TRUST</span>

                    @if(!empty($trust['enamad_image']))
                        <img
                            src="{{ $trust['enamad_image'] }}"
                            alt="نماد اعتماد الکترونیکی"
                            loading="lazy"
                        >
                    @endif

                    <strong>نماد اعتماد الکترونیکی</strong>
                    <span>مشاهده اطلاعات رسمی</span>
                </a>
            @else
                <div class="footer-trust__item">
                    <span class="footer-trust__label">VERIFIED TRUST</span>
                    <strong>نماد اعتماد</strong>
                    <span>پس از ثبت اطلاعات رسمی فعال می‌شود</span>
                </div>
            @endif

            @if(!empty($trust['license_url']))
                <a
                    href="{{ $trust['license_url'] }}"
                    class="footer-trust__item"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <span class="footer-trust__label">LEGAL</span>
                    <strong>مجوزها و اطلاعات قانونی</strong>
                    <span>مشاهده جزئیات رسمی</span>
                </a>
            @else
                <div class="footer-trust__item">
                    <span class="footer-trust__label">LEGAL</span>
                    <strong>مجوزها و اطلاعات قانونی</strong>
                    <span>اطلاعات رسمی از تنظیمات فروشگاه</span>
                </div>
            @endif

            <div class="footer-trust__item">
                <span class="footer-trust__label">SECURE CHECKOUT</span>
                <strong>پرداخت آنلاین</strong>
                <span>اتصال به درگاه پرداخت پیکربندی‌شده فروشگاه</span>
            </div>

            <div class="footer-trust__item">
                <span class="footer-trust__label">SOCIAL / SUPPORT</span>
                <strong>شبکه‌های اجتماعی</strong>

                <span>
                    @if(!empty($trust['social_instagram']))
                        <a href="{{ $trust['social_instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                    @endif

                    @if(!empty($trust['social_instagram']) && !empty($trust['social_telegram']))
                        ·
                    @endif

                    @if(!empty($trust['social_telegram']))
                        <a href="{{ $trust['social_telegram'] }}" target="_blank" rel="noopener noreferrer">Telegram</a>
                    @endif

                    @if(empty($trust['social_instagram']) && empty($trust['social_telegram']))
                        لینک‌های اجتماعی از تنظیمات محیطی اضافه می‌شوند.
                    @endif
                </span>
            </div>

        </div>
    </div>


    {{-- =====================================================
         FOOTER BOTTOM
    ====================================================== --}}

    <div class="container footer-bottom">

        <span>
            © {{ date('Y') }}
            {{ $siteBrandNameLatin ?? 'Janan' }}
            · تمامی حقوق محفوظ است.
        </span>

        <span class="footer-bottom__signature">
            JANAN / ONLINE STORE
        </span>

    </div>

</footer>
