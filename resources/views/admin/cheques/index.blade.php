@extends('layouts.admin')

@section('title', 'مدیریت چک‌ها')
@section('page-title', 'مدیریت چک‌ها')

@section('content')
@php
    $number = [\App\Support\PersianNumber::class, 'format'];
    $shortStatuses = ['submitted' => 'ثبت‌شده', 'under_review' => 'در حال بررسی', 'awaiting_original' => 'انتظار اصل چک', 'original_received' => 'اصل دریافت شد', 'accepted' => 'تأیید شده', 'deposited' => 'واگذار به بانک', 'cleared' => 'تسویه شده', 'rejected' => 'رد شده', 'bounced' => 'برگشتی', 'cancelled' => 'لغو شده'];
    $listParams = array_merge(['tab' => $tab], request()->only('q', 'status', 'access', 'page'));
    $listUrl = route('admin.cheques.index', $listParams);
@endphp
<div class="cheque-workspace">
    <header class="admin-page-head">
        <div><h1 class="admin-page-head__title">مدیریت چک‌ها</h1>
        <p class="admin-page-head__text">بررسی چک‌ها، درخواست اعتبار و مجوز مشتریان.</p></div>
    </header>
    <nav class="cheque-hub-tabs" aria-label="بخش‌های مدیریت چک">
        @foreach(['requests' => 'درخواست‌ها', 'customers' => 'مشتریان مجاز', 'payments' => 'چک‌های ثبت‌شده'] as $key => $label)
            <a href="{{ route('admin.cheques.index', ['tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif>
                {{ $label }}
                @if($key === 'requests' || $key === 'payments')
                    <span data-cheque-count="{{ $key }}" @if(!($chequeAlerts[$key] ?? 0)) hidden @endif>{{ $number($chequeAlerts[$key] ?? 0) }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    @if($tab !== 'payments')
        @include('admin.cheques.customers')
    @else
        @if($selectedCheque)
            @include('admin.cheques.payment-detail', ['cheque' => $selectedCheque])
        @endif
        <section class="cheque-hub-panel" aria-labelledby="cheque-list-title">
            <header class="cheque-section-head"><div><h2 id="cheque-list-title">چک‌های ثبت‌شده</h2><p>{{ $number($cheques->total()) }} چک مطابق فیلتر</p></div></header>
            <form method="GET" action="{{ route('admin.cheques.index') }}" class="cheque-hub-filter">
                <input type="hidden" name="tab" value="payments">
                <div class="admin-field"><label for="cheque-q">جستجوی چک</label><input id="cheque-q" type="search" name="q" maxlength="120" value="{{ request('q') }}" placeholder="شماره چک، صیاد، بانک یا مشتری"></div>
                <div class="admin-field"><label for="cheque-status">وضعیت چک</label><select id="cheque-status" name="status">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach($statusNames as $status => $label)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                    @endforeach
                </select></div>
                <button type="submit" class="admin-btn admin-btn--secondary">اعمال فیلتر</button>
                <a href="{{ route('admin.cheques.index') }}" class="admin-btn admin-btn--ghost">پاک کردن</a>
            </form>
            <div class="cheque-table-wrap">
                <table class="cheque-table">
                    <thead><tr><th scope="col">چک / بانک</th><th scope="col">مشتری</th><th scope="col">مبلغ</th><th scope="col">سررسید</th><th scope="col">وضعیت</th><th scope="col">عملیات</th></tr></thead>
                    <tbody>
                    @forelse($cheques as $cheque)
                        <tr data-cheque-row="{{ $cheque->id }}" @class(['cheque-selected' => $selectedCheque?->id === $cheque->id])>
                            <td data-label="چک"><strong><bdi>{{ $cheque->cheque_number ?: '—' }}</bdi></strong><small>{{ $cheque->bank_name ?: 'بانک نامشخص' }}</small><small>صیاد: <bdi>{{ $cheque->sayad_id }}</bdi></small></td>
                            <td data-label="مشتری"><strong>{{ $cheque->order?->user?->name ?: '—' }}</strong><small><bdi>{{ $cheque->order?->user?->phone ?: '—' }}</bdi></small></td>
                            <td data-label="مبلغ"><strong>{{ $number((float) $cheque->amount) }} تومان</strong></td>
                            <td data-label="سررسید">@if($cheque->due_date)<time datetime="{{ $cheque->due_date->toDateString() }}" data-admin-date="{{ $cheque->due_date->toDateString() }}" data-admin-date-format="day">{{ $cheque->due_date->format('Y/m/d') }}</time>@else — @endif</td>
                            <td data-label="وضعیت">@include('admin.cheques.status', ['status' => $cheque->status])</td>
                            <td data-label="عملیات"><a class="admin-btn admin-btn--ghost admin-btn--sm" href="{{ route('admin.cheques.index', array_merge($listParams, ['cheque_id' => $cheque->id])) }}#cheque-detail">جزئیات و بررسی</a></td>
                        </tr>
                    @empty
                        <tr class="cheque-empty"><td colspan="6">چکی مطابق فیلتر پیدا نشد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($cheques->hasPages())<div class="admin-pagination">{{ $cheques->links() }}</div>@endif
        </section>
    @endif
</div>
@endsection
