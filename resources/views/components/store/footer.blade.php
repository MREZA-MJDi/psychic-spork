<footer class="store-footer">
    <div class="container footer-top">
        <div class="footer-brand">
            <a href="{{ route('home') }}" class="brand brand--footer">
                <span class="brand__latin">{{ $siteBrandNameLatin ?? 'Janan' }}</span>
                <span class="brand__fa">{{ $siteBrandNameFa ?? 'جانان' }}</span>
            </a>

            <p>
                {{ $siteFooterText ?: "فروشگاه آنلاین {$siteBrandNameLatin}؛ ترکیبی از کیفیت، زیبایی و انتخابی که برای شما طراحی شده است." }}
            </p>

            <div class="footer-social" aria-label="دسترسی سریع">
                <a href="{{ route('categories.index') }}">دسته‌ها</a>
                <a href="{{ route('cart') }}">سبد</a>
                <a href="{{ route('contact') }}">پشتیبانی</a>
            </div>
        </div>

        <div class="footer-col">
            <h3>دسترسی سریع</h3>
            <a href="{{ route('home') }}">خانه</a>
            <a href="{{ route('products.index') }}">محصولات</a>
            <a href="{{ route('categories.index') }}">دسته‌بندی‌ها</a>
        </div>

        <div class="footer-col">
            <h3>خدمات مشتری</h3>
            <a href="{{ route('shipping') }}">روش‌های ارسال</a>
            <a href="{{ route('returns') }}">شرایط مرجوعی</a>
            <a href="{{ route('faq') }}">سوالات متداول</a>
            <a href="{{ route('contact') }}">پشتیبانی</a>
        </div>

        <div class="footer-newsletter">
            <h3>ارتباط با فروشگاه</h3>

            @if($siteStorePhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $siteStorePhone) }}" class="footer-contact-line">
                    {{ $siteStorePhone }}
                </a>
            @endif

            @if($siteStoreEmail)
                <a href="mailto:{{ $siteStoreEmail }}" class="footer-contact-line">
                    {{ $siteStoreEmail }}
                </a>
            @endif

            @if($siteStoreAddress)
                <span class="footer-contact-line">{{ $siteStoreAddress }}</span>
            @endif

            <a href="{{ auth()->check() ? route('account') : route('login') }}" class="button button--ghost">
                {{ auth()->check() ? 'حساب جانان' : 'ورود به حساب' }}
            </a>
        </div>
    </div>

    <div class="container footer-bottom">
        <span>© {{ date('Y') }} {{ $siteBrandNameLatin ?? 'Janan' }}. تمامی حقوق محفوظ است.</span>
        <span>فروشگاه آنلاین · انتخاب و سفارش</span>
    </div>
</footer>