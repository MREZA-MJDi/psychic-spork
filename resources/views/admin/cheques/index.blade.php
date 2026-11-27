@extends('layouts.admin')

@section('title', 'مدیریت چک‌ها')

@section('page-title', 'مدیریت چک‌ها')

@section('content')
<div class="admin-page">
    <header class="admin-page-head">
        <div><h1 class="admin-page-head__title">مدیریت چک‌ها</h1>
        <p class="admin-page-head__text">درخواست اعتبار، مشتریان مجاز و پرداخت‌های چکی در یکجا.</p></div>
    </header>
    <nav class="cheque-hub-tabs" aria-label="بخش‌های مدیریت چک">
        @foreach(['requests' => 'درخواست‌ها', 'customers' => 'مشتریان مجاز', 'payments' => 'چک‌های ثبت‌شده'] as $key => $label)
            <a href="{{ route('admin.cheques.index', ['tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif>
                {{ $label }}
                @if($key === 'requests' || $key === 'payments')
                    <span data-cheque-count="{{ $key }}" @if(!($chequeAlerts[$key] ?? 0)) hidden @endif>{{ \App\Support\PersianNumber::format($chequeAlerts[$key] ?? 0) }}</span>
                @endif
            </a>
        @endforeach
    </nav>
    @if($tab !== 'payments')
        @include('admin.cheques.customers')
    @else
    <section class="admin-card cheque-hub-panel">
    <form method="GET" action="{{ route('admin.cheques.index') }}" class="admin-filter-bar cheque-hub-filter">
        <input type="hidden" name="tab" value="payments">
        <input aria-label="جستجوی چک" type="search" name="q" value="{{ request('q') }}" placeholder="شماره چک، صیاد، بانک، نام یا موبایل مشتری">
        <select name="status" aria-label="وضعیت چک">
            <option value="">همه وضعیت‌ها</option>
            @foreach($statusNames as $status => $label)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="admin-btn admin-btn--secondary">فیلتر</button>
        <a href="{{ route('admin.cheques.index') }}" class="admin-btn admin-btn--ghost">پاک کردن</a>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
            <tr>
                <th>چک</th>
                <th>مشتری</th>
                <th>مبلغ</th>
                <th>سررسید</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            @forelse($cheques as $cheque)
                <tr>
                    <td>
                        <strong>{{ $cheque->cheque_number ?: '—' }}</strong>
                        <small>{{ $cheque->bank_name ?: 'بانک نامشخص' }}</small>
                        <small>صیاد: {{ $cheque->sayad_id }}</small>
                        @if($cheque->account_holder)
                            <small>صاحب حساب: {{ $cheque->account_holder }}</small>
                        @endif
                        @if($cheque->image_path)
                            <a href="{{ route('admin.cheques.image', $cheque) }}" target="_blank" rel="noopener">مشاهده تصویر چک</a>
                        @endif
                        @if($cheque->original_received_at)
                            <small>اصل چک دریافت شد: {{ $cheque->original_received_at->format('Y/m/d H:i') }}</small>
                        @endif
                        @if($cheque->review_note)
                            <small>یادداشت / دستور مشتری: {{ $cheque->review_note }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $cheque->order?->user?->name ?: '—' }}
                        <small>{{ $cheque->order?->user?->phone ?: '—' }}</small>
                    </td>
                    <td>{{ \App\Support\PersianNumber::format((float) $cheque->amount) }} تومان</td>
                    <td>{{ $cheque->due_date?->format('Y/m/d') ?: '—' }}</td>
                    <td><span class="status-badge">{{ $statusNames[$cheque->status] ?? $cheque->status }}</span></td>
                    <td>
                        <div class="admin-actions">
                            @if($cheque->status === 'submitted')
                                <form method="POST" action="{{ route('admin.cheques.review', $cheque) }}">@csrf @method('PATCH')<button type="submit">بررسی</button></form>
                            @elseif(in_array($cheque->status, ['under_review', 'original_received'], true))
                                <form method="POST" action="{{ route('admin.cheques.accept', $cheque) }}">@csrf @method('PATCH')<button type="submit">تأیید و آزادسازی سفارش</button></form>
                                @if($cheque->status === 'under_review')
                                    <form method="POST" action="{{ route('admin.cheques.request-original', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <input name="note" maxlength="2000" required placeholder="آدرس یا دستور ارسال اصل چک" aria-label="دستور ارسال اصل چک">
                                        <button type="submit">درخواست ارسال اصل چک</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.cheques.reject', $cheque) }}">
                                    @csrf @method('PATCH')
                                    <input name="note" maxlength="2000" placeholder="دلیل رد (اختیاری)" aria-label="دلیل رد چک">
                                    <button type="submit">رد</button>
                                </form>
                            @elseif($cheque->status === 'awaiting_original')
                                <form method="POST" action="{{ route('admin.cheques.original-received', $cheque) }}">
                                    @csrf @method('PATCH')
                                    <input name="note" maxlength="2000" placeholder="یادداشت دریافت اصل چک (اختیاری)" aria-label="یادداشت دریافت اصل چک">
                                    <button type="submit">اصل چک دریافت شد</button>
                                </form>
                            @elseif($cheque->status === 'accepted')
                                <form method="POST" action="{{ route('admin.cheques.deposit', $cheque) }}">@csrf @method('PATCH')<button type="submit">واریز</button></form>
                            @elseif($cheque->status === 'deposited')
                                <form method="POST" action="{{ route('admin.cheques.clear', $cheque) }}">@csrf @method('PATCH')<button type="submit">تسویه</button></form>
                                <form method="POST" action="{{ route('admin.cheques.bounce', $cheque) }}">
                                    @csrf @method('PATCH')
                                    <input name="note" maxlength="2000" placeholder="علت برگشت (اختیاری)" aria-label="علت برگشت چک">
                                    <button type="submit">برگشت</button>
                                </form>
                            @else
                                <span>بدون اقدام</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">چکی با این فیلتر پیدا نشد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-pagination">{{ $cheques->links() }}</div>
    </section>
    @endif
</div>
@endsection
