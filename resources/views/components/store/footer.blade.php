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

                <a href="{{ route('contact') }}">
                    <span>پشتیبانی</span>
                    <b aria-hidden="true">↗</b>
                </a>
            <div
                class="footer-social footer-social--networks"
                aria-label="شبکه‌های اجتماعی"
            >
                @if($siteInstagram)
                    <a href="{{ $siteInstagram }}" target="_blank" rel="noopener noreferrer">
                        Instagram
                    </a>
                @endif

                @if($siteTelegram)
                    <a href="{{ $siteTelegram }}" target="_blank" rel="noopener noreferrer">
                        Telegram
                    </a>
                @endif

                @if($siteWhatsapp)
                    <a href="{{ $siteWhatsapp }}" target="_blank" rel="noopener noreferrer">
                        WhatsApp
                    </a>
                @endif
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

                <a href="{{ route('contact') }}">
                    پشتیبانی
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


        <div class="container footer-legal">
        <span>اطلاعات فروشگاه و شرایط خرید</span>

        <a href="{{ route('returns') }}">
            شرایط مرجوعی
        </a>

        <a href="{{ route('shipping') }}">
            روش ارسال
        </a>

        @if($siteEnamad)
            <a href="{{ $siteEnamad }}" target="_blank" rel="noopener noreferrer">
                مجوز فروشگاه
            </a>
        @endif

        @if($siteLicense)
            <a href="{{ $siteLicense }}" target="_blank" rel="noopener noreferrer">
                مجوزها
            </a>
        @endif
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
