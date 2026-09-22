@extends('layouts.store')

@section('title', 'تماس با ما — ' . ($siteBrandNameLatin ?? 'JANAN'))

@section('content')

    <div class="janan-contact-page">

        {{-- =====================================================
             HERO
        ====================================================== --}}
        <section class="janan-contact-hero">

            <div class="janan-contact-container">

                <div class="janan-contact-breadcrumb">
                    <a href="{{ route('home') }}">JANAN</a>
                    <span>/</span>
                    <span>CONTACT</span>
                </div>

                <div class="janan-contact-hero__content">

                <span class="janan-contact-eyebrow">
                    GET IN TOUCH
                </span>

                    <h1>
                        با ما<br>
                        <em>در ارتباط باشید.</em>
                    </h1>

                    <p>
                        برای پرسش درباره محصولات، سفارش‌ها یا خدمات جانان،
                        از طریق راه‌های ارتباطی زیر با ما در تماس باشید.
                    </p>

                </div>

            </div>

        </section>


        {{-- =====================================================
             CONTACT CONTENT
        ====================================================== --}}
        <section class="janan-contact-section">

            <div class="janan-contact-container">

                <div class="janan-contact-grid">


                    {{-- =================================================
                         INFO
                    ================================================== --}}
                    <div class="janan-contact-info">

                        <div class="janan-contact-heading">

                        <span>
                            JANAN / SUPPORT
                        </span>

                            <h2>
                                کنار شما هستیم.
                            </h2>

                            <p>
                                تیم پشتیبانی جانان تلاش می‌کند پاسخ‌گوی
                                سوالات و درخواست‌های شما باشد.
                            </p>

                        </div>


                        {{-- PHONE --}}
                        <a
                            href="{{ !empty($siteStorePhone) ? 'tel:' . preg_replace('/[^0-9+]/', '', $siteStorePhone) : '#' }}"
                            class="janan-contact-item"
                        >

                        <span class="janan-contact-item__index">
                            01
                        </span>

                            <div>

                                <small>
                                    PHONE
                                </small>

                                <strong>
                                    {{ $siteStorePhone ?: '—' }}
                                </strong>

                            </div>

                            <span class="janan-contact-item__arrow">
                            ↗
                        </span>

                        </a>


                        {{-- EMAIL --}}
                        <a
                            href="{{ !empty($siteStoreEmail) ? 'mailto:' . $siteStoreEmail : '#' }}"
                            class="janan-contact-item"
                        >

                        <span class="janan-contact-item__index">
                            02
                        </span>

                            <div>

                                <small>
                                    EMAIL
                                </small>

                                <strong>
                                    {{ $siteStoreEmail ?: '—' }}
                                </strong>

                            </div>

                            <span class="janan-contact-item__arrow">
                            ↗
                        </span>

                        </a>


                        {{-- ADDRESS --}}
                        <div class="janan-contact-item janan-contact-item--address">

                        <span class="janan-contact-item__index">
                            03
                        </span>

                            <div>

                                <small>
                                    ADDRESS
                                </small>

                                <strong>
                                    {{ $siteStoreAddress ?: '—' }}
                                </strong>

                            </div>

                        </div>


                        {{-- FOOTER NOTE --}}
                        @if(!empty($siteFooterText))

                            <div class="janan-contact-note">
                                {{ $siteFooterText }}
                            </div>

                        @endif

                    </div>


                    {{-- =================================================
                         FORM
                    ================================================== --}}
                    <div class="janan-contact-form-wrap">

                        <div class="janan-contact-form-head">

                        <span>
                            SEND A MESSAGE
                        </span>

                            <h2>
                                پیام شما
                            </h2>

                        </div>


                        @if(session('success'))

                            <div class="janan-contact-success">
                                {{ session('success') }}
                            </div>

                        @endif


                        @if($errors->any())

                            <div class="janan-contact-errors">

                                <strong>
                                    لطفاً موارد زیر را بررسی کنید:
                                </strong>

                                <ul>

                                    @foreach($errors->all() as $error)

                                        <li>
                                            {{ $error }}
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        @endif


                        <form
                            class="janan-contact-form"
                            action="{{ route('contact.submit') }}"
                            method="POST"
                        >

                            @csrf


                            {{-- NAME --}}
                            <div class="janan-form-field">

                                <label for="contact-name">
                                    نام و نام خانوادگی
                                </label>

                                <input
                                    id="contact-name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="نام و نام خانوادگی"
                                    autocomplete="name"
                                    required
                                >

                            </div>


                            {{-- EMAIL --}}
                            <div class="janan-form-field">

                                <label for="contact-email">
                                    ایمیل
                                </label>

                                <input
                                    id="contact-email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="example@email.com"
                                    autocomplete="email"
                                    required
                                >

                            </div>


                            {{-- MESSAGE --}}
                            <div class="janan-form-field">

                                <label for="contact-message">
                                    پیام شما
                                </label>

                                <textarea
                                    id="contact-message"
                                    name="message"
                                    rows="7"
                                    placeholder="پیام خود را بنویسید..."
                                    required
                                >{{ old('message') }}</textarea>

                            </div>


                            {{-- SUBMIT --}}
                            <button
                                type="submit"
                                class="janan-contact-submit"
                            >

                            <span>
                                ارسال پیام
                            </span>

                                <span>
                                ↗
                            </span>

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
             BOTTOM BAND
        ====================================================== --}}
        <section class="janan-contact-bottom">

            <div class="janan-contact-container">

                <div class="janan-contact-bottom__inner">

                <span>
                    JANAN
                </span>

                    <p>
                        جزئیات مهم‌اند.
                    </p>

                    <a href="{{ route('products.index') }}">
                        مشاهده محصولات
                        <span>↗</span>
                    </a>

                </div>

            </div>

        </section>

    </div>


    <style>

        /* =========================================================
           ROOT
        ========================================================== */

        .janan-contact-page {
            min-height: 100vh;
            background: #ffffff;
            color: #334155;
        }

        .janan-contact-container {
            width: min(1380px, calc(100% - 48px));
            margin: 0 auto;
        }


        /* =========================================================
           HERO
        ========================================================== */

        .janan-contact-hero {
            position: relative;
            overflow: hidden;

            background:
                radial-gradient(
                    circle at 78% 35%,
                    rgba(255,255,255,.95) 0%,
                    rgba(255,255,255,0) 40%
                ),
                linear-gradient(
                    135deg,
                    #f5fbff 0%,
                    #f8fbff 48%,
                    #fff4f8 100%
                );

            border-bottom: 1px solid #e7edf2;
        }


        .janan-contact-hero::before {
            content: "";

            position: absolute;

            width: 370px;
            height: 370px;

            top: -170px;
            left: -110px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(246,207,225,.30),
                    rgba(246,207,225,0)
                );

            pointer-events: none;
        }


        .janan-contact-hero::after {
            content: "";

            position: absolute;

            width: 300px;
            height: 300px;

            right: -120px;
            bottom: -150px;

            border-radius: 50%;

            background:
                radial-gradient(
                    circle,
                    rgba(191,233,255,.28),
                    rgba(191,233,255,0)
                );

            pointer-events: none;
        }


        .janan-contact-breadcrumb {
            position: relative;
            z-index: 2;

            display: flex;
            align-items: center;
            gap: 9px;

            padding-top: 24px;

            color: #a0acb8;

            font-size: 9px;

            letter-spacing: .12em;
        }


        .janan-contact-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }


        .janan-contact-hero__content {
            position: relative;
            z-index: 2;

            padding:
                70px 0
                75px;
        }


        .janan-contact-eyebrow {
            display: block;

            margin-bottom: 20px;

            color: #b97896;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: .25em;
        }


        .janan-contact-hero h1 {
            margin: 0;

            color: #334155;

            font-family:
                Vazirmatn,
                Arial,
                sans-serif;

            font-size: clamp(48px, 7vw, 92px);

            line-height: .92;

            font-weight: 500;

            letter-spacing: -.05em;
        }


        .janan-contact-hero h1 em {
            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-size: .72em;

            font-weight: 400;

            font-style: italic;

            color: #b97896;
        }


        .janan-contact-hero__content p {
            max-width: 530px;

            margin: 28px 0 0;

            color: #64748b;

            font-size: 13px;

            line-height: 2.05;
        }


        /* =========================================================
           SECTION
        ========================================================== */

        .janan-contact-section {
            padding:
                85px 0
                100px;
        }


        .janan-contact-grid {
            display: grid;

            grid-template-columns:
            minmax(0, .9fr)
            minmax(440px, 1.1fr);

            gap: 90px;

            align-items: start;
        }


        /* =========================================================
           INFO
        ========================================================== */

        .janan-contact-info {
            padding-top: 8px;
        }


        .janan-contact-heading > span {
            display: block;

            margin-bottom: 10px;

            color: #b97896;

            font-size: 9px;

            font-weight: 700;

            letter-spacing: .2em;
        }


        .janan-contact-heading h2 {
            margin: 0;

            color: #334155;

            font-size: 32px;

            font-weight: 500;

            letter-spacing: -.03em;
        }


        .janan-contact-heading p {
            max-width: 440px;

            margin: 17px 0 35px;

            color: #64748b;

            font-size: 12px;

            line-height: 2;
        }


        /* =========================================================
           CONTACT ITEMS
        ========================================================== */

        .janan-contact-item {
            min-height: 82px;

            display: grid;

            grid-template-columns:
            38px
            minmax(0, 1fr)
            30px;

            align-items: center;

            gap: 15px;

            padding: 15px 0;

            border-top: 1px solid #e8edf1;

            color: #334155;

            text-decoration: none;
        }


        .janan-contact-item:last-of-type {
            border-bottom: 1px solid #e8edf1;
        }


        .janan-contact-item__index {
            color: #b97896;

            font-size: 9px;

            letter-spacing: .1em;
        }


        .janan-contact-item div {
            min-width: 0;
        }


        .janan-contact-item small {
            display: block;

            margin-bottom: 5px;

            color: #a1adba;

            font-size: 8px;

            letter-spacing: .18em;
        }


        .janan-contact-item strong {
            display: block;

            overflow: hidden;

            color: #475569;

            font-size: 12px;

            font-weight: 500;

            text-overflow: ellipsis;
        }


        .janan-contact-item__arrow {
            color: #94a3b8;

            font-size: 16px;

            transition:
                transform .25s ease,
                color .25s ease;
        }


        .janan-contact-item:hover
        .janan-contact-item__arrow {
            color: #b97896;

            transform: translate(3px, -3px);
        }


        .janan-contact-item--address {
            cursor: default;
        }


        .janan-contact-note {
            margin-top: 24px;

            padding-top: 20px;

            color: #a1adba;

            font-size: 10px;

            line-height: 1.9;
        }


        /* =========================================================
           FORM
        ========================================================== */

        .janan-contact-form-wrap {
            padding:
                32px;

            border:
                1px solid #e4ebf0;

            border-radius: 24px;

            background:
                linear-gradient(
                    145deg,
                    #ffffff 0%,
                    #fbfdff 62%,
                    #fff7fa 100%
                );

            box-shadow:
                0 25px 65px rgba(51,65,85,.06);
        }


        .janan-contact-form-head {
            margin-bottom: 30px;
        }


        .janan-contact-form-head > span {
            display: block;

            margin-bottom: 7px;

            color: #b97896;

            font-size: 9px;

            font-weight: 700;

            letter-spacing: .2em;
        }


        .janan-contact-form-head h2 {
            margin: 0;

            color: #334155;

            font-size: 27px;

            font-weight: 500;
        }


        /* =========================================================
           ALERTS
        ========================================================== */

        .janan-contact-success {
            margin-bottom: 22px;

            padding: 14px 16px;

            border:
                1px solid #dcece3;

            border-radius: 12px;

            background: #f6fbf8;

            color: #668777;

            font-size: 11px;

            line-height: 1.8;
        }


        .janan-contact-errors {
            margin-bottom: 22px;

            padding: 15px 17px;

            border:
                1px solid #f0dce3;

            border-radius: 12px;

            background: #fff7fa;

            color: #a86e82;

            font-size: 11px;

            line-height: 1.9;
        }


        .janan-contact-errors strong {
            display: block;

            margin-bottom: 5px;

            font-weight: 600;
        }


        .janan-contact-errors ul {
            margin: 0;
            padding-right: 17px;
        }


        /* =========================================================
           FORM FIELD
        ========================================================== */

        .janan-contact-form {
            display: grid;

            gap: 21px;
        }


        .janan-form-field {
            display: grid;

            gap: 9px;
        }


        .janan-form-field label {
            color: #475569;

            font-size: 11px;

            font-weight: 600;
        }


        .janan-form-field input,
        .janan-form-field textarea {
            width: 100%;

            border:
                1px solid #dfe7ed;

            border-radius: 13px;

            outline: none;

            background:
                rgba(255,255,255,.86);

            color: #334155;

            font-family: inherit;

            font-size: 12px;

            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease;
        }


        .janan-form-field input {
            height: 50px;

            padding:
                0 14px;
        }


        .janan-form-field textarea {
            min-height: 165px;

            padding:
                14px;

            resize: vertical;

            line-height: 1.9;
        }


        .janan-form-field input::placeholder,
        .janan-form-field textarea::placeholder {
            color: #b2bcc6;
        }


        .janan-form-field input:focus,
        .janan-form-field textarea:focus {
            border-color: #c7a7b7;

            background: #ffffff;

            box-shadow:
                0 0 0 4px rgba(217,169,192,.10);
        }


        /* =========================================================
           SUBMIT
        ========================================================== */

        .janan-contact-submit {
            width: 100%;

            min-height: 56px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding:
                0 18px;

            border: 0;

            border-radius: 14px;

            background: #334155;

            color: #ffffff;

            font-family: inherit;

            font-size: 12px;

            cursor: pointer;

            transition:
                background .25s ease,
                transform .25s ease;
        }


        .janan-contact-submit:hover {
            background: #1e293b;

            transform: translateY(-2px);
        }


        .janan-contact-submit span:last-child {
            font-size: 17px;
        }


        /* =========================================================
           BOTTOM
        ========================================================== */

        .janan-contact-bottom {
            border-top: 1px solid #e8edf1;

            background:
                linear-gradient(
                    135deg,
                    #f8fbff 0%,
                    #ffffff 48%,
                    #fff6fa 100%
                );
        }


        .janan-contact-bottom__inner {
            min-height: 145px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 25px;
        }


        .janan-contact-bottom__inner > span {
            color: #b97896;

            font-family:
                Georgia,
                serif;

            font-size: 16px;

            letter-spacing: .16em;
        }


        .janan-contact-bottom__inner p {
            margin: 0;

            color: #94a3b8;

            font-size: 11px;
        }


        .janan-contact-bottom__inner a {
            display: inline-flex;

            align-items: center;

            gap: 9px;

            color: #475569;

            font-size: 11px;

            text-decoration: none;
        }


        /* =========================================================
           TABLET
        ========================================================== */

        @media (max-width: 950px) {

            .janan-contact-grid {
                grid-template-columns:
                1fr;

                gap: 55px;
            }


            .janan-contact-info {
                max-width: 760px;
            }

        }


        /* =========================================================
           MOBILE
        ========================================================== */

        @media (max-width: 640px) {

            .janan-contact-container {
                width: min(100% - 32px, 1380px);
            }


            .janan-contact-hero__content {
                padding:
                    52px 0
                    52px;
            }


            .janan-contact-hero h1 {
                font-size: 48px;
            }


            .janan-contact-hero__content p {
                max-width: 350px;

                font-size: 12px;
            }


            .janan-contact-section {
                padding:
                    55px 0
                    70px;
            }


            .janan-contact-grid {
                gap: 45px;
            }


            .janan-contact-heading h2 {
                font-size: 27px;
            }


            .janan-contact-form-wrap {
                padding:
                    22px;

                border-radius: 19px;
            }


            .janan-contact-form-head h2 {
                font-size: 24px;
            }


            .janan-contact-bottom__inner {
                min-height: 125px;

                flex-wrap: wrap;

                justify-content: space-between;

                padding:
                    25px 0;
            }


            .janan-contact-bottom__inner p {
                order: 3;

                width: 100%;
            }

        }

    </style>

@endsection
