@extends('layouts.store')

@section('title','تماس با ما — '.($siteBrandNameLatin ?? 'Janan'))

@section('content')
<section class="page-hero page-hero--motion"><div class="container"><span class="eyebrow">{{ $siteBrandNameLatin ?? 'Janan' }} / CONTACT</span><h1>با جانان در ارتباط باش.</h1><p>پیام خود را مستقیم برای فروشگاه ارسال کن.</p></div></section>

<section class="section-block">
<div class="container checkout-layout">
<section class="checkout-card">
<div class="section-head"><div><span class="eyebrow">CONTACT FORM</span><h2>ارسال پیام</h2></div></div>
<form method="POST" action="{{ route('contact.submit') }}" class="checkout-form">
@csrf
<div class="form-grid">
<label>نام<input name="name" value="{{ old('name',auth()->user()?->name) }}" required></label>
<label>ایمیل<input type="email" name="email" value="{{ old('email',auth()->user()?->email) }}"></label>
<label>شماره تماس<input name="phone" value="{{ old('phone',auth()->user()?->phone) }}"></label>
<label>موضوع<input name="subject" value="{{ old('subject') }}"></label>
<label class="form-grid__full">پیام<textarea name="message" rows="7" required>{{ old('message') }}</textarea></label>
</div>
<button class="button button--primary" type="submit">ارسال پیام</button>
</form>
</section>

<aside class="checkout-card">
<span class="eyebrow">STORE CONTACT</span>
<h2>{{ $siteProfile?->site_name ?: 'Janan' }}</h2>
@if($siteProfile?->phone)<p>تلفن: {{ $siteProfile->phone }}</p>@endif
@if($siteProfile?->email)<p>ایمیل: {{ $siteProfile->email }}</p>@endif
@if($siteProfile?->address)<p>آدرس: {{ $siteProfile->address }}</p>@endif
@if($siteProfile?->working_hours)<p>ساعات کاری: {{ $siteProfile->working_hours }}</p>@endif
</aside>
</div>
</section>
@endsection
