@extends('layouts.store')

@section('title', 'خرید عمده — ' . ($siteBrandNameFa ?? 'جانان'))

@section('content')
<div class="wholesale-page">

    <section class="section-block section-block--compact">
        <div class="container">
            <div class="wholesale-hero wholesale-hero--reference">
                <div class="wholesale-hero__main">
                    <h1>پک‌های عمدهٔ جانان</h1>
                    <p>محتویات و قیمت هر بسته را ببین و پک مناسب را به سبد خرید اضافه کن.</p>
                    <div class="wholesale-hero__actions">
                        <a class="wholesale-button" href="#packs">مشاهدهٔ پک‌ها</a>
                        <a class="wholesale-button wholesale-button--soft" href="#cheque-application">شرایط خرید چکی</a>
                    </div>
                </div>
                <aside class="wholesale-hero__status" aria-label="وضعیت خرید عمده">
                    <span class="wholesale-status__label">وضعیت خرید</span>
                    <strong>خرید آنلاین عمده فعال است</strong>
                    <p>برای سفارش آنلاین نیازی به تأیید قبلی حساب نیست. پرداخت چکی فقط با مجوز فعال مدیر امکان‌پذیر است.</p>
                    <a href="{{ route('wholesale.catalog') }}">مشاهدهٔ کاتالوگ محصولات عمده</a>
                </aside>
            </div>

        </div>
    </section>

    <section class="section-block wholesale-catalog" id="wholesale-catalog"><span id="packs" class="wholesale-anchor" aria-hidden="true"></span>
        <div class="container">
            <header class="wholesale-catalog__head">
                <div>
                    <h2>پک‌های آمادهٔ عمده</h2>
                    <p>تعداد هر مدل و مبلغ کل بسته روی کارت مشخص است.</p>
                </div>
                <a class="button button--ghost" href="{{ route('wholesale.catalog') }}">مشاهده کاتالوگ محصولات عمده</a>
            </header>

            @if($packs->isNotEmpty())
                <div class="wholesale-pack-grid">
                    @foreach($packs as $pack)
                        @include('pages.wholesale.pack-card', ['pack' => $pack])
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
                <h2 class="customer-panel-title">مراحل خرید چکی</h2>
                <ol>
                    <li><span>۱</span><div><strong>درخواست اعتبار</strong><p>مبلغ موردنیاز را ثبت کن. سقف هر سفارش بعد از بررسی مدیر در حساب نمایش داده می‌شود.</p></div></li>
                    <li><span>۲</span><div><strong>ثبت چک در تسویه‌حساب</strong><p>پس از تأیید اعتبار، پک را به سبد اضافه کن و در پرداخت چکی، شناسهٔ صیادی، بانک، سررسید و تصویر چک را وارد کن.</p></div></li>
                    <li><span>۳</span><div><strong>پیگیری بررسی</strong><p>سفارش تا بررسی چک در انتظار می‌ماند. نتیجه و درخواست احتمالی ارسال اصل چک را در حساب کاربری ببین.</p></div></li>
                </ol>
            </aside>

            <section class="customer-surface wholesale-form-card" aria-labelledby="cheque-application-title">
                <header class="section-head">
                    <div>
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
                        <p>سقف هر سفارش: {{ $chequePermission->max_order_amount !== null ? \App\Support\PersianNumber::format((float) $chequePermission->max_order_amount) . ' تومان' : 'بدون سقف تعیین‌شده' }}.</p>
                        <a class="button button--primary" href="{{ route('cart') }}" aria-label="سبد خرید">
                            <x-store.icon name="basket" />
                        </a>
                        <a class="button button--ghost" href="#packs">انتخاب پک</a>
                    </div>
                @elseif($chequePermission?->isPending())
                    <div class="customer-inline-status" role="status">
                        <strong>درخواستت در انتظار بررسی مدیر است.</strong>
                        <p>مبلغ درخواستی: {{ \App\Support\PersianNumber::format((float) $chequePermission->requested_amount) }} تومان.</p>
                        <a href="{{ route('account') }}#account-cheque-title">پیگیری در حساب کاربری</a>
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
