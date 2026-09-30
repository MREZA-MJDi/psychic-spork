@extends('layouts.admin')

@section('title', 'مدیریت چک‌ها')
@section('page-title', 'مدیریت چک‌ها')

@section('content')
<div class="admin-page-head">
    <div>
        <h1 class="admin-page-head__title">مدیریت چک‌ها</h1>
        <p class="admin-page-head__text">
            بررسی، تأیید، واریز و تسویه پرداخت‌های چکی؛ هر مرحله با وضعیت واقعی سفارش و پرداخت هماهنگ است.
        </p>
    </div>
</div>

<section class="admin-control-grid">
    <article class="admin-control-card admin-control-card--attention">
        <span>نیازمند اقدام</span>
        <strong>{{ number_format($awaitingCount) }}</strong>
        <small>چک ثبت‌شده یا در حال بررسی</small>
    </article>
    <article class="admin-control-card">
        <span>مبلغ در انتظار</span>
        <strong>{{ number_format($awaitingAmount) }}</strong>
        <small>تومان</small>
    </article>
    <article class="admin-control-card">
        <span>قانون دسترسی</span>
        <strong>Permission</strong>
        <small>دسترسی چکی مستقل از وضعیت عمده است.</small>
    </article>
</section>

<div class="admin-card admin-filter-card">
    <form method="GET" action="{{ route('admin.cheques.index') }}">
        <div class="admin-filter-grid">
            <div class="admin-field">
                <label for="q">جستجو</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="شماره سفارش، صیاد، چک یا بانک...">
            </div>
            <div class="admin-field">
                <label for="status">وضعیت</label>
                <select id="status" name="status">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button type="submit" class="admin-btn admin-btn--secondary">اعمال فیلتر</button>
                <a href="{{ route('admin.cheques.index') }}" class="admin-btn admin-btn--ghost">پاک کردن</a>
            </div>
        </div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">دفتر پرداخت‌های چکی</h2>
            <p class="admin-card-description">هیچ وضعیت مالی با تغییر ظاهری سفارش جعل نمی‌شود؛ تسویه واقعی در مرحله تسویه چک ثبت می‌شود.</p>
        </div>
    </div>

    @if($cheques->isNotEmpty())
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>سفارش / مشتری</th>
                    <th>چک</th>
                    <th>مبلغ</th>
                    <th>سررسید</th>
                    <th>وضعیت</th>
                    <th>مدرک</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($cheques as $cheque)
                    @php
                        $statusClass = match($cheque->status) {
                            'submitted', 'under_review' => 'warning',
                            'accepted', 'deposited' => 'info',
                            'cleared' => 'success',
                            'rejected', 'bounced', 'cancelled' => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $cheque->order?->order_number ?? '—' }}</strong>
                            <div class="admin-muted">{{ $cheque->order?->user?->name ?? $cheque->order?->customer_name ?? 'مشتری' }}</div>
                        </td>
                        <td>
                            <strong dir="ltr">{{ $cheque->sayad_id }}</strong>
                            <div class="admin-muted">{{ $cheque->bank_name }} @if($cheque->cheque_number) · {{ $cheque->cheque_number }} @endif</div>
                        </td>
                        <td>
                            <strong>{{ number_format((float) $cheque->amount) }}</strong>
                            <div class="admin-muted">تومان</div>
                        </td>
                        <td>
                            @if($cheque->due_date)
                                <span data-admin-date="{{ $cheque->due_date->toDateString() }}" data-admin-date-format="day">{{ $cheque->due_date->format('Y/m/d') }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="admin-badge admin-badge--{{ $statusClass }}">{{ $statuses[$cheque->status] ?? $cheque->status }}</span>
                        </td>
                        <td>
                            @if($cheque->image_path)
                                <a href="{{ route('admin.cheques.image', $cheque) }}" target="_blank" rel="noopener" class="admin-btn admin-btn--ghost admin-btn--sm">مشاهده تصویر</a>
                            @else
                                <span class="admin-muted">بدون تصویر</span>
                            @endif
                        </td>
                        <td>
                            <div class="admin-action-stack">
                                @if($cheque->status === 'submitted')
                                    <form method="POST" action="{{ route('admin.cheques.review', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <button class="admin-btn admin-btn--ghost admin-btn--sm" type="submit">شروع بررسی</button>
                                    </form>
                                @endif

                                @if($cheque->status === 'under_review')
                                    <form method="POST" action="{{ route('admin.cheques.accept', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">تأیید چک</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cheques.reject', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <button class="admin-btn admin-btn--danger admin-btn--sm" type="submit">رد چک</button>
                                    </form>
                                @endif

                                @if($cheque->status === 'accepted')
                                    <form method="POST" action="{{ route('admin.cheques.deposit', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <button class="admin-btn admin-btn--ghost admin-btn--sm" type="submit">ثبت واریز</button>
                                    </form>
                                @endif

                                @if($cheque->status === 'deposited')
                                    <form method="POST" action="{{ route('admin.cheques.clear', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <button class="admin-btn admin-btn--secondary admin-btn--sm" type="submit">ثبت تسویه</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.cheques.bounce', $cheque) }}">
                                        @csrf @method('PATCH')
                                        <button class="admin-btn admin-btn--danger admin-btn--sm" type="submit">برگشت چک</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        @if($cheques->hasPages())
            <div class="admin-pagination">{{ $cheques->links() }}</div>
        @endif
    @else
        <div class="admin-empty">
            <div class="admin-empty__icon">✓</div>
            <h3 class="admin-empty__title">موردی برای نمایش نیست</h3>
            <p class="admin-empty__text">با فیلتر فعلی پرداخت چکی پیدا نشد.</p>
        </div>
    @endif
</div>
@endsection
