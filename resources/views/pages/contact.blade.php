@extends('layouts.store')

@section('title','تماس با ما — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--premium"><div class="container page-hero__layout"><div><span class="eyebrow">04 / CONTACT</span><h1>با جانان<br><em>در ارتباط باش.</em></h1><p>پیامت را مستقیم برای فروشگاه بفرست. اطلاعات تماس فقط وقتی نمایش داده می‌شود که در تنظیمات محیطی فروشگاه ثبت شده باشد.</p></div><div class="page-hero__word" aria-hidden="true">↗</div></div></section>
<section class="section-block contact-stage"><div class="container contact-layout">
<section class="checkout-card contact-form-card"><div class="section-head"><div><span class="eyebrow">CONTACT FORM</span><h2>پیامت را بفرست.</h2></div><span class="form-card__index">01</span></div>
<form method="POST" action="{{ route('contact.submit') }}" class="checkout-form">@csrf<div class="form-grid">
<label>نام<input name="name" value="{{ old('name',auth()->user()?->name) }}" required></label><label>ایمیل<input type="email" name="email" value="{{ old('email',auth()->user()?->email) }}"></label><label>شماره تماس<input name="phone" value="{{ old('phone',auth()->user()?->phone) }}"></label><label>موضوع<input name="subject" value="{{ old('subject') }}"></label><label class="form-grid__full">پیام<textarea name="message" rows="7" required>{{ old('message') }}</textarea></label>
</div><button class="button button--primary" type="submit">ارسال پیام ↗</button></form></section>
<aside class="contact-info-panel"><span class="eyebrow">JANAN / STUDIO</span><h2>راه‌های ارتباط</h2><div class="contact-info-list">
@if($contactStore['phone'] ?? null)<p><small>PHONE</small><span>{{ $contactStore['phone'] }}</span></p>@endif
@if($contactStore['email'] ?? null)<p><small>EMAIL</small><span>{{ $contactStore['email'] }}</span></p>@endif
@if($contactStore['address'] ?? null)<p><small>ADDRESS</small><span>{{ $contactStore['address'] }}</span></p>@endif
@if($contactStore['working_hours'] ?? null)<p><small>HOURS</small><span>{{ $contactStore['working_hours'] }}</span></p>@endif
@if(!array_filter($contactStore ?? []))<p class="contact-info-empty">اطلاعات تماس هنوز در تنظیمات محیطی فروشگاه ثبت نشده است.</p>@endif
</div></aside>
</div></section>
@endsection
